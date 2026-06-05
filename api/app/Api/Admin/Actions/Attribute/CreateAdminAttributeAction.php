<?php

namespace App\Api\Admin\Actions\Attribute;

use App\Api\Admin\Dto\AttributeDto;
use App\Api\V1\Repositories\AttributeRepositoryInterface;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CreateAdminAttributeAction
{
    public function __construct(
        protected AttributeRepositoryInterface $attributeRepository
    ) {}

    public function execute(AttributeDto $dto): Attribute
    {
        return DB::transaction(function () use ($dto) {
            $attribute = $this->attributeRepository->create($dto->toArray());

            $this->syncValues($attribute, $dto->values);

            $attribute->categories()->sync($dto->categoryIds);

            return $attribute->load(['values', 'categories']);
        });
    }

    protected function syncValues(Attribute $attribute, array $valuesInput): void
    {
        $existingIds = [];

        foreach ($valuesInput as $valData) {
            $valuePayload = [];
            if ($attribute->type === 'color') {
                $valuePayload = ['value' => $valData['value'] ?? ''];
            } else {
                $valuePayload = [
                    'uk' => $valData['valueUk'] ?? '',
                    'en' => $valData['valueEn'] ?? '',
                ];
            }

            if (! empty($valData['id'])) {
                $existingVal = AttributeValue::where('attribute_id', $attribute->id)
                    ->where('id', $valData['id'])
                    ->first();
                if ($existingVal) {
                    $existingVal->update(['value' => $valuePayload]);
                    $existingIds[] = $existingVal->id;
                }
            } else {
                $newVal = AttributeValue::create([
                    'attribute_id' => $attribute->id,
                    'value' => $valuePayload,
                ]);
                $existingIds[] = $newVal->id;
            }
        }

        AttributeValue::where('attribute_id', $attribute->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }
}
