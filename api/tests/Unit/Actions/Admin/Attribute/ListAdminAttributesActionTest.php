<?php

namespace Tests\Unit\Actions\Admin\Attribute;

use App\Api\Admin\Actions\Attribute\ListAdminAttributesAction;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAdminAttributesActionTest extends TestCase
{
    use RefreshDatabase;

    private ListAdminAttributesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListAdminAttributesAction::class);
    }

    public function test_execute_returns_all_attributes_with_their_values_loaded(): void
    {
        $attribute = Attribute::create(['code' => 'color-'.uniqid(), 'name' => ['uk' => 'К', 'en' => 'C'], 'type' => 'color']);
        AttributeValue::create(['attribute_id' => $attribute->id, 'value' => ['value' => '#000']]);

        $result = $this->action->execute();
        $found = $result->firstWhere('id', $attribute->id);

        $this->assertNotNull($found);
        $this->assertTrue($found->relationLoaded('values'));
        $this->assertCount(1, $found->values);
    }

    public function test_execute_includes_attributes_seeded_by_migrations(): void
    {
        $result = $this->action->execute();

        $this->assertGreaterThan(0, $result->count());
    }
}
