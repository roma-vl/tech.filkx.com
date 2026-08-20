<?php

namespace Tests\Unit\Actions\Admin\Page;

use App\Api\Admin\Actions\Page\UpdatePageAction;
use App\Models\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePageActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdatePageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdatePageAction::class);
    }

    private function makePage(): Page
    {
        return Page::create([
            'slug' => 'zz-test-original',
            'title' => ['uk' => 'Оригінал', 'en' => 'Original'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'draft',
        ]);
    }

    public function test_execute_updates_the_pages_fields(): void
    {
        $page = $this->makePage();

        $updated = $this->action->execute($page->id, [
            'slug' => 'zz-test-updated',
            'titleUk' => 'Оновлено',
            'titleEn' => 'Updated',
            'contentUk' => 'Новий зміст',
            'contentEn' => 'New content',
            'status' => 'published',
        ]);

        $this->assertSame('zz-test-updated', $updated->slug);
        $this->assertSame(['uk' => 'Оновлено', 'en' => 'Updated'], $updated->title);
        $this->assertSame(['uk' => 'Новий зміст', 'en' => 'New content'], $updated->content);
        $this->assertSame('published', $updated->status);
    }

    public function test_execute_keeps_the_same_slug_when_it_still_belongs_to_this_page(): void
    {
        $page = $this->makePage();

        $updated = $this->action->execute($page->id, [
            'slug' => 'zz-test-original',
            'titleUk' => 'Оригінал',
            'titleEn' => 'Original',
            'contentUk' => 'C',
            'contentEn' => 'C',
            'status' => 'draft',
        ]);

        $this->assertSame('zz-test-original', $updated->slug);
    }

    public function test_execute_deduplicates_the_slug_when_it_belongs_to_another_page(): void
    {
        $page = $this->makePage();
        Page::create([
            'slug' => 'zz-test-taken',
            'title' => ['uk' => 'Т', 'en' => 'T'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'published',
        ]);

        $updated = $this->action->execute($page->id, [
            'slug' => 'zz-test-taken',
            'titleUk' => 'Оригінал',
            'titleEn' => 'Original',
            'contentUk' => 'C',
            'contentEn' => 'C',
            'status' => 'draft',
        ]);

        $this->assertSame('zz-test-taken-1', $updated->slug);
    }

    public function test_execute_throws_when_the_page_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, [
            'slug' => 'zz-test-missing',
            'titleUk' => 'Т',
            'titleEn' => 'T',
            'contentUk' => 'C',
            'contentEn' => 'C',
            'status' => 'draft',
        ]);
    }
}
