<?php

namespace App\Api\V1\Repositories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Collection;

interface AttributeRepositoryInterface
{
    public function allWithValues(): Collection;

    public function find(int $id): ?Attribute;

    public function create(array $data): Attribute;

    public function update(Attribute $attribute, array $data): Attribute;

    public function delete(Attribute $attribute): bool;
}
