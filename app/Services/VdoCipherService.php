<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VdoCipherService
{
    protected string $baseUrl = 'https://dev.vdocipher.com/api';

    public function isConfigured(): bool
    {
        return filled($this->apiSecret());
    }

    protected function apiSecret(): ?string
    {
        return config('edvora.vdocipher.api_secret') ?: SettingService::get('vdocipher_api_secret');
    }

    public function webhookToken(): ?string
    {
        return config('edvora.vdocipher.webhook_token');
    }

    public function getUploadCredentials(string $title): array
    {
        if (! $this->isConfigured()) {
            return [
                'demo' => true,
                'videoId' => 'demo-'.Str::uuid(),
                'clientPayload' => null,
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Apisecret '.$this->apiSecret(),
            'Accept' => 'application/json',
        ])->put("{$this->baseUrl}/videos?".http_build_query(['title' => $title]))->throw()->json();

        if (empty($response['videoId'])) {
            Log::warning('VdoCipher upload credentials response missing videoId', ['response' => $response]);
        }

        return [
            'demo' => false,
            'videoId' => $response['videoId'] ?? null,
            'clientPayload' => $response['clientPayload'] ?? null,
        ];
    }

    public function checkStatus(string $videoId): array
    {
        if (! $this->isConfigured() || str_starts_with($videoId, 'demo-')) {
            return ['status' => 'ready', 'demo' => true];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Apisecret '.$this->apiSecret(),
            'Accept' => 'application/json',
        ])->get("{$this->baseUrl}/videos/{$videoId}")->throw()->json();

        return [
            'status' => $response['status'] ?? null,
            'length' => $response['length'] ?? null,
            'demo' => false,
        ];
    }

    public function generatePlaybackOtp(string $videoId, string $watermarkText, int $ttl = 300): array
    {
        if (! $this->isConfigured() || str_starts_with($videoId, 'demo-')) {
            return ['otp' => null, 'playbackInfo' => null, 'demo' => true];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Apisecret '.$this->apiSecret(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/videos/{$videoId}/otp", [
            'ttl' => $ttl,
            'annotate' => json_encode([[
                'type' => 'text',
                'text' => $watermarkText,
                'alpha' => '0.60',
                'color' => '0xFFFFFF',
                'size' => '14',
                'interval' => '6000',
            ]]),
        ])->throw()->json();

        return [
            'otp' => $response['otp'] ?? null,
            'playbackInfo' => $response['playbackInfo'] ?? null,
            'demo' => false,
        ];
    }

    public function deleteVideo(string $videoId): void
    {
        if (! $this->isConfigured() || str_starts_with($videoId, 'demo-')) {
            return;
        }

        try {
            Http::withHeaders([
                'Authorization' => 'Apisecret '.$this->apiSecret(),
                'Accept' => 'application/json',
            ])->delete("{$this->baseUrl}/videos/{$videoId}")->throw();
        } catch (\Throwable $e) {
            Log::warning('VdoCipher video delete failed', [
                'video_id' => $videoId,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function mapRemoteStatus(?string $remoteStatus): string
    {
        return match ($remoteStatus) {
            'ready' => Video::STATUS_READY,
            'Pre-Upload' => Video::STATUS_PENDING,
            default => Video::STATUS_PROCESSING,
        };
    }
}
