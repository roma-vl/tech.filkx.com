<?php

namespace Tests\Unit\Actions\Admin\Category;

use App\Api\Admin\Actions\Category\UpdateCategoryAction;
use App\Api\Admin\Dto\CategoryDto;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateCategoryAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateCategoryAction::class);
    }

    public function test_execute_updates_the_categorys_fields_without_touching_the_slug(): void
    {
        $category = Category::create(['slug' => 'zz-test-original', 'name' => ['uk' => 'О', 'en' => 'O'], 'order' => 0]);

        $updated = $this->action->execute($category->id, new CategoryDto(
            nameUk: 'Оновлено',
            nameEn: 'Updated',
            parentId: null,
            order: 5,
        ));

        $this->assertSame('zz-test-original', $updated->slug);
        $this->assertSame(['uk' => 'Оновлено', 'en' => 'Updated'], $updated->name);
        $this->assertSame(5, $updated->order);
    }

    public function test_execute_throws_when_the_category_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, new CategoryDto(nameUk: 'Т', nameEn: 'T', parentId: null, order: 0));
    }
}
