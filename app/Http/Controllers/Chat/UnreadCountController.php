<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnreadCountController extends Controller
{
    public function __invoke(Request $request, ChatService $chats): JsonResponse
    {
        return response()->json([
            'unread_count' => $chats->unreadCountFor($request->user()),
        ]);
    }
}
