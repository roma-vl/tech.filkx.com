<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\CreateBlogCategoryAction;
use App\Api\Admin\Dto\BlogCategoryDto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBlogCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateBlogCategoryAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateBlogCategoryAction::class);
    }

    public function test_execute_creates_the_category_with_a_generated_slug(): void
    {
        $dto = new BlogCategoryDto(
            nameUk: 'Новини',
            nameEn: 'Zz Test News',
            descriptionUk: 'Опис',
            descriptionEn: 'Description',
            order: 2,
        );

        $category = $this->action->execute($dto);

        $this->assertDatabaseHas('blog_categories', [
            'id' => $category->id,
            'slug' => 'zz-test-news',
            'order' => 2,
        ]);
        $this->assertSame(['uk' => 'Новини', 'en' => 'Zz Test News'], $category->name);
        $this->assertSame(['uk' => 'Опис', 'en' => 'Description'], $category->description);
    }

    public function test_execute_defaults_missing_descriptions_to_empty_strings(): void
    {
        $dto = new BlogCategoryDto(nameUk: 'Т', nameEn: 'Zz Test No Desc', descriptionUk: null, descriptionEn: null, order: 0);

        $category = $this->action->execute($dto);

        $this->assertSame(['uk' => '', 'en' => ''], $category->description);
    }
}
