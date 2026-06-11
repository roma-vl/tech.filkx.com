<?php

namespace App\Api\Admin\Actions\Attribute;

use App\Api\V1\Repositories\AttributeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListAdminAttributesAction
{
    public function __construct(
        protected AttributeRepositoryInterface $attributeRepository
    ) {}

    public function execute(): Collection
    {
        return $this->attributeRepository->allWithValues();
    }
}
