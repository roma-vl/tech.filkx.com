<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'nameUk' => $this->name['uk'] ?? '',
            'nameEn' => $this->name['en'] ?? '',
            'type' => $this->type,
            'values' => $this->values->map(function ($val) {
                if ($this->type === 'color') {
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
