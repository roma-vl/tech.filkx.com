<?php

namespace Tests\Unit\Actions\Review;

use App\Api\V1\Actions\Review\UploadReviewPhotosAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadReviewPhotosActionTest extends TestCase
{
    use RefreshDatabase;

    private UploadReviewPhotosAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UploadReviewPhotosAction::class);
    }

    public function test_execute_returns_an_empty_array_when_no_photos_are_given(): void
    {
        $this->assertSame([], $this->action->execute(1, []));
    }

    public function test_execute_stores_each_photo_and_returns_public_urls(): void
    {
        Storage::fake('public');

        $urls = $this->action->execute(42, [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.jpg'),
        ]);

        $this->assertCount(2, $urls);
        $this->assertCount(2, Storage::disk('public')->files('reviews/42'));
        foreach ($urls as $url) {
            $this->assertStringContainsString('reviews/42/', $url);
        }
    }
}
