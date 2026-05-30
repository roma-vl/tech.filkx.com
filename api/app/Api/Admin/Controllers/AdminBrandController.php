<?php

namespace App\Api\Admin\Controllers;

use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminBrandController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $brands = Brand::all()->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'logoPath' => $brand->logo_path,
                'description' => $brand->description,
            ];
        });

        return self::successfulResponseWithData($brands);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:brands,name',
            'logoPath' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $brand = Brand::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'logo_path' => $request->input('logoPath'),
            'description' => $request->input('description'),
        ]);

        return self::successfulResponseWithData([
            'id' => $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'logoPath' => $brand->logo_path,
            'description' => $brand->description,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:brands,name,'.$brand->id,
            'logoPath' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $brand->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'logo_path' => $request->input('logoPath'),
            'description' => $request->input('description'),
        ]);

        return self::successfulResponseWithData([
            'id' => $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'logoPath' => $brand->logo_path,
            'description' => $brand->description,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return self::successfulResponse();
    }
}
