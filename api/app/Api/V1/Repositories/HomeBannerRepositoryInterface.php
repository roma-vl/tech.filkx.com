<?php

namespace App\Api\V1\Repositories;

use App\Models\HomeBanner;
use Illuminate\Database\Eloquent\Collection;

interface HomeBannerRepositoryInterface
{
    public function all(): Collection;

    public function getActiveOrdered(): Collection;

    public function find(int $id): ?HomeBanner;

    public function create(array $data): HomeBanner;

    public function update(HomeBanner $banner, array $data): HomeBanner;

    public function delete(HomeBanner $banner): bool;
}
