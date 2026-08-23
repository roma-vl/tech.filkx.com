<?php

namespace Tests\Feature\Console;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadProductImagesCommandTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(string $slug): Product
    {
        return Product::create([
            'slug' => $slug,
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    private function makeVariant(Product $product, string $sku, ?array $dimensions = null): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'price' => 100,
            'dimensions' => $dimensions,
        ]);
    }

    private function writeSourceFile(array $data): string
    {
        $path = tempnam(sys_get_temp_dir(), 'products').'.json';
        file_put_contents($path, json_encode($data));

        return $path;
    }

    public function test_downloaded_images_are_attached_to_every_variant_of_the_product(): void
    {
        Storage::fake('public');
        Http::fake(['*' => Http::response('binary-image-data', 200, ['Content-Type' => 'image/webp'])]);

        $product = $this->makeProduct('iphone-15-pro');
        $variantWithoutPhotos = $this->makeVariant($product, 'sku-1');
        $siblingVariantWithoutPhotos = $this->makeVariant($product, 'sku-2');

        $source = $this->writeSourceFile([
            [
                'products' => [
                    [
                        'slug' => 'iphone-15-pro.html',
                        'images' => ['https://sota.store/img/iphone.webp'],
                    ],
                ],
            ],
        ]);

        $this->artisan('products:download-images', ['--source' => $source])
            ->assertSuccessful();

        $variantWithoutPhotos->refresh();
        $siblingVariantWithoutPhotos->refresh();

        $this->assertNotEmpty($variantWithoutPhotos->dimensions['images']);
        $this->assertSame(
            $variantWithoutPhotos->dimensions['images'],
            $siblingVariantWithoutPhotos->dimensions['images'],
        );
        $this->assertTrue($variantWithoutPhotos->dimensions['images'][0]['isPrimary']);

        unlink($source);
    }

    public function test_payment_and_logo_images_are_filtered_out(): void
    {
        Storage::fake('public');
        Http::fake(['*' => Http::response('binary-image-data', 200, ['Content-Type' => 'image/webp'])]);

        $product = $this->makeProduct('samsung-galaxy-s24');
        $variant = $this->makeVariant($product, 'sku-1');

        $source = $this->writeSourceFile([
            [
                'products' => [
                    [
                        'slug' => 'samsung-galaxy-s24.html',
                        'images' => [
                            'https://sota.store/img/privat24-pay.webp',
                            'https://sota.store/img/samsung.webp',
                        ],
                    ],
                ],
            ],
        ]);

        $this->artisan('products:download-images', ['--source' => $source])
            ->assertSuccessful();

        $variant->refresh();

        $this->assertCount(1, $variant->dimensions['images']);
        $this->assertStringContainsString('samsung', $variant->dimensions['images'][0]['url']);

        unlink($source);
    }

    public function test_skips_product_when_images_already_exist_and_force_not_passed(): void
    {
        Storage::fake('public');
        Http::fake(['*' => Http::response('binary-image-data', 200, ['Content-Type' => 'image/webp'])]);

        $product = $this->makeProduct('lenovo-legion-5');
        $existingImages = [['url' => 'https://existing.example/photo.webp', 'isPrimary' => true]];
        $variant = $this->makeVariant($product, 'sku-1', ['images' => $existingImages]);

        $source = $this->writeSourceFile([
            [
                'products' => [
                    [
                        'slug' => 'lenovo-legion-5.html',
                        'images' => ['https://sota.store/img/legion.webp'],
                    ],
                ],
            ],
        ]);

        $this->artisan('products:download-images', ['--source' => $source])
            ->assertSuccessful();

        $variant->refresh();

        $this->assertSame($existingImages, $variant->dimensions['images']);

        unlink($source);
    }

    public function test_force_option_redownloads_and_overwrites_existing_images_on_every_variant(): void
    {
        Storage::fake('public');
        Http::fake(['*' => Http::response('binary-image-data', 200, ['Content-Type' => 'image/webp'])]);

        $product = $this->makeProduct('sony-wh-1000xm5');
        $existingImages = [['url' => 'https://existing.example/photo.webp', 'isPrimary' => true]];
        $firstVariant = $this->makeVariant($product, 'sku-1', ['images' => $existingImages]);
        $secondVariant = $this->makeVariant($product, 'sku-2');

        $source = $this->writeSourceFile([
            [
                'products' => [
                    [
                        'slug' => 'sony-wh-1000xm5.html',
                        'images' => ['https://sota.store/img/sony.webp'],
                    ],
                ],
            ],
        ]);

        $this->artisan('products:download-images', ['--source' => $source, '--force' => true])
            ->assertSuccessful();

        $firstVariant->refresh();
        $secondVariant->refresh();

        $this->assertNotSame($existingImages, $firstVariant->dimensions['images']);
        $this->assertSame($firstVariant->dimensions['images'], $secondVariant->dimensions['images']);

        unlink($source);
    }

    public function test_reports_error_when_source_file_is_missing(): void
    {
        $this->artisan('products:download-images', ['--source' => '/nonexistent/products.json'])
            ->assertFailed();
    }
}
