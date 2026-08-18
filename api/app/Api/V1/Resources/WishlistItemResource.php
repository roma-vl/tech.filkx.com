<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'WishlistItemResource',
    title: 'Wishlist Item Resource',
)]
class WishlistItemResource extends JsonResource
{
    #[OA\Property(property: 'id', type: 'integer')]
    #[OA\Property(property: 'slug', type: 'string')]
    #[OA\Property(property: 'name', type: 'object', description: 'Localized product name, keyed by locale')]
    #[OA\Property(property: 'current_price', type: 'number', format: 'float', nullable: true)]
    #[OA\Property(property: 'price_at_add', type: 'number', format: 'float', nullable: true)]
    #[OA\Property(property: 'price_drop_pct', type: 'number', format: 'float', nullable: true, description: 'Percentage the price has dropped since it was added, if any')]
    #[OA\Property(property: 'notify_on_drop', type: 'boolean')]
    #[OA\Property(property: 'added_at', type: 'string', format: 'date-time')]
    public function toArray(Request $request): array
    {
        $currentPrice = $this->variants->min('price');
        $priceAtAdd = $this->pivot->price_at_add;

        $dropped = null;
        if ($priceAtAdd && $currentPrice) {
            $diff = $priceAtAdd - $currentPrice;
            $dropped = $diff > 0 ? round(($diff / $priceAtAdd) * 100, 1) : null;
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'current_price' => $currentPrice,
            'price_at_add' => $priceAtAdd,
            'price_drop_pct' => $dropped,
            'notify_on_drop' => (bool) $this->pivot->notify_on_drop,
            'added_at' => $this->pivot->created_at,
        ];
    }
}
