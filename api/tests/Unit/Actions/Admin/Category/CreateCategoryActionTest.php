<?php

namespace Tests\Unit\Actions\Admin\Category;

use App\Api\Admin\Actions\Category\CreateCategoryAction;
use App\Api\Admin\Dto\CategoryDto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateCategoryAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateCategoryAction::class);
    }

    public function test_execute_creates_the_category_with_a_generated_slug(): void
    {
        $dto = new CategoryDto(nameUk: 'Телефони', nameEn: 'Zz Test Phones', parentId: null, order: 3);

        $category = $this->action->execute($dto);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'zz-test-phones',
            'parent_id' => null,
            'order' => 3,
        ]);
        $this->assertSame(['uk' => 'Телефони', 'en' => 'Zz Test Phones'], $category->name);
    }

    public function test_execute_sets_the_parent_id_when_given(): void
    {
        $parent = $this->action->execute(new CategoryDto(nameUk: 'Батько', nameEn: 'Zz Test Parent', parentId: null, order: 0));

        $child = $this->action->execute(new CategoryDto(nameUk: 'Дитина', nameEn: 'Zz Test Child', parentId: $parent->id, order: 0));

        $this->assertSame($parent->id, $child->parent_id);
    }
}
