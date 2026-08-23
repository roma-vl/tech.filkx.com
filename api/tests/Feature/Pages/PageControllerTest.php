<?php

namespace Tests\Feature\Pages;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_a_published_page(): void
    {
        Page::create([
            'slug' => 'about-us',
            'title' => ['uk' => 'Про нас', 'en' => 'About us'],
            'content' => ['uk' => '<p>Текст</p>', 'en' => '<p>Text</p>'],
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/v1/pages/about-us');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'about-us')
            ->assertJsonPath('data.title.en', 'About us')
            ->assertJsonPath('data.content.en', '<p>Text</p>');
    }

    public function test_show_returns_404_for_an_unknown_slug(): void
    {
        $this->getJson('/api/v1/pages/does-not-exist')->assertStatus(404);
    }

    public function test_show_returns_404_for_a_draft_page(): void
    {
        Page::create([
            'slug' => 'draft-page',
            'title' => ['uk' => 'Чернетка', 'en' => 'Draft'],
            'content' => ['uk' => '<p>...</p>', 'en' => '<p>...</p>'],
            'status' => 'draft',
        ]);

        $this->getJson('/api/v1/pages/draft-page')->assertStatus(404);
    }
}
