<?php

namespace App\Api\Admin\Actions\Attribute;

use App\Api\V1\Exceptions\AttributeNotFoundException;
use App\Api\V1\Repositories\AttributeRepositoryInterface;

class DeleteAdminAttributeAction
{
    public function __construct(
        protected AttributeRepositoryInterface $attributeRepository
    ) {}

    public function execute(int $id): void
    {
        $attribute = $this->attributeRepository->find($id);

        if (! $attribute) {
            throw new AttributeNotFoundException;
        }

        $this->attributeRepository->delete($attribute);
    }
}
