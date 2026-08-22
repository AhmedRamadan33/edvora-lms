<?php

namespace App\Repositories;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ChatRepository extends BaseRepository
{
    public function __construct(ChatConversation $model)
    {
        parent::__construct($model);
    }

    public function conversationsFor(User $user): Collection
    {
        return $this->query()
            ->where(fn ($query) => $query
                ->where('student_id', $user->id)
                ->orWhere('instructor_id', $user->id))
            ->with(['student', 'instructor', 'lastMessage'])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->get();
    }

    public function findBetween(int $studentId, int $instructorId): ?ChatConversation
    {
        return $this->query()
            ->where('student_id', $studentId)
            ->where('instructor_id', $instructorId)
            ->first();
    }

    public function createConversation(int $studentId, int $instructorId): ChatConversation
    {
        return $this->query()->create([
            'student_id' => $studentId,
            'instructor_id' => $instructorId,
        ]);
    }

    public function paginateMessages(ChatConversation $conversation, int $perPage = 30): LengthAwarePaginator
    {
        return $conversation->messages()
            ->with('sender')
            ->latest('id')
            ->paginate($perPage);
    }

    public function createMessage(ChatConversation $conversation, int $senderId, string $body): ChatMessage
    {
        return $conversation->messages()->create([
            'sender_id' => $senderId,
            'body' => $body,
        ]);
    }

    public function touchLastMessageAt(ChatConversation $conversation): void
    {
        $conversation->update(['last_message_at' => now()]);
    }

    public function markRead(ChatConversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function unreadCountFor(User $user): int
    {
        return ChatMessage::query()
            ->whereHas('conversation', fn ($query) => $query
                ->where('student_id', $user->id)
                ->orWhere('instructor_id', $user->id))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function unreadCountsByConversation(User $user): array
    {
        return ChatMessage::query()
            ->select('conversation_id')
            ->selectRaw('COUNT(*) as unread')
            ->whereHas('conversation', fn ($query) => $query
                ->where('student_id', $user->id)
                ->orWhere('instructor_id', $user->id))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->groupBy('conversation_id')
            ->pluck('unread', 'conversation_id')
            ->all();
    }
}
