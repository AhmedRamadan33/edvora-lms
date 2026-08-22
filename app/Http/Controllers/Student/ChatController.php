<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendChatMessageRequest;
use App\Http\Requests\Student\StartChatRequest;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request, ChatService $chats): View
    {
        $user = $request->user();
        $conversations = $chats->conversationsFor($user);

        $activeConversation = null;
        $messages = null;

        if ($request->filled('conversation')) {
            $activeConversation = $conversations->firstWhere('id', $request->integer('conversation'));
            if ($activeConversation) {
                $messages = $chats->messagesFor($activeConversation, $user);
            }
        }

        return view('student.chat.index', [
            'conversations' => $conversations,
            'instructors' => $chats->eligibleInstructorsFor($user),
            'activeConversation' => $activeConversation,
            'messages' => $messages,
        ]);
    }

    public function conversations(Request $request, ChatService $chats): JsonResponse
    {
        return response()->json([
            'conversations' => $chats->conversationsFor($request->user())->map($this->conversationPayload(...))->values(),
        ]);
    }

    public function messages(ChatConversation $conversation, Request $request, ChatService $chats): JsonResponse
    {
        $messages = $chats->messagesFor($conversation, $request->user());

        return response()->json([
            'messages' => collect($messages->items())->reverse()->values()->map($this->messagePayload(...)),
            'has_more' => $messages->hasMorePages(),
            'next_page' => $messages->currentPage() + 1,
        ]);
    }

    public function start(StartChatRequest $request, ChatService $chats): JsonResponse
    {
        $conversation = $chats->startAsStudent($request->user(), $request->integer('instructor_id'));
        $conversation->load(['student', 'instructor']);

        return response()->json([
            'conversation' => $this->conversationPayload($conversation),
        ]);
    }

    public function send(ChatConversation $conversation, SendChatMessageRequest $request, ChatService $chats): JsonResponse
    {
        $message = $chats->send($conversation, $request->user(), $request->string('body')->toString());
        $message->load('sender');

        return response()->json([
            'message' => $this->messagePayload($message),
        ], 201);
    }

    public function read(ChatConversation $conversation, Request $request, ChatService $chats): JsonResponse
    {
        $chats->markRead($conversation, $request->user());

        return response()->json(['ok' => true]);
    }

    public function destroyMessage(ChatMessage $message, Request $request, ChatService $chats): JsonResponse
    {
        $chats->deleteMessage($message, $request->user());

        return response()->json(['ok' => true]);
    }

    protected function conversationPayload(ChatConversation $conversation): array
    {
        $user = auth()->user();
        $other = $conversation->otherPartyFor($user);
        $last = $conversation->lastMessage;

        return [
            'id' => $conversation->id,
            'name' => $other->name,
            'avatar' => mb_substr($other->name, 0, 1),
            'last_message' => $last?->body,
            'last_message_at' => $last?->created_at?->diffForHumans(),
            'unread_count' => $conversation->unread_count ?? 0,
        ];
    }

    protected function messagePayload(ChatMessage $message): array
    {
        $user = auth()->user();

        return [
            'id' => $message->id,
            'body' => $message->body,
            'is_mine' => $message->sender_id === $user->id,
            'sender_name' => $message->sender?->name,
            'created_at' => $message->created_at->toIso8601String(),
            'time' => $message->created_at->format('H:i'),
            'date_label' => $message->created_at->isToday()
                ? __('Today')
                : ($message->created_at->isYesterday() ? __('Yesterday') : $message->created_at->format('d M Y')),
            'read' => $message->read_at !== null,
        ];
    }
}
