<?php

namespace Database\Factories;

use App\Models\HomeBanner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomeBanner>
 */
class HomeBannerFactory extends Factory
{
    protected $model = HomeBanner::class;

    public function definition(): array
    {
        return [
            'badge' => fake()->words(2, true),
            'subtitle' => fake()->sentence(4),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(12),
            'image_path' => 'banners/'.fake()->uuid().'.jpg',
            'button_label' => 'Переглянути',
            'link_type' => 'catalog',
            'link_value' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
