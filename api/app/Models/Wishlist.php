<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Wishlist extends Pivot
{
    protected $table = 'favorites';

    protected $fillable = [
        'user_id',
        'product_id',
        'price_at_add',
        'notify_on_drop',
    ];

    protected $casts = [
        'price_at_add'   => 'decimal:2',
        'notify_on_drop' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getCurrentPriceAttribute(): ?float
    {
        return $this->product
            ->variants()
            ->whereNotNull('price')
            ->min('price');
    }

    public function hasPriceDrop(float $thresholdPercent = 5.0): bool
    {
        if ($this->price_at_add === null || $this->current_price === null) {
            return false;
        }

        $drop = (($this->price_at_add - $this->current_price) / $this->price_at_add) * 100;

        return $drop >= $thresholdPercent;
    }
}
