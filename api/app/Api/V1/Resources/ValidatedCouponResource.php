<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidatedCouponResource',
    title: 'Validated Coupon Resource',
)]
class ValidatedCouponResource extends JsonResource
{
    #[OA\Property(property: 'code', type: 'string')]
    #[OA\Property(property: 'type', type: 'string', example: 'percent')]
    #[OA\Property(property: 'amount', type: 'number', format: 'float')]
    #[OA\Property(property: 'discount', type: 'number', format: 'float', description: 'Computed discount amount for the current cart')]
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'discount' => (float) $this->discount,
        ];
    }
}
