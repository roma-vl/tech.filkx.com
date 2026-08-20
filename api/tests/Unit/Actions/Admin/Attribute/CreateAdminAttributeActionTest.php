<?php

namespace Tests\Unit\Actions\Admin\Attribute;

use App\Api\Admin\Actions\Attribute\CreateAdminAttributeAction;
use App\Api\Admin\Dto\AttributeDto;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminAttributeActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateAdminAttributeAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateAdminAttributeAction::class);
    }

    private function makeDto(array $overrides = []): AttributeDto
    {
        return new AttributeDto(
            code: $overrides['code'] ?? 'code-'.uniqid(),
            nameUk: $overrides['nameUk'] ?? 'Колір',
            nameEn: $overrides['nameEn'] ?? 'Color',
            type: $overrides['type'] ?? 'text',
            values: $overrides['values'] ?? [],
            categoryIds: $overrides['categoryIds'] ?? []
        );
    }

    public function test_execute_creates_the_attribute(): void
    {
        $dto = $this->makeDto(['code' => 'material']);

        $attribute = $this->action->execute($dto);

        $this->assertDatabaseHas('attributes', ['id' => $attribute->id, 'code' => 'material', 'type' => 'text']);
    }

    public function test_execute_creates_color_values_from_the_raw_value_field(): void
    {
        $dto = $this->makeDto(['type' => 'color', 'values' => [['value' => '#ff0000']]]);

        $attribute = $this->action->execute($dto);

        $this->assertDatabaseHas('attribute_values', ['attribute_id' => $attribute->id, 'value' => json_encode(['value' => '#ff0000'])]);
    }

    public function test_execute_creates_localized_values_for_a_non_color_attribute(): void
    {
        $dto = $this->makeDto(['type' => 'text', 'values' => [['valueUk' => 'Червоний', 'valueEn' => 'Red']]]);

        $attribute = $this->action->execute($dto);

        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'value' => json_encode(['uk' => 'Червоний', 'en' => 'Red']),
        ]);
    }

    public function test_execute_syncs_the_given_categories(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);

        $dto = $this->makeDto(['categoryIds' => [$category->id]]);

        $attribute = $this->action->execute($dto);

        $this->assertTrue($attribute->categories()->where('categories.id', $category->id)->exists());
    }

    public function test_execute_returns_the_attribute_with_values_and_categories_loaded(): void
    {
        $dto = $this->makeDto(['values' => [['valueUk' => 'A', 'valueEn' => 'A']]]);

        $attribute = $this->action->execute($dto);

        $this->assertTrue($attribute->relationLoaded('values'));
        $this->assertTrue($attribute->relationLoaded('categories'));
        $this->assertCount(1, $attribute->values);
    }
}
