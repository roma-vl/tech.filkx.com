<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class HomePromoSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        if ($products->count() > 0) {
            // Set 3 products as hot deals
            $products->slice(0, 3)->each(function ($p) {
                $p->update(['is_hot' => true]);
            });

            // Set another 3 products as recommended
            $products->slice(3, 3)->each(function ($p) {
                $p->update(['is_recommended' => true]);
            });
        }
    }
}
