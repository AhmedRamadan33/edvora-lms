<?php

namespace App\Repositories;

use App\Models\Announcement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AnnouncementRepository extends BaseRepository
{
    public function __construct(Announcement $model)
    {
        parent::__construct($model);
    }

    public function paginateForSender(int $senderId, int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->query()
            ->where('sender_id', $senderId)
            ->when($search, fn ($query) => $query->where('subject', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
