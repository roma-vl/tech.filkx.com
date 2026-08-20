<?php

namespace Tests\Unit\Actions\Admin\Page;

use App\Api\Admin\Actions\Page\CreatePageAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePageActionTest extends TestCase
{
    use RefreshDatabase;

    private CreatePageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreatePageAction::class);
    }

    public function test_execute_creates_a_page_with_the_given_slug(): void
    {
        $page = $this->action->execute([
            'slug' => 'zz-test-about-us',
            'titleUk' => 'Про нас',
            'titleEn' => 'About us',
            'contentUk' => 'Зміст',
            'contentEn' => 'Content',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('static_pages', [
            'id' => $page->id,
            'slug' => 'zz-test-about-us',
            'status' => 'draft',
        ]);
        $this->assertSame(['uk' => 'Про нас', 'en' => 'About us'], $page->title);
        $this->assertSame(['uk' => 'Зміст', 'en' => 'Content'], $page->content);
    }

    public function test_execute_derives_the_slug_from_the_english_title_when_no_slug_is_given(): void
    {
        $page = $this->action->execute([
            'titleUk' => 'Довідка',
            'titleEn' => 'Zz Test Help Center',
            'contentUk' => 'C',
            'contentEn' => 'C',
        ]);

        $this->assertSame('zz-test-help-center', $page->slug);
    }

    public function test_execute_defaults_status_to_published_when_not_given(): void
    {
        $page = $this->action->execute([
            'slug' => 'zz-test-default-status',
            'titleUk' => 'Т',
            'titleEn' => 'T',
            'contentUk' => 'C',
            'contentEn' => 'C',
        ]);

        $this->assertSame('published', $page->status);
    }
}
