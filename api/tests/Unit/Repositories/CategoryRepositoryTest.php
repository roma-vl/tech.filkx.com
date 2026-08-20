<?php

namespace Tests\Unit\Repositories;

use App\Api\V1\Repositories\CategoryRepository;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(CategoryRepository::class);
    }

    private function makeCategory(?int $parentId = null): Category
    {
        return Category::create([
            'slug' => 'cat-'.uniqid(),
            'name' => ['uk' => 'Категорія', 'en' => 'Category'],
            'order' => 0,
            'parent_id' => $parentId,
        ]);
    }

    public function test_resolve_category_ids_by_slug_returns_the_category_and_its_children_ids(): void
    {
        $parent = $this->makeCategory();
        $child = $this->makeCategory($parent->id);

        $result = $this->repository->resolveCategoryIdsBySlug($parent->slug);

        $this->assertEqualsCanonicalizing([$parent->id, $child->id], $result);
    }

    public function test_resolve_category_ids_by_slug_returns_only_the_category_id_when_it_has_no_children(): void
    {
        $category = $this->makeCategory();

        $result = $this->repository->resolveCategoryIdsBySlug($category->slug);

        $this->assertSame([$category->id], $result);
    }

    public function test_resolve_category_ids_by_slug_returns_an_empty_array_for_an_unknown_slug(): void
    {
        $result = $this->repository->resolveCategoryIdsBySlug('does-not-exist');

        $this->assertSame([], $result);
    }
}
