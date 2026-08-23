<?php

namespace Tests\Unit\Actions;

use App\Api\V1\Actions\GetPageBySlugAction;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class GetPageBySlugActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_execute_returns_a_published_page_by_slug(): void
    {
        $page = Page::create([
            'slug' => 'about-us',
            'title' => ['uk' => 'Про нас', 'en' => 'About us'],
            'content' => ['uk' => '<p>...</p>', 'en' => '<p>...</p>'],
            'status' => 'published',
        ]);

        $result = app(GetPageBySlugAction::class)->execute('about-us');

        $this->assertTrue($result->is($page));
    }

    public function test_execute_aborts_with_404_when_the_slug_does_not_exist(): void
    {
        $this->expectException(NotFoundHttpException::class);

        app(GetPageBySlugAction::class)->execute('does-not-exist');
    }

    public function test_execute_aborts_with_404_for_a_draft_page(): void
    {
        Page::create([
            'slug' => 'draft-page',
            'title' => ['uk' => 'Чернетка', 'en' => 'Draft'],
            'content' => ['uk' => '<p>...</p>', 'en' => '<p>...</p>'],
            'status' => 'draft',
        ]);

        $this->expectException(NotFoundHttpException::class);

        app(GetPageBySlugAction::class)->execute('draft-page');
    }
}
