<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\UploadBlogImageAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadBlogImageActionTest extends TestCase
{
    public function test_execute_stores_the_image_under_the_blog_directory_and_returns_its_url(): void
    {
        Storage::fake('public');
        $image = UploadedFile::fake()->image('cover.jpg');

        $url = app(UploadBlogImageAction::class)->execute($image);

        $files = Storage::disk('public')->files('blog');
        $this->assertCount(1, $files);
        $this->assertStringContainsString(basename($files[0]), $url);
    }
}
