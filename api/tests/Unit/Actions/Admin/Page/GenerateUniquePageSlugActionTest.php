<?php

namespace Tests\Unit\Actions\Admin\Page;

use App\Api\Admin\Actions\Page\GenerateUniquePageSlugAction;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateUniquePageSlugActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateUniquePageSlugAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GenerateUniquePageSlugAction::class);
    }

    private function makePage(string $slug): Page
    {
        return Page::create([
            'slug' => $slug,
            'title' => ['uk' => 'Т', 'en' => 'T'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'published',
        ]);
    }

    public function test_execute_slugifies_the_source_when_no_conflict_exists(): void
    {
        $slug = $this->action->execute('About Us');

        $this->assertSame('about-us', $slug);
    }

    public function test_execute_appends_a_numeric_suffix_when_the_slug_already_exists(): void
    {
        $this->makePage('about-us');

        $slug = $this->action->execute('About Us');

        $this->assertSame('about-us-1', $slug);
    }

    public function test_execute_increments_the_suffix_until_it_finds_a_free_slug(): void
    {
        $this->makePage('about-us');
        $this->makePage('about-us-1');

        $slug = $this->action->execute('About Us');

        $this->assertSame('about-us-2', $slug);
    }

    public function test_execute_ignores_the_excluded_page_when_checking_for_conflicts(): void
    {
        $page = $this->makePage('about-us');

        $slug = $this->action->execute('About Us', $page->id);

        $this->assertSame('about-us', $slug);
    }

    public function test_execute_falls_back_to_a_generated_slug_when_the_source_has_no_slug_characters(): void
    {
        $slug = $this->action->execute('---');

        $this->assertStringStartsWith('page-', $slug);
    }
}
