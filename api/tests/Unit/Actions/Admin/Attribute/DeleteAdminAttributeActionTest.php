<?php

namespace Tests\Unit\Actions\Admin\Attribute;

use App\Api\Admin\Actions\Attribute\DeleteAdminAttributeAction;
use App\Api\V1\Exceptions\AttributeNotFoundException;
use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAdminAttributeActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteAdminAttributeAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteAdminAttributeAction::class);
    }

    public function test_execute_deletes_the_attribute(): void
    {
        $attribute = Attribute::create(['code' => 'color', 'name' => ['uk' => 'К', 'en' => 'C'], 'type' => 'color']);

        $this->action->execute($attribute->id);

        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
    }

    public function test_execute_throws_when_the_attribute_does_not_exist(): void
    {
        $this->expectException(AttributeNotFoundException::class);

        $this->action->execute(999999);
    }
}
