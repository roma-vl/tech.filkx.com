<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'variant_id',
        'quantity',
    ];

    // Keeps Cart::updated_at reflecting the last time an item was actually
    // added/changed/removed, not just when the (often long-lived) cart row
    // was first created — SendAbandonedCartRemindersAction relies on this to
    // find carts that have genuinely sat untouched past the threshold.
    protected $touches = ['cart'];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
