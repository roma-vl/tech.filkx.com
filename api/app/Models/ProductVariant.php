<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'price',
        'old_price',
        'weight',
        'dimensions',
    ];

    protected $casts = [
        'dimensions' => 'array',
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'variant_id');
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->stocks()->sum('quantity') - $this->stocks()->sum('reserved');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'variant_id');
    }
}
