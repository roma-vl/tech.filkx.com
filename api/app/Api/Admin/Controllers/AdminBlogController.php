<?php

namespace App\Api\Admin\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminBlogController extends BaseApiController
{
    // ─── Posts ────────────────────────────────────────────────────────────────

    public function posts(Request $request): JsonResponse
    {
        $query = BlogPost::with(['category', 'author', 'tags'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('category_id'), fn ($q) => $q->where('blog_category_id', $request->category_id))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->whereRaw("title->>'uk' ILIKE ?", ['%'.$request->search.'%'])
                   ->orWhereRaw("title->>'en' ILIKE ?", ['%'.$request->search.'%']);
            }))
            ->orderByDesc('created_at');

        $paginated = $query->paginate($request->input('per_page', 20));

        return self::successfulResponseWithData([
            'data' => $paginated->map(fn ($post) => $this->formatPost($post)),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function showPost(int $id): JsonResponse
    {
        $post = BlogPost::with(['category', 'author', 'tags'])->findOrFail($id);
        return self::successfulResponseWithData($this->formatPost($post, true));
    }

    public function storePost(Request $request): JsonResponse
    {
        $request->validate([
            'titleUk' => 'required|string|max:500',
            'titleEn' => 'required|string|max:500',
            'contentUk' => 'required|string',
            'contentEn' => 'required|string',
            'excerptUk' => 'nullable|string|max:1000',
            'excerptEn' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published,archived',
            'categoryId' => 'nullable|exists:blog_categories,id',
            'tagIds' => 'nullable|array',
            'tagIds.*' => 'exists:blog_tags,id',
            'coverImage' => 'nullable|string',
            'publishedAt' => 'nullable|date',
        ]);

        $slug = Str::slug($request->input('titleEn'));
        $count = BlogPost::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug = "{$slug}-".($count + 1);
        }

        $post = BlogPost::create([
            'blog_category_id' => $request->input('categoryId'),
            'author_id' => Auth::id(),
            'slug' => $slug,
            'title' => ['uk' => $request->input('titleUk'), 'en' => $request->input('titleEn')],
            'excerpt' => ['uk' => $request->input('excerptUk', ''), 'en' => $request->input('excerptEn', '')],
            'content' => ['uk' => $request->input('contentUk'), 'en' => $request->input('contentEn')],
            'cover_image' => $request->input('coverImage'),
            'status' => $request->input('status', 'draft'),
            'published_at' => $request->input('status') === 'published' ? ($request->input('publishedAt') ?? now()) : $request->input('publishedAt'),
        ]);

        if ($request->filled('tagIds')) {
            $post->tags()->sync($request->input('tagIds'));
        }

        $post->load(['category', 'author', 'tags']);
        return self::successfulResponseWithData($this->formatPost($post, true), Response::HTTP_CREATED);
    }

    public function updatePost(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);

        $request->validate([
            'titleUk' => 'required|string|max:500',
            'titleEn' => 'required|string|max:500',
            'contentUk' => 'required|string',
            'contentEn' => 'required|string',
            'excerptUk' => 'nullable|string|max:1000',
            'excerptEn' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published,archived',
            'categoryId' => 'nullable|exists:blog_categories,id',
            'tagIds' => 'nullable|array',
            'tagIds.*' => 'exists:blog_tags,id',
            'coverImage' => 'nullable|string',
            'publishedAt' => 'nullable|date',
        ]);

        $wasPublished = $post->status !== 'published' && $request->input('status') === 'published';

        $post->update([
            'blog_category_id' => $request->input('categoryId'),
            'title' => ['uk' => $request->input('titleUk'), 'en' => $request->input('titleEn')],
            'excerpt' => ['uk' => $request->input('excerptUk', ''), 'en' => $request->input('excerptEn', '')],
            'content' => ['uk' => $request->input('contentUk'), 'en' => $request->input('contentEn')],
            'cover_image' => $request->input('coverImage'),
            'status' => $request->input('status'),
            'published_at' => $wasPublished ? now() : ($request->input('publishedAt') ?? $post->published_at),
        ]);

        $post->tags()->sync($request->input('tagIds', []));
        $post->load(['category', 'author', 'tags']);

        return self::successfulResponseWithData($this->formatPost($post, true));
    }

    public function destroyPost(int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();
        return self::successfulResponse();
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $path = $request->file('image')->store('blog', 'public');
        $url = Storage::disk('public')->url($path);

        return self::successfulResponseWithData(['url' => $url]);
    }

    // ─── Categories ───────────────────────────────────────────────────────────

    public function categories(): JsonResponse
    {
        $cats = BlogCategory::withCount('posts')->orderBy('order')->get()
            ->map(fn ($c) => $this->formatCategory($c));
        return self::successfulResponseWithData($cats);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $request->validate([
            'nameUk' => 'required|string|max:255',
            'nameEn' => 'required|string|max:255',
            'descriptionUk' => 'nullable|string',
            'descriptionEn' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $slug = Str::slug($request->input('nameEn'));
        $count = BlogCategory::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug = "{$slug}-".($count + 1);
        }

        $cat = BlogCategory::create([
            'slug' => $slug,
            'name' => ['uk' => $request->input('nameUk'), 'en' => $request->input('nameEn')],
            'description' => ['uk' => $request->input('descriptionUk', ''), 'en' => $request->input('descriptionEn', '')],
            'order' => $request->input('order', 0),
        ]);

        return self::successfulResponseWithData($this->formatCategory($cat), Response::HTTP_CREATED);
    }

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        $cat = BlogCategory::findOrFail($id);

        $request->validate([
            'nameUk' => 'required|string|max:255',
            'nameEn' => 'required|string|max:255',
            'descriptionUk' => 'nullable|string',
            'descriptionEn' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $cat->update([
            'name' => ['uk' => $request->input('nameUk'), 'en' => $request->input('nameEn')],
            'description' => ['uk' => $request->input('descriptionUk', ''), 'en' => $request->input('descriptionEn', '')],
            'order' => $request->input('order', 0),
        ]);

        return self::successfulResponseWithData($this->formatCategory($cat->loadCount('posts')));
    }

    public function destroyCategory(int $id): JsonResponse
    {
        $cat = BlogCategory::findOrFail($id);
        $cat->delete();
        return self::successfulResponse();
    }

    // ─── Tags ─────────────────────────────────────────────────────────────────

    public function tags(): JsonResponse
    {
        $tags = BlogTag::withCount('posts')->orderBy('id', 'desc')->get()
            ->map(fn ($t) => $this->formatTag($t));
        return self::successfulResponseWithData($tags);
    }

    public function storeTag(Request $request): JsonResponse
    {
        $request->validate([
            'nameUk' => 'required|string|max:100',
            'nameEn' => 'required|string|max:100',
        ]);

        $slug = Str::slug($request->input('nameEn'));
        $count = BlogTag::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug = "{$slug}-".($count + 1);
        }

        $tag = BlogTag::create([
            'slug' => $slug,
            'name' => ['uk' => $request->input('nameUk'), 'en' => $request->input('nameEn')],
        ]);

        return self::successfulResponseWithData($this->formatTag($tag), Response::HTTP_CREATED);
    }

    public function updateTag(Request $request, int $id): JsonResponse
    {
        $tag = BlogTag::findOrFail($id);

        $request->validate([
            'nameUk' => 'required|string|max:100',
            'nameEn' => 'required|string|max:100',
        ]);

        $tag->update([
            'name' => ['uk' => $request->input('nameUk'), 'en' => $request->input('nameEn')],
        ]);

        return self::successfulResponseWithData($this->formatTag($tag->loadCount('posts')));
    }

    public function destroyTag(int $id): JsonResponse
    {
        $tag = BlogTag::findOrFail($id);
        $tag->delete();
        return self::successfulResponse();
    }

    // ─── Formatters ───────────────────────────────────────────────────────────

    private function formatPost(BlogPost $post, bool $withContent = false): array
    {
        $data = [
            'id' => $post->id,
            'slug' => $post->slug,
            'titleUk' => $post->title['uk'] ?? '',
            'titleEn' => $post->title['en'] ?? '',
            'excerptUk' => $post->excerpt['uk'] ?? '',
            'excerptEn' => $post->excerpt['en'] ?? '',
            'coverImage' => $post->cover_image,
            'status' => $post->status,
            'views' => $post->views,
            'publishedAt' => $post->published_at?->toIso8601String(),
            'createdAt' => $post->created_at->toIso8601String(),
            'categoryId' => $post->blog_category_id,
            'categoryName' => $post->category ? ($post->category->name['uk'] ?? $post->category->name['en'] ?? '') : null,
            'authorName' => $post->author ? $post->author->name : null,
            'tags' => $post->tags->map(fn ($t) => ['id' => $t->id, 'nameUk' => $t->name['uk'] ?? '', 'nameEn' => $t->name['en'] ?? '', 'slug' => $t->slug])->values(),
        ];

        if ($withContent) {
            $data['contentUk'] = $post->content['uk'] ?? '';
            $data['contentEn'] = $post->content['en'] ?? '';
        }

        return $data;
    }

    private function formatCategory(BlogCategory $cat): array
    {
        return [
            'id' => $cat->id,
            'slug' => $cat->slug,
            'nameUk' => $cat->name['uk'] ?? '',
            'nameEn' => $cat->name['en'] ?? '',
            'descriptionUk' => $cat->description['uk'] ?? '',
            'descriptionEn' => $cat->description['en'] ?? '',
            'order' => $cat->order,
            'postsCount' => $cat->posts_count ?? 0,
        ];
    }

    private function formatTag(BlogTag $tag): array
    {
        return [
            'id' => $tag->id,
            'slug' => $tag->slug,
            'nameUk' => $tag->name['uk'] ?? '',
            'nameEn' => $tag->name['en'] ?? '',
            'postsCount' => $tag->posts_count ?? 0,
        ];
    }
}
