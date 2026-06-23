<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentPrice = $this->variants->min('price');
        $priceAtAdd   = $this->pivot->price_at_add;

        $dropped = null;
        if ($priceAtAdd && $currentPrice) {
            $diff    = $priceAtAdd - $currentPrice;
            $dropped = $diff > 0 ? round(($diff / $priceAtAdd) * 100, 1) : null;
        }

        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'name'           => $this->name,
            'current_price'  => $currentPrice,
            'price_at_add'   => $priceAtAdd,
            'price_drop_pct' => $dropped,
            'notify_on_drop' => (bool) $this->pivot->notify_on_drop,
            'added_at'       => $this->pivot->created_at,
        ];
    }
}
