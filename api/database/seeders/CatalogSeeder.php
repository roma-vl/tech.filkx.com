<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\Stock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Warehouses
        $mainWarehouse = Warehouse::create([
            'name' => 'Головний склад Київ',
            'address' => 'вул. Хрещатик 1, Київ, Україна',
        ]);

        $reserveWarehouse = Warehouse::create([
            'name' => 'Резервний склад Львів',
            'address' => 'вул. Городоцька 15, Львів, Україна',
        ]);

        // 2. Create Brands
        $brands = [
            'Apple' => ['slug' => 'apple', 'description' => 'Think Different. Преміум девайси від Apple.'],
            'Samsung' => ['slug' => 'samsung', 'description' => 'Інновації для кожного від Samsung.'],
            'Lenovo' => ['slug' => 'lenovo', 'description' => 'Надійні ноутбуки та комп\'ютери для бізнесу та геймінгу.'],
            'Sony' => ['slug' => 'sony', 'description' => 'Найкращий звук та мультимедіа техніка.'],
        ];

        $brandModels = [];
        foreach ($brands as $name => $data) {
            $brandModels[$name] = Brand::create([
                'name' => $name,
                'slug' => $data['slug'],
                'description' => $data['description'],
            ]);
        }

        // 3. Create Categories
        $categories = [
            [
                'slug' => 'laptops',
                'name' => ['uk' => 'Ноутбуки', 'en' => 'Laptops'],
                'order' => 1,
                'children' => [
                    ['slug' => 'macbooks', 'name' => ['uk' => 'MacBook', 'en' => 'MacBook'], 'order' => 1],
                    ['slug' => 'gaming-laptops', 'name' => ['uk' => 'Ігрові ноутбуки', 'en' => 'Gaming Laptops'], 'order' => 2],
                ]
            ],
            [
                'slug' => 'phones',
                'name' => ['uk' => 'Смартфони', 'en' => 'Phones'],
                'order' => 2,
                'children' => [
                    ['slug' => 'ios-phones', 'name' => ['uk' => 'iPhone', 'en' => 'iPhone'], 'order' => 1],
                    ['slug' => 'android-phones', 'name' => ['uk' => 'Android Смартфони', 'en' => 'Android Phones'], 'order' => 2],
                ]
            ],
            [
                'slug' => 'audio',
                'name' => ['uk' => 'Аудіо та Навушники', 'en' => 'Audio & Headphones'],
                'order' => 3,
                'children' => [
                    ['slug' => 'tws-headphones', 'name' => ['uk' => 'TWS Навушники', 'en' => 'TWS Headphones'], 'order' => 1],
                    ['slug' => 'overhead-headphones', 'name' => ['uk' => 'Накладні навушники', 'en' => 'Overhead Headphones'], 'order' => 2],
                ]
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $catData) {
            $parent = Category::create([
                'slug' => $catData['slug'],
                'name' => $catData['name'],
                'order' => $catData['order'],
            ]);
            $categoryModels[$catData['slug']] = $parent;

            if (isset($catData['children'])) {
                foreach ($catData['children'] as $childData) {
                    $child = Category::create([
                        'parent_id' => $parent->id,
                        'slug' => $childData['slug'],
                        'name' => $childData['name'],
                        'order' => $childData['order'],
                    ]);
                    $categoryModels[$childData['slug']] = $child;
                }
            }
        }

        // 4. Create Products
        $productsData = [
            [
                'brand' => 'Apple',
                'slug' => 'iphone-15-pro-max',
                'name' => [
                    'uk' => 'Apple iPhone 15 Pro Max 256GB Natural Titanium',
                    'en' => 'Apple iPhone 15 Pro Max 256GB Natural Titanium'
                ],
                'description' => [
                    'uk' => 'Флагманський смартфон з титановим корпусом, новим процесором A17 Pro та професійною системою камер.',
                    'en' => 'Flagship smartphone with titanium body, new A17 Pro chip and professional camera system.'
                ],
                'categories' => ['phones', 'ios-phones'],
                'status' => 'active',
                'variants' => [
                    [
                        'sku' => 'IPH15PM-256-NT',
                        'price' => 54999.00,
                        'old_price' => 59999.00,
                        'weight' => 0.22,
                        'stock_qty' => 15,
                    ]
                ]
            ],
            [
                'brand' => 'Samsung',
                'slug' => 'samsung-galaxy-s24-ultra',
                'name' => [
                    'uk' => 'Samsung Galaxy S24 Ultra 12/512GB Titanium Gray',
                    'en' => 'Samsung Galaxy S24 Ultra 12/512GB Titanium Gray'
                ],
                'description' => [
                    'uk' => 'Суперфлагман з підтримкою стилуса S Pen, процесором Snapdragon 8 Gen 3 та штучним інтелектом Galaxy AI.',
                    'en' => 'Super flagship with S Pen support, Snapdragon 8 Gen 3 processor and Galaxy AI.'
                ],
                'categories' => ['phones', 'android-phones'],
                'status' => 'active',
                'variants' => [
                    [
                        'sku' => 'SM-S24U-512-TG',
                        'price' => 49999.00,
                        'old_price' => 55999.00,
                        'weight' => 0.23,
                        'stock_qty' => 10,
                    ]
                ]
            ],
            [
                'brand' => 'Lenovo',
                'slug' => 'lenovo-legion-5-pro',
                'name' => [
                    'uk' => 'Lenovo Legion 5 Pro 16ARH7H Storm Grey',
                    'en' => 'Lenovo Legion 5 Pro 16ARH7H Storm Grey'
                ],
                'description' => [
                    'uk' => 'Потужний ігровий ноутбук з екраном WQXGA 165Hz, процесором AMD Ryzen 7 та відеокартою RTX 3070 Ti.',
                    'en' => 'Powerful gaming laptop with 165Hz WQXGA screen, AMD Ryzen 7 and RTX 3070 Ti.'
                ],
                'categories' => ['laptops', 'gaming-laptops'],
                'status' => 'active',
                'variants' => [
                    [
                        'sku' => 'LEN-LEG5P-RTX3070TI',
                        'price' => 62999.00,
                        'old_price' => null,
                        'weight' => 2.49,
                        'stock_qty' => 5,
                    ]
                ]
            ],
            [
                'brand' => 'Sony',
                'slug' => 'sony-wh-1000xm5-black',
                'name' => [
                    'uk' => 'Бездротові навушники Sony WH-1000XM5 Black',
                    'en' => 'Wireless Headphones Sony WH-1000XM5 Black'
                ],
                'description' => [
                    'uk' => 'Накладні навушники преміум класу з провідною в індустрії системою активного шумозаглушення ANC.',
                    'en' => 'Premium over-ear headphones with industry leading Active Noise Canceling.'
                ],
                'categories' => ['audio', 'overhead-headphones'],
                'status' => 'active',
                'variants' => [
                    [
                        'sku' => 'SONY-WH1000XM5-B',
                        'price' => 14999.00,
                        'old_price' => 16999.00,
                        'weight' => 0.25,
                        'stock_qty' => 20,
                    ]
                ]
            ],
            [
                'brand' => 'Apple',
                'slug' => 'apple-airpods-pro-2',
                'name' => [
                    'uk' => 'Apple AirPods Pro 2nd Gen USB-C',
                    'en' => 'Apple AirPods Pro 2nd Gen USB-C'
                ],
                'description' => [
                    'uk' => 'Найпопулярніші TWS навушники з активним шумозаглушенням, адаптивним аудіо та кейсом USB-C.',
                    'en' => 'Most popular TWS headphones with Active Noise Canceling, adaptive audio and USB-C case.'
                ],
                'categories' => ['audio', 'tws-headphones'],
                'status' => 'active',
                'variants' => [
                    [
                        'sku' => 'APP-AIRPRO2-USBC',
                        'price' => 9999.00,
                        'old_price' => 10999.00,
                        'weight' => 0.05,
                        'stock_qty' => 30,
                    ]
                ]
            ]
        ];

        foreach ($productsData as $pData) {
            $product = Product::create([
                'brand_id' => $brandModels[$pData['brand']]->id,
                'slug' => $pData['slug'],
                'name' => $pData['name'],
                'description' => $pData['description'],
                'status' => $pData['status'],
            ]);

            // Sync categories
            $catIds = [];
            foreach ($pData['categories'] as $slug) {
                if (isset($categoryModels[$slug])) {
                    $catIds[] = $categoryModels[$slug]->id;
                }
            }
            $product->categories()->sync($catIds);

            // Create variants & stock
            foreach ($pData['variants'] as $vData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $vData['sku'],
                    'price' => $vData['price'],
                    'old_price' => $vData['old_price'],
                    'weight' => $vData['weight'],
                ]);

                // Seed stock at main warehouse
                Stock::create([
                    'variant_id' => $variant->id,
                    'warehouse_id' => $mainWarehouse->id,
                    'quantity' => $vData['stock_qty'],
                    'reserved' => 0,
                ]);

                // Seed some minor stock at reserve warehouse
                Stock::create([
                    'variant_id' => $variant->id,
                    'warehouse_id' => $reserveWarehouse->id,
                    'quantity' => max(1, (int)($vData['stock_qty'] / 3)),
                    'reserved' => 0,
                ]);
            }
        }
    }
}
