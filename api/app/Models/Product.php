<?php

namespace App\Models;

use App\Services\Catalog\ProductAttributeFacetCodec;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductSummary',
    title: 'Product Summary',
    description: 'Product as returned by the public catalog endpoints (raw Eloquent model, camelCase-converted).',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'name', description: 'Localized name keyed by locale (uk, en)', type: 'object'),
        new OA\Property(property: 'description', description: 'Localized description keyed by locale (uk, en)', type: 'object'),
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
    use HasFactory, Searchable, SoftDeletes;

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
     * Eager-load the relationships this reads via makeAllSearchableUsing() -
     * see that method for what it covers and why.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $variantPrices = $this->variants
            ->pluck('price')
            ->filter(fn ($price) => $price !== null)
            ->map(fn ($price) => (float) $price)
            ->values();

        $attributeAssignments = $this->attributeValues
            ->concat($this->variants->flatMap(fn (ProductVariant $variant) => $variant->attributeValues));

        $facetCodec = app(ProductAttributeFacetCodec::class);

        $attributeTokens = $attributeAssignments
            ->map(function (ProductAttributeValue $assignment) use ($facetCodec) {
                if ($assignment->attribute_value_id !== null) {
                    return $facetCodec->encodeAttributeValue($assignment->attribute->code, $assignment->attribute_value_id);
                }

                if (! empty($assignment->custom_value)) {
                    return $facetCodec->encodeCustomValue($assignment->attribute->code, $assignment->custom_value);
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values();

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name_uk' => $this->name['uk'] ?? '',
            'name_en' => $this->name['en'] ?? '',
            'description_uk' => $this->description['uk'] ?? '',
            'description_en' => $this->description['en'] ?? '',
            'status' => $this->status,
            'brand_id' => $this->brand_id,
            'category_ids' => $this->categories
                ->flatMap(fn (Category $category) => array_merge([$category->id], $category->getAncestorIds()))
                ->unique()
                ->values()
                ->all(),
            'price_min' => $variantPrices->min(),
            'price_max' => $variantPrices->max(),
            'variant_prices' => $variantPrices->all(),
            'has_discount' => $this->variants->contains(fn (ProductVariant $variant) => $variant->old_price !== null),
            'in_stock' => $this->variants
                ->flatMap(fn (ProductVariant $variant) => $variant->stocks)
                ->contains(fn (Stock $stock) => $stock->quantity > $stock->reserved),
            'attributes' => $attributeTokens->all(),
            'views_count' => $this->views_count ?? 0,
            'created_at' => $this->created_at?->getTimestamp() ?? 0,
        ];
    }

    /**
     * Eager-load everything toSearchableArray() reads so `scout:import`/bulk
     * reindexing doesn't N+1 per product. `categories.parent` only covers one
     * level up the category tree - correct for deeper trees too (getAncestorIds()
     * lazy-loads the rest), just not pre-loaded; the current catalog is at most
     * two levels deep.
     */
    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'brand',
            'categories.parent',
            'variants.stocks',
            'variants.attributeValues.attribute',
            'attributeValues.attribute',
        ]);
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
