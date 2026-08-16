<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HomeBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'badge' => $this->badge,
            'subtitle' => $this->subtitle,
            'title' => $this->title,
            'description' => $this->description,
            'imagePath' => $this->image_path,
            'imageUrl' => Storage::disk('public')->url($this->image_path),
            'buttonLabel' => $this->button_label,
            'linkType' => $this->link_type,
            'linkValue' => $this->link_value,
            'isActive' => $this->is_active,
            'sortOrder' => $this->sort_order,
        ];
    }
}
