<?php

namespace App\Api\Admin\Actions\Page;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPagesAction
{
    public function execute(?string $search, int $perPage): LengthAwarePaginator
    {
        return Page::query()
            ->when($search, function ($query) use ($search) {
                $query->where('slug', 'like', "%{$search}%")
                    ->orWhereRaw("title->>'uk' ILIKE ?", ['%'.$search.'%'])
                    ->orWhereRaw("title->>'en' ILIKE ?", ['%'.$search.'%']);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }
}
