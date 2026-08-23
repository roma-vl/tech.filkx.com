<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\UploadProductImageAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadProductImageActionTest extends TestCase
{
    private UploadProductImageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UploadProductImageAction::class);
    }

    public function test_execute_stores_the_file_on_the_public_disk_and_returns_its_url_and_path(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('product.jpg');

        $result = $this->action->execute($file);

        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertStringStartsWith('catalog/', $result['path']);
        Storage::disk('public')->assertExists($result['path']);
        $this->assertSame(Storage::disk('public')->url($result['path']), $result['url']);
    }
}
