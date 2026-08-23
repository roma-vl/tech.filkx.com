<?php

namespace App\Api\V1\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PromoPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'badge' => $this->badge,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'imageUrl' => Storage::disk('public')->url($this->image_path),
            // Products are the same raw Eloquent shape returned by
            // /catalog/products/{slug} - the frontend already knows how to
            // map that shape (see mapHomeProduct), so no extra resource
            // wrapping is added here.
            'products' => $this->products,
        ];
    }
}
