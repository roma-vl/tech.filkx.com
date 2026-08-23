<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PromoPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'badge' => $this->badge,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'imagePath' => $this->image_path,
            'imageUrl' => Storage::disk('public')->url($this->image_path),
            'isActive' => $this->is_active,
            'sortOrder' => $this->sort_order,
            'productsCount' => $this->when(
                $this->products_count !== null,
                fn () => $this->products_count
            ),
            'products' => $this->whenLoaded('products', fn () => $this->products->map(fn ($product) => [
                'id' => $product->id,
                'nameUk' => $product->name['uk'] ?? '',
                'nameEn' => $product->name['en'] ?? '',
            ])),
        ];
    }
}
