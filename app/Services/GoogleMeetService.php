<?php

namespace App\Services;

use App\Models\LiveClass;
use App\Models\OAuthConnection;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleMeetService
{
    protected const AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    protected const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    protected const REVOKE_URL = 'https://oauth2.googleapis.com/revoke';

    protected const CALENDAR_API_BASE = 'https://www.googleapis.com/calendar/v3';

    protected const SCOPE = 'https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/userinfo.email';

    public function isConfigured(): bool
    {
        return filled($this->clientId()) && filled($this->clientSecret());
    }

    protected function clientId(): ?string
    {
        return config('edvora.google_meet.client_id') ?: SettingService::get('google_meet_client_id');
    }

    protected function clientSecret(): ?string
    {
        return config('edvora.google_meet.client_secret') ?: SettingService::get('google_meet_client_secret');
    }

    protected function redirectUri(): string
    {
        return route('instructor.integrations.google.callback');
    }

    public function authorizationUrl(User $instructor): string
    {
        $state = Crypt::encryptString($instructor->id.'|'.Str::random(16));
        session(['google_oauth_state' => $state]);

        return self::AUTHORIZE_URL.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function handleCallback(User $instructor, string $code): void
    {
        $response = Http::asForm()
            ->timeout(30)
            ->post(self::TOKEN_URL, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'redirect_uri' => $this->redirectUri(),
            ])->throw()->json();

        $profile = Http::withToken($response['access_token'])
            ->timeout(30)
            ->get('https://www.googleapis.com/oauth2/v2/userinfo')
            ->throw()->json();

        $connection = $this->connectionFor($instructor);

        OAuthConnection::query()->updateOrCreate(
            ['user_id' => $instructor->id, 'provider' => OAuthConnection::PROVIDER_GOOGLE_MEET],
            [
                'provider_user_id' => $profile['id'] ?? null,
                'provider_email' => $profile['email'] ?? null,
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'] ?? $connection?->refresh_token,
                'expires_at' => now()->addSeconds((int) ($response['expires_in'] ?? 3600)),
                'scopes' => $response['scope'] ?? null,
            ]
        );
    }

    public function disconnect(User $instructor): void
    {
        $connection = $this->connectionFor($instructor);

        if (! $connection) {
            return;
        }

        try {
            Http::asForm()
                ->timeout(30)
                ->post(self::REVOKE_URL, ['token' => $connection->refresh_token])
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('Google token revoke failed', [
                'user_id' => $instructor->id,
                'message' => $e->getMessage(),
            ]);
        }

        $connection->delete();
    }

    public function connectionFor(User $instructor): ?OAuthConnection
    {
        return OAuthConnection::query()
            ->where('user_id', $instructor->id)
            ->where('provider', OAuthConnection::PROVIDER_GOOGLE_MEET)
            ->first();
    }

    public function accessTokenFor(User $instructor): ?string
    {
        $connection = $this->connectionFor($instructor);

        if (! $connection) {
            return null;
        }

        if (! $connection->isExpired()) {
            return $connection->access_token;
        }

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post(self::TOKEN_URL, [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $connection->refresh_token,
                    'client_id' => $this->clientId(),
                    'client_secret' => $this->clientSecret(),
                ])->throw()->json();
        } catch (\Throwable $e) {
            Log::warning('Google token refresh failed', [
                'user_id' => $instructor->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        $connection->update([
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'] ?? $connection->refresh_token,
            'expires_at' => now()->addSeconds((int) ($response['expires_in'] ?? 3600)),
        ]);

        return $connection->access_token;
    }

    public function createMeeting(User $instructor, LiveClass $liveClass): array
    {
        $token = $this->accessTokenFor($instructor);

        if (! $token) {
            throw new RuntimeException('Instructor has not connected their Google account.');
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->post(self::CALENDAR_API_BASE.'/calendars/primary/events?conferenceDataVersion=1', $this->eventPayload($liveClass))
            ->throw()->json();

        $joinUrl = $response['hangoutLink']
            ?? collect(data_get($response, 'conferenceData.entryPoints', []))->firstWhere('entryPointType', 'video')['uri']
            ?? null;

        return [
            'meeting_id' => (string) ($response['id'] ?? ''),
            'join_url' => $joinUrl,
            'start_url' => null,
            'meta' => $response,
        ];
    }

    public function updateMeeting(User $instructor, LiveClass $liveClass): void
    {
        $token = $this->accessTokenFor($instructor);

        if (! $token || ! $liveClass->provider_meeting_id) {
            return;
        }

        Http::withToken($token)
            ->timeout(30)
            ->patch(self::CALENDAR_API_BASE."/calendars/primary/events/{$liveClass->provider_meeting_id}", $this->eventPayload($liveClass))
            ->throw();
    }

    public function deleteMeeting(User $instructor, LiveClass $liveClass): void
    {
        $token = $this->accessTokenFor($instructor);

        if (! $token || ! $liveClass->provider_meeting_id) {
            return;
        }

        try {
            Http::withToken($token)
                ->timeout(30)
                ->delete(self::CALENDAR_API_BASE."/calendars/primary/events/{$liveClass->provider_meeting_id}")
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('Google Meet event delete failed', [
                'live_class_id' => $liveClass->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function eventPayload(LiveClass $liveClass): array
    {
        return [
            'summary' => $liveClass->title,
            'description' => (string) $liveClass->description,
            'start' => [
                'dateTime' => $liveClass->scheduled_at->clone()->setTimezone('UTC')->toRfc3339String(),
                'timeZone' => 'UTC',
            ],
            'end' => [
                'dateTime' => $liveClass->endsAt()->setTimezone('UTC')->toRfc3339String(),
                'timeZone' => 'UTC',
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => (string) Str::uuid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
        ];
    }
}
