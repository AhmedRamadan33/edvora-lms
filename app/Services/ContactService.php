<?php

namespace App\Services;

use App\Models\ActivityLog;
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
            $message = $this->messages->update($message, [
                'status' => 'read',
                'read_at' => now(),
            ]);

            ActivityLog::record('contact_message.read', $message, ['name' => $message->name]);
        }

        return $message;
    }

    public function delete(ContactMessage $message): bool
    {
        $name = $message->name;

        $result = $this->messages->delete($message);

        ActivityLog::record('contact_message.deleted', $message, ['name' => $name]);

        return $result;
    }
}
