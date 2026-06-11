<?php

namespace App\Api\Admin\Controllers;

use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminPageController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Page::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where('slug', 'like', "%{$search}%")
                  ->orWhereRaw("title->>'uk' ILIKE ?", ['%'.$search.'%'])
                  ->orWhereRaw("title->>'en' ILIKE ?", ['%'.$search.'%']);
            })
            ->orderBy('id', 'desc');

        $paginated = $query->paginate($request->input('per_page', 20));

        return self::successfulResponseWithData([
            'data' => $paginated->map(fn ($page) => $this->formatPage($page)),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $page = Page::findOrFail($id);
        return self::successfulResponseWithData($this->formatPage($page, true));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'titleUk' => 'required|string|max:500',
            'titleEn' => 'required|string|max:500',
            'contentUk' => 'required|string',
            'contentEn' => 'required|string',
            'slug' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published',
        ]);

        $slug = $request->input('slug') ? Str::slug($request->input('slug')) : Str::slug($request->input('titleEn'));
        if (!$slug) {
            $slug = 'page-' . time();
        }

        // Ensure uniqueness
        $originalSlug = $slug;
        $count = 1;
        while (Page::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $page = Page::create([
            'slug' => $slug,
            'title' => [
                'uk' => $request->input('titleUk'),
                'en' => $request->input('titleEn'),
            ],
            'content' => [
                'uk' => $request->input('contentUk'),
                'en' => $request->input('contentEn'),
            ],
            'status' => $request->input('status', 'published'),
        ]);

        return self::successfulResponseWithData($this->formatPage($page, true), Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $page = Page::findOrFail($id);

        $request->validate([
            'titleUk' => 'required|string|max:500',
            'titleEn' => 'required|string|max:500',
            'contentUk' => 'required|string',
            'contentEn' => 'required|string',
            'slug' => 'required|string|max:255',
            'status' => 'required|in:draft,published',
        ]);

        $slug = Str::slug($request->input('slug'));
        // Ensure uniqueness excluding this page
        $originalSlug = $slug;
        $count = 1;
        while (Page::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $page->update([
            'slug' => $slug,
            'title' => [
                'uk' => $request->input('titleUk'),
                'en' => $request->input('titleEn'),
            ],
            'content' => [
                'uk' => $request->input('contentUk'),
                'en' => $request->input('contentEn'),
            ],
            'status' => $request->input('status'),
        ]);

        return self::successfulResponseWithData($this->formatPage($page, true));
    }

    public function destroy(int $id): JsonResponse
    {
        $page = Page::findOrFail($id);
        $page->delete();
        return self::successfulResponse();
    }

    private function formatPage(Page $page, bool $withContent = false): array
    {
        $data = [
            'id' => $page->id,
            'slug' => $page->slug,
            'titleUk' => $page->title['uk'] ?? '',
            'titleEn' => $page->title['en'] ?? '',
            'status' => $page->status,
            'createdAt' => $page->created_at->toIso8601String(),
            'updatedAt' => $page->updated_at->toIso8601String(),
        ];

        if ($withContent) {
            $data['contentUk'] = $page->content['uk'] ?? '';
            $data['contentEn'] = $page->content['en'] ?? '';
        }

        return $data;
    }
}
