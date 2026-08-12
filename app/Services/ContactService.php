<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Repositories\ContactMessageRepository;

class ContactService
{
    public function __construct(private ContactMessageRepository $messages)
    {
    }

    public function submit(array $data): ContactMessage
    {
        return $this->messages->create($data);
    }

    public function paginate(int $perPage = 20, ?string $search = null)
    {
        return $this->messages->paginateLatest($perPage, $search);
    }

    public function markRead(ContactMessage $message): ContactMessage
    {
        if ($message->status === 'new') {
            return $this->messages->update($message, [
                'status' => 'read',
                'read_at' => now(),
            ]);
        }

        return $message;
    }

    public function delete(ContactMessage $message): bool
    {
        return $this->messages->delete($message);
    }
}
