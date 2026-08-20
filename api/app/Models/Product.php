<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductSummary',
    title: 'Product Summary',
    description: 'Product as returned by the public catalog endpoints (raw Eloquent model, camelCase-converted).',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'name', type: 'object', description: 'Localized name keyed by locale (uk, en)'),
        new OA\Property(property: 'description', type: 'object', description: 'Localized description keyed by locale (uk, en)'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'isHot', type: 'boolean'),
        new OA\Property(property: 'isRecommended', type: 'boolean'),
        new OA\Property(property: 'viewsCount', type: 'integer'),
        new OA\Property(property: 'brand', type: 'object', nullable: true),
        new OA\Property(property: 'categories', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'variants', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'approvedReviewsCount', type: 'integer'),
        new OA\Property(property: 'approvedReviewsAvgRating', type: 'number', nullable: true),
    ],
)]
class Product extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'brand_id',
        'slug',
        'name',
        'description',
        'status',
        'views_count',
        'is_hot',
        'is_recommended',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'is_hot' => 'boolean',
        'is_recommended' => 'boolean',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name_uk' => $this->name['uk'] ?? '',
            'name_en' => $this->name['en'] ?? '',
            'description_uk' => $this->description['uk'] ?? '',
            'description_en' => $this->description['en'] ?? '',
            'status' => $this->status,
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('status', 'approved');
    }
}
