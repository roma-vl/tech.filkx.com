<?php

namespace App\Models;

use App\Models\Concerns\Targetable;
use App\Models\Contracts\Discountable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model implements Discountable
{
    use HasFactory, Targetable;

    protected $fillable = [
        'name',
        'description',
        'type',
        'amount',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'float',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'promotion_category');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'promotion_product');
    }
}
