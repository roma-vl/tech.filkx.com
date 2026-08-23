<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class PromoPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'badge',
        'title',
        'subtitle',
        'description',
        'image_path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promo_page_product')
            ->withPivot('sort_order')
            ->orderBy('promo_page_product.sort_order');
    }
}
