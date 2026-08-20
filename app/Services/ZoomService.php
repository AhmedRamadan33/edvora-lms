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

class ZoomService
{
    protected const AUTHORIZE_URL = 'https://zoom.us/oauth/authorize';

    protected const TOKEN_URL = 'https://zoom.us/oauth/token';

    protected const REVOKE_URL = 'https://zoom.us/oauth/revoke';

    protected const API_BASE = 'https://api.zoom.us/v2';

    public function isConfigured(): bool
    {
        return filled($this->clientId()) && filled($this->clientSecret());
    }

    protected function clientId(): ?string
    {
        return config('edvora.zoom.client_id') ?: SettingService::get('zoom_client_id');
    }

    protected function clientSecret(): ?string
    {
        return config('edvora.zoom.client_secret') ?: SettingService::get('zoom_client_secret');
    }

    protected function redirectUri(): string
    {
        return route('instructor.integrations.zoom.callback');
    }

    public function authorizationUrl(User $instructor): string
    {
        $state = Crypt::encryptString($instructor->id.'|'.Str::random(16));
        session(['zoom_oauth_state' => $state]);

        return self::AUTHORIZE_URL.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'state' => $state,
        ]);
    }

    public function handleCallback(User $instructor, string $code): void
    {
        $response = Http::asForm()
            ->withBasicAuth((string) $this->clientId(), (string) $this->clientSecret())
            ->timeout(30)
            ->post(self::TOKEN_URL, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri(),
            ])->throw()->json();

        $profile = Http::withToken($response['access_token'])
            ->timeout(30)
            ->get(self::API_BASE.'/users/me')
            ->throw()->json();

        OAuthConnection::query()->updateOrCreate(
            ['user_id' => $instructor->id, 'provider' => OAuthConnection::PROVIDER_ZOOM],
            [
                'provider_user_id' => $profile['id'] ?? null,
                'provider_email' => $profile['email'] ?? null,
                'access_token' => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
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
                ->withBasicAuth((string) $this->clientId(), (string) $this->clientSecret())
                ->timeout(30)
                ->post(self::REVOKE_URL, ['token' => $connection->access_token])
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('Zoom token revoke failed', [
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
            ->where('provider', OAuthConnection::PROVIDER_ZOOM)
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
                ->withBasicAuth((string) $this->clientId(), (string) $this->clientSecret())
                ->timeout(30)
                ->post(self::TOKEN_URL, [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $connection->refresh_token,
                ])->throw()->json();
        } catch (\Throwable $e) {
            Log::warning('Zoom token refresh failed', [
                'user_id' => $instructor->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        $connection->update([
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'expires_at' => now()->addSeconds((int) ($response['expires_in'] ?? 3600)),
        ]);

        return $connection->access_token;
    }

    public function createMeeting(User $instructor, LiveClass $liveClass): array
    {
        $token = $this->accessTokenFor($instructor);

        if (! $token) {
            throw new RuntimeException('Instructor has not connected their Zoom account.');
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->post(self::API_BASE.'/users/me/meetings', $this->meetingPayload($liveClass))
            ->throw()->json();

        return [
            'meeting_id' => (string) ($response['id'] ?? ''),
            'join_url' => $response['join_url'] ?? null,
            'start_url' => $response['start_url'] ?? null,
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
            ->patch(self::API_BASE."/meetings/{$liveClass->provider_meeting_id}", $this->meetingPayload($liveClass))
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
                ->delete(self::API_BASE."/meetings/{$liveClass->provider_meeting_id}")
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('Zoom meeting delete failed', [
                'live_class_id' => $liveClass->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    protected function meetingPayload(LiveClass $liveClass): array
    {
        return [
            'topic' => $liveClass->title,
            'type' => 2,
            'start_time' => $liveClass->scheduled_at->clone()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z'),
            'duration' => $liveClass->duration_minutes,
            'timezone' => 'UTC',
            'agenda' => (string) $liveClass->description,
            'settings' => [
                'join_before_host' => true,
                'waiting_room' => false,
                'mute_upon_entry' => true,
                'host_video' => true,
                'participant_video' => true,
            ],
        ];
    }
}
