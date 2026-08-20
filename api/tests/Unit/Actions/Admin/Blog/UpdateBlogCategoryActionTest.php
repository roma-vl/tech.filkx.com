<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\UpdateBlogCategoryAction;
use App\Api\Admin\Dto\BlogCategoryDto;
use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateBlogCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateBlogCategoryAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateBlogCategoryAction::class);
    }

    public function test_execute_updates_the_categorys_fields_without_touching_the_slug(): void
    {
        $category = BlogCategory::create(['slug' => 'zz-test-original', 'name' => ['uk' => 'О', 'en' => 'O']]);

        $updated = $this->action->execute($category->id, new BlogCategoryDto(
            nameUk: 'Оновлено',
            nameEn: 'Updated',
            descriptionUk: 'Опис',
            descriptionEn: 'Description',
            order: 4,
        ));

        $this->assertSame('zz-test-original', $updated->slug);
        $this->assertSame(['uk' => 'Оновлено', 'en' => 'Updated'], $updated->name);
        $this->assertSame(4, $updated->order);
    }

    public function test_execute_returns_the_category_with_a_posts_count(): void
    {
        $category = BlogCategory::create(['slug' => 'zz-test-original', 'name' => ['uk' => 'О', 'en' => 'O']]);

        $updated = $this->action->execute($category->id, new BlogCategoryDto(
            nameUk: 'О', nameEn: 'O', descriptionUk: null, descriptionEn: null, order: 0,
        ));

        $this->assertSame(0, $updated->posts_count);
    }

    public function test_execute_throws_when_the_category_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, new BlogCategoryDto(nameUk: 'Т', nameEn: 'T', descriptionUk: null, descriptionEn: null, order: 0));
    }
}
