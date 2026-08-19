<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\VdoCipherService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class VdoCipherWebhookController extends Controller
{
    public function handle(Request $request, VdoCipherService $vdocipher): Response
    {
        $expectedToken = $vdocipher->webhookToken();

        if ($expectedToken) {
            if ($request->query('token') !== $expectedToken) {
                return response('invalid', 403);
            }
        } elseif (! (app()->environment('local', 'testing') && config('edvora.payments.allow_unsigned_webhooks'))) {
            return response('invalid', 403);
        }

        $videoId = $request->input('payload.id');
        $remoteStatus = $request->input('payload.status');

        if (! $videoId) {
            return response('ok');
        }

        $video = Video::query()->where('vdocipher_video_id', $videoId)->first();

        if (! $video) {
            Log::warning('VdoCipher webhook video not found', ['video_id' => $videoId]);

            return response('ok');
        }

        $video->update(['status' => $vdocipher->mapRemoteStatus($remoteStatus)]);

        return response('ok');
    }
}
