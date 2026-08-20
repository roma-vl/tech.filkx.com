<?php

namespace App\Api\Admin\Actions\Attribute;

use App\Api\Admin\Dto\AttributeDto;
use App\Api\V1\Repositories\AttributeRepositoryInterface;
use App\Models\Attribute;
use App\Models\AttributeValue;
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
                // Stored with the same {uk, en} shape every other attribute value uses (a
                // color has no locale, so both keys just hold the same hex string) - this
                // keeps the public catalog's attribute-value JSON shape uniform instead of
                // nesting an extra level for color alone.
                $hex = $valData['value'] ?? '';
                $valuePayload = ['uk' => $hex, 'en' => $hex];
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
