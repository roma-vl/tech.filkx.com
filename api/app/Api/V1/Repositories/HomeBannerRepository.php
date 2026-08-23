<?php

namespace App\Api\V1\Repositories;

use App\Models\HomeBanner;
use Illuminate\Database\Eloquent\Collection;

class HomeBannerRepository implements HomeBannerRepositoryInterface
{
    public function all(): Collection
    {
        return HomeBanner::orderBy('sort_order')->orderByDesc('id')->get();
    }

    public function getActiveOrdered(): Collection
    {
        return HomeBanner::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();
    }

    public function find(int $id): ?HomeBanner
    {
        return HomeBanner::find($id);
    }

    public function create(array $data): HomeBanner
    {
        return HomeBanner::create($data);
    }

    public function update(HomeBanner $banner, array $data): HomeBanner
    {
        $banner->update($data);

        return $banner;
    }

    public function delete(HomeBanner $banner): bool
    {
        return (bool) $banner->delete();
    }
}
