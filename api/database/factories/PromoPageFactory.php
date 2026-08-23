<?php

namespace Database\Factories;

use App\Models\PromoPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromoPage>
 */
class PromoPageFactory extends Factory
{
    protected $model = PromoPage::class;

    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(3),
            'badge' => fake()->words(2, true),
            'title' => fake()->sentence(3),
            'subtitle' => fake()->sentence(4),
            'description' => fake()->sentence(12),
            'image_path' => 'promo-pages/'.fake()->uuid().'.jpg',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
