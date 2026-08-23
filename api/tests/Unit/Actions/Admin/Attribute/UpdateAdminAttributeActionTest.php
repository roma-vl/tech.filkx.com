<?php

namespace Tests\Unit\Actions\Admin\Attribute;

use App\Api\Admin\Actions\Attribute\UpdateAdminAttributeAction;
use App\Api\Admin\Dto\AttributeDto;
use App\Api\V1\Exceptions\AttributeNotFoundException;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAdminAttributeActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateAdminAttributeAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateAdminAttributeAction::class);
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

    public function test_execute_throws_when_the_attribute_does_not_exist(): void
    {
        $this->expectException(AttributeNotFoundException::class);

        $this->action->execute(999999, $this->makeDto());
    }

    public function test_execute_updates_the_attribute_fields(): void
    {
        $attribute = Attribute::create(['code' => 'old', 'name' => ['uk' => 'A', 'en' => 'A'], 'type' => 'text']);

        $this->action->execute($attribute->id, $this->makeDto(['code' => 'new']));

        $this->assertDatabaseHas('attributes', ['id' => $attribute->id, 'code' => 'new']);
    }

    public function test_execute_updates_an_existing_value_when_an_id_is_given(): void
    {
        $attribute = Attribute::create(['code' => 'c', 'name' => ['uk' => 'A', 'en' => 'A'], 'type' => 'text']);
        $value = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => ['uk' => 'Old', 'en' => 'Old']]);

        $dto = $this->makeDto(['values' => [['id' => $value->id, 'valueUk' => 'New', 'valueEn' => 'New']]]);
        $this->action->execute($attribute->id, $dto);

        $this->assertSame(1, AttributeValue::where('attribute_id', $attribute->id)->count());
        $this->assertDatabaseHas('attribute_values', ['id' => $value->id, 'value' => json_encode(['uk' => 'New', 'en' => 'New'])]);
    }

    public function test_execute_creates_a_new_value_when_no_id_is_given(): void
    {
        $attribute = Attribute::create(['code' => 'c', 'name' => ['uk' => 'A', 'en' => 'A'], 'type' => 'text']);

        $dto = $this->makeDto(['values' => [['valueUk' => 'Fresh', 'valueEn' => 'Fresh']]]);
        $this->action->execute($attribute->id, $dto);

        $this->assertSame(1, AttributeValue::where('attribute_id', $attribute->id)->count());
    }

    public function test_execute_deletes_values_that_are_not_present_in_the_payload(): void
    {
        $attribute = Attribute::create(['code' => 'c', 'name' => ['uk' => 'A', 'en' => 'A'], 'type' => 'text']);
        $stale = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => ['uk' => 'Stale', 'en' => 'Stale']]);

        $this->action->execute($attribute->id, $this->makeDto(['values' => []]));

        $this->assertDatabaseMissing('attribute_values', ['id' => $stale->id]);
    }

    public function test_execute_updates_a_color_values_raw_value(): void
    {
        $attribute = Attribute::create(['code' => 'c', 'name' => ['uk' => 'A', 'en' => 'A'], 'type' => 'color']);
        $value = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => ['uk' => '#000', 'en' => '#000']]);

        $dto = $this->makeDto(['type' => 'color', 'values' => [['id' => $value->id, 'value' => '#fff']]]);
        $this->action->execute($attribute->id, $dto);

        $this->assertDatabaseHas('attribute_values', ['id' => $value->id, 'value' => json_encode(['uk' => '#fff', 'en' => '#fff'])]);
    }

    public function test_execute_syncs_categories(): void
    {
        $attribute = Attribute::create(['code' => 'c', 'name' => ['uk' => 'A', 'en' => 'A'], 'type' => 'text']);
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);

        $updated = $this->action->execute($attribute->id, $this->makeDto(['categoryIds' => [$category->id]]));

        $this->assertTrue($updated->categories()->where('categories.id', $category->id)->exists());
    }
}
