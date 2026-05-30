<?php

namespace App\Api\V1\Repositories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Collection;

class AttributeRepository implements AttributeRepositoryInterface
{
    public function allWithValues(): Collection
    {
        return Attribute::with('values')->get();
    }

    public function find(int $id): ?Attribute
    {
        return Attribute::find($id);
    }

    public function create(array $data): Attribute
    {
        return Attribute::create($data);
    }

    public function update(Attribute $attribute, array $data): Attribute
    {
        $attribute->update($data);
        return $attribute;
    }

    public function delete(Attribute $attribute): bool
    {
        return (bool) $attribute->delete();
    }
}
