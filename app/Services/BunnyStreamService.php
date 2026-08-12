<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BunnyStreamService
{
    public function libraryId(): ?string
    {
        return config('edvora.bunny.library_id') ?: SettingService::get('bunny_library_id');
    }

    public function apiKey(): ?string
    {
        return config('edvora.bunny.api_key') ?: SettingService::get('bunny_api_key');
    }

    public function createVideo(string $title): array
    {
        $libraryId = $this->libraryId();
        $apiKey = $this->apiKey();

        if (! $libraryId || ! $apiKey) {
            // Local/dev fallback GUID so UI can continue without Bunny credentials.
            return [
                'guid' => (string) Str::uuid(),
                'status' => 0,
                'demo' => true,
            ];
        }

        $response = Http::withHeaders([
            'AccessKey' => $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("https://video.bunnycdn.com/library/{$libraryId}/videos", [
            'title' => $title,
        ]);

        $response->throw();

        return $response->json();
    }

    public function uploadUrl(string $videoId): string
    {
        $libraryId = $this->libraryId();

        return "https://video.bunnycdn.com/library/{$libraryId}/videos/{$videoId}";
    }

    public function markReady(Video $video): void
    {
        $video->update(['status' => 'ready']);
    }

    public function signedPlaybackUrl(Video $video): string
    {
        $cdn = config('edvora.bunny.cdn_hostname') ?: SettingService::get('bunny_cdn_hostname');
        $tokenKey = config('edvora.bunny.token_key') ?: SettingService::get('bunny_token_key');
        $ttl = (int) config('edvora.bunny.token_ttl', 300);
        $expires = now()->addSeconds($ttl)->timestamp;
        $videoId = $video->bunny_video_id;

        if (! $cdn || ! $videoId) {
            return '';
        }

        $path = "/{$videoId}/playlist.m3u8";

        if (! $tokenKey) {
            return "https://{$cdn}{$path}";
        }

        $hashable = $tokenKey.$path.$expires;
        $token = hash('sha256', $hashable);

        return "https://{$cdn}{$path}?token={$token}&expires={$expires}";
    }

    public function embedUrl(Video $video): string
    {
        $libraryId = $video->library_id ?: $this->libraryId();
        $videoId = $video->bunny_video_id;

        if (! $libraryId || ! $videoId) {
            return '';
        }

        $tokenKey = config('edvora.bunny.token_key') ?: SettingService::get('bunny_token_key');
        $ttl = (int) config('edvora.bunny.token_ttl', 300);
        $expires = now()->addSeconds($ttl)->timestamp;

        $base = "https://iframe.mediadelivery.net/embed/{$libraryId}/{$videoId}";

        if (! $tokenKey) {
            return $base.'?autoplay=false&preload=true';
        }

        $token = hash('sha256', $tokenKey.$videoId.$expires);

        return $base.'?token='.$token.'&expires='.$expires.'&autoplay=false&preload=true';
    }
}
