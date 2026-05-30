<?php

namespace App\Api\Admin\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAttributeController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $attributes = Attribute::with('values')->get()->map(function ($attr) {
            return [
                'id' => $attr->id,
                'code' => $attr->code,
                'nameUk' => $attr->name['uk'] ?? '',
                'nameEn' => $attr->name['en'] ?? '',
                'type' => $attr->type,
                'values' => $attr->values->map(function ($val) use ($attr) {
                    if ($attr->type === 'color') {
                        return [
                            'id' => $val->id,
                            'value' => $val->value['value'] ?? $val->value,
                        ];
                    }

                    return [
                        'id' => $val->id,
                        'valueUk' => $val->value['uk'] ?? '',
                        'valueEn' => $val->value['en'] ?? '',
                    ];
                }),
            ];
        });

        return self::successfulResponseWithData($attributes);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|unique:attributes,code',
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'type' => 'required|string|in:text,select,boolean,number,color',
            'values' => 'nullable|array',
        ]);

        $attribute = Attribute::create([
            'code' => $request->input('code'),
            'name' => [
                'uk' => $request->input('nameUk'),
                'en' => $request->input('nameEn'),
            ],
            'type' => $request->input('type'),
        ]);

        $this->syncValues($attribute, $request->input('values', []));

        return self::successfulResponseWithData($this->mapSingleAttribute($attribute), Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $attribute = Attribute::findOrFail($id);

        $request->validate([
            'code' => 'required|string|unique:attributes,code,'.$attribute->id,
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'type' => 'required|string|in:text,select,boolean,number,color',
            'values' => 'nullable|array',
        ]);

        $attribute->update([
            'code' => $request->input('code'),
            'name' => [
                'uk' => $request->input('nameUk'),
                'en' => $request->input('nameEn'),
            ],
            'type' => $request->input('type'),
        ]);

        $this->syncValues($attribute, $request->input('values', []));

        return self::successfulResponseWithData($this->mapSingleAttribute($attribute));
    }

    public function destroy(int $id): JsonResponse
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();

        return self::successfulResponse();
    }

    private function syncValues(Attribute $attribute, array $valuesInput): void
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

        // Delete values not included in request
        AttributeValue::where('attribute_id', $attribute->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }

    private function mapSingleAttribute(Attribute $attribute): array
    {
        $attribute->load('values');

        return [
            'id' => $attribute->id,
            'code' => $attribute->code,
            'nameUk' => $attribute->name['uk'] ?? '',
            'nameEn' => $attribute->name['en'] ?? '',
            'type' => $attribute->type,
            'values' => $attribute->values->map(function ($val) use ($attribute) {
                if ($attribute->type === 'color') {
                    return [
                        'id' => $val->id,
                        'value' => $val->value['value'] ?? $val->value,
                    ];
                }

                return [
                    'id' => $val->id,
                    'valueUk' => $val->value['uk'] ?? '',
                    'valueEn' => $val->value['en'] ?? '',
                ];
            }),
        ];
    }
}
