<?php

namespace App\Repositories;

use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactMessageRepository extends BaseRepository
{
    public function __construct(ContactMessage $model)
    {
        parent::__construct($model);
    }

    public function paginateLatest(int $perPage = 20, ?string $search = null): LengthAwarePaginator
    {
        return $this->query()
            ->when($search, fn ($query) => $query->where(fn ($messageQuery) => $messageQuery
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
