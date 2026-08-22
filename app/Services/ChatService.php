<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Repositories\ChatRepository;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ChatService
{
    public function __construct(
        private ChatRepository $chats,
        private UserRepository $users,
    ) {
    }

    public function conversationsFor(User $user): Collection
    {
        $conversations = $this->chats->conversationsFor($user);
        $unread = $this->chats->unreadCountsByConversation($user);

        return $conversations->map(function (ChatConversation $conversation) use ($user, $unread) {
            $conversation->setAttribute('other_party', $conversation->otherPartyFor($user));
            $conversation->setAttribute('unread_count', $unread[$conversation->id] ?? 0);

            return $conversation;
        });
    }

    public function eligibleInstructorsFor(User $student): Collection
    {
        return $this->users->instructorsForStudent($student->id);
    }

    public function eligibleStudentsFor(User $instructor): Collection
    {
        return $this->users->studentsForInstructor($instructor->id);
    }

    public function startAsStudent(User $student, int $instructorId): ChatConversation
    {
        abort_unless($this->eligibleInstructorsFor($student)->contains('id', $instructorId), 403);

        return $this->chats->findBetween($student->id, $instructorId)
            ?? $this->chats->createConversation($student->id, $instructorId);
    }

    public function startAsInstructor(User $instructor, int $studentId): ChatConversation
    {
        abort_unless($this->eligibleStudentsFor($instructor)->contains('id', $studentId), 403);

        return $this->chats->findBetween($studentId, $instructor->id)
            ?? $this->chats->createConversation($studentId, $instructor->id);
    }

    public function messagesFor(ChatConversation $conversation, User $viewer): LengthAwarePaginator
    {
        abort_unless($conversation->hasParticipant($viewer), 403);

        $messages = $this->chats->paginateMessages($conversation);

        $this->chats->markRead($conversation, $viewer);

        return $messages;
    }

    public function send(ChatConversation $conversation, User $sender, string $body): ChatMessage
    {
        abort_unless($conversation->hasParticipant($sender), 403);

        $message = $this->chats->createMessage($conversation, $sender->id, $body);
        $this->chats->touchLastMessageAt($conversation);

        return $message;
    }

    public function markRead(ChatConversation $conversation, User $reader): void
    {
        abort_unless($conversation->hasParticipant($reader), 403);

        $this->chats->markRead($conversation, $reader);
    }

    public function unreadCountFor(User $user): int
    {
        return $this->chats->unreadCountFor($user);
    }

    public function deleteMessage(ChatMessage $message, User $user): void
    {
        abort_unless($message->sender_id === $user->id, 403);

        $message->delete();
    }
}
