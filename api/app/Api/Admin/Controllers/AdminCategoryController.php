<?php

namespace App\Api\Admin\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminCategoryController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::with('parent')->get()->map(function ($cat) {
            return [
                'id' => $cat->id,
                'parentId' => $cat->parent_id,
                'parentName' => $cat->parent ? ($cat->parent->name['uk'] ?? $cat->parent->name['en'] ?? '') : null,
                'slug' => $cat->slug,
                'nameUk' => $cat->name['uk'] ?? '',
                'nameEn' => $cat->name['en'] ?? '',
                'order' => $cat->order,
            ];
        });

        return self::successfulResponseWithData($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'parentId' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer',
        ]);

        $slug = Str::slug($request->input('nameEn'));

        // Ensure uniqueness of slug
        $count = Category::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug = "{$slug}-".($count + 1);
        }

        $category = Category::create([
            'name' => [
                'uk' => $request->input('nameUk'),
                'en' => $request->input('nameEn'),
            ],
            'slug' => $slug,
            'parent_id' => $request->input('parentId'),
            'order' => $request->input('order', 0),
        ]);

        return self::successfulResponseWithData([
            'id' => $category->id,
            'parentId' => $category->parent_id,
            'slug' => $category->slug,
            'nameUk' => $category->name['uk'] ?? '',
            'nameEn' => $category->name['en'] ?? '',
            'order' => $category->order,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'parentId' => 'nullable|exists:categories,id|different:id',
            'order' => 'nullable|integer',
        ]);

        $category->update([
            'name' => [
                'uk' => $request->input('nameUk'),
                'en' => $request->input('nameEn'),
            ],
            'parent_id' => $request->input('parentId'),
            'order' => $request->input('order', 0),
        ]);

        return self::successfulResponseWithData([
            'id' => $category->id,
            'parentId' => $category->parent_id,
            'slug' => $category->slug,
            'nameUk' => $category->name['uk'] ?? '',
            'nameEn' => $category->name['en'] ?? '',
            'order' => $category->order,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return self::successfulResponse();
    }
}
