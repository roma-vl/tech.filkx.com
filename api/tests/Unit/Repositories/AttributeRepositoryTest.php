<?php

namespace Tests\Unit\Repositories;

use App\Api\V1\Repositories\AttributeRepository;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private AttributeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(AttributeRepository::class);
    }

    private function makeAttribute(array $overrides = []): Attribute
    {
        return Attribute::create(array_merge([
            'code' => 'attr-'.uniqid(),
            'name' => ['uk' => 'Колір', 'en' => 'Color'],
            'type' => 'select',
        ], $overrides));
    }

    // --- allWithValues ---
    //
    // The attributes table is pre-seeded by the seed_smartphone_attributes migration,
    // so these tests clear it first to assert on a known baseline.

    public function test_all_with_values_returns_attributes_with_values_and_categories_loaded(): void
    {
        Attribute::query()->delete();

        $attribute = $this->makeAttribute();
        AttributeValue::create([
            'attribute_id' => $attribute->id,
            'value' => ['uk' => 'Червоний'],
        ]);
        $category = Category::create([
            'slug' => 'phones',
            'name' => ['uk' => 'Телефони', 'en' => 'Phones'],
        ]);
        $attribute->categories()->attach($category->id);

        $result = $this->repository->allWithValues();

        $this->assertCount(1, $result);
        $loaded = $result->first();
        $this->assertTrue($loaded->relationLoaded('values'));
        $this->assertTrue($loaded->relationLoaded('categories'));
        $this->assertCount(1, $loaded->values);
        $this->assertCount(1, $loaded->categories);
    }

    public function test_all_with_values_returns_empty_collection_when_no_attributes_exist(): void
    {
        Attribute::query()->delete();

        $result = $this->repository->allWithValues();

        $this->assertCount(0, $result);
    }

    public function test_all_with_values_includes_attributes_without_any_values_or_categories(): void
    {
        Attribute::query()->delete();
        $this->makeAttribute();

        $result = $this->repository->allWithValues();

        $this->assertCount(1, $result);
        $this->assertCount(0, $result->first()->values);
        $this->assertCount(0, $result->first()->categories);
    }

    // --- find ---

    public function test_find_returns_the_attribute(): void
    {
        $attribute = $this->makeAttribute();

        $result = $this->repository->find($attribute->id);

        $this->assertNotNull($result);
        $this->assertSame($attribute->id, $result->id);
    }

    public function test_find_returns_null_when_attribute_does_not_exist(): void
    {
        $result = $this->repository->find(999999);

        $this->assertNull($result);
    }

    // --- create ---

    public function test_create_persists_a_new_attribute(): void
    {
        $result = $this->repository->create([
            'code' => 'ram',
            'name' => ['uk' => 'Оперативна пам\'ять', 'en' => 'RAM'],
            'type' => 'number',
        ]);

        $this->assertNotNull($result->id);
        $this->assertDatabaseHas('attributes', [
            'id' => $result->id,
            'code' => 'ram',
            'type' => 'number',
        ]);
    }

    // --- update ---

    public function test_update_persists_the_given_data_and_returns_the_attribute(): void
    {
        $attribute = $this->makeAttribute(['type' => 'select']);

        $result = $this->repository->update($attribute, ['type' => 'boolean']);

        $this->assertSame('boolean', $result->type);
        $this->assertSame('boolean', $attribute->fresh()->type);
    }

    // --- delete ---

    public function test_delete_removes_the_attribute_and_returns_true(): void
    {
        $attribute = $this->makeAttribute();

        $result = $this->repository->delete($attribute);

        $this->assertTrue($result);
        $this->assertNull(Attribute::find($attribute->id));
    }
}
