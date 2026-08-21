<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(NotificationService $notifications): View
    {
        $items = $notifications->paginateFor(auth()->user());

        return view('notifications.index', compact('items'));
    }

    public function recent(NotificationService $notifications): JsonResponse
    {
        $items = $notifications->recentFor(auth()->user())->map(fn (DatabaseNotification $n) => [
            'id' => $n->id,
            'message' => $n->data['message'] ?? '',
            'url' => $n->data['url'] ?? route('notifications.index'),
            'read' => $n->read_at !== null,
            'created_at' => $n->created_at->diffForHumans(),
        ]);

        return response()->json([
            'unread_count' => $notifications->unreadCountFor(auth()->user()),
            'items' => $items,
        ]);
    }

    public function read(DatabaseNotification $notification, NotificationService $notifications): RedirectResponse
    {
        $this->authorizeNotification($notification);

        return redirect()->to($notifications->openAndMarkRead($notification));
    }

    public function readAll(NotificationService $notifications): RedirectResponse
    {
        $notifications->markAllReadFor(auth()->user());

        return back()->with('success', __('All notifications marked as read.'));
    }

    protected function authorizeNotification(DatabaseNotification $notification): void
    {
        abort_unless(
            $notification->notifiable_type === auth()->user()::class && $notification->notifiable_id === auth()->id(),
            403
        );
    }
}
