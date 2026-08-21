<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\GenericNotification;
use App\Repositories\ContactMessageRepository;
use Illuminate\Support\Facades\Notification;

class ContactService
{
    public function __construct(private ContactMessageRepository $messages)
    {
    }

    public function submit(array $data): ContactMessage
    {
        $message = $this->messages->create($data);

        $admins = User::role('admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new GenericNotification(
                __('New contact message from :name.', ['name' => $message->name]),
                route('admin.contacts.show', $message),
                __('New contact message')
            ));
        }

        return $message;
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
