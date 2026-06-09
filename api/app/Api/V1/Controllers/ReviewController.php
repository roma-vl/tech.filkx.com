<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReviewController extends BaseApiController
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function index(string $slug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404, 'Product not found.');
        }

        $reviews = ProductReview::with('user:id,name')
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'title' => $r->title,
                'body' => $r->body,
                'photos' => $r->photos ?? [],
                'author' => $r->user?->name ?? 'Анонім',
                'created_at' => $r->created_at->toISOString(),
            ]);

        $stats = [
            'count' => $reviews->count(),
            'avg' => $reviews->count() ? round($reviews->avg('rating'), 1) : 0,
            'distribution' => array_map(
                fn ($star) => $reviews->where('rating', $star)->count(),
                [5, 4, 3, 2, 1]
            ),
        ];

        return self::successfulResponseWithData([
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404, 'Product not found.');
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'body' => 'required|string|min:10|max:2000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'order_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userId = Auth::id();

        // Prevent duplicate review per product per user
        $existing = ProductReview::where('product_id', $product->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ви вже залишали відгук на цей товар.',
            ], 422);
        }

        // Upload photos
        $photoUrls = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store("reviews/{$product->id}", 'public');
                $photoUrls[] = url(Storage::url($path));
            }
        }

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'order_id' => $request->integer('order_id') ?: null,
            'rating' => (int) $request->input('rating'),
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'photos' => $photoUrls ?: null,
            'status' => 'approved',
        ]);

        return self::successfulResponseWithData([
            'id' => $review->id,
            'rating' => $review->rating,
            'title' => $review->title,
            'body' => $review->body,
            'photos' => $review->photos ?? [],
            'author' => Auth::user()?->name ?? 'Анонім',
            'created_at' => $review->created_at->toISOString(),
        ], 201);
    }

    public function update(Request $request, string $slug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404, 'Product not found.');
        }

        $review = ProductReview::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'body' => 'required|string|min:10|max:2000',
            'existing_photos' => 'nullable|array',
            'existing_photos.*' => 'nullable|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Keep URLs the user explicitly kept, add new uploads
        $keptPhotos = $request->input('existing_photos', []);
        $newPhotoUrls = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store("reviews/{$product->id}", 'public');
                $newPhotoUrls[] = url(Storage::url($path));
            }
        }
        $allPhotos = array_merge($keptPhotos, $newPhotoUrls);

        $review->update([
            'rating' => (int) $request->input('rating'),
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'photos' => $allPhotos ?: null,
        ]);

        return self::successfulResponseWithData([
            'id' => $review->id,
            'rating' => $review->rating,
            'title' => $review->title,
            'body' => $review->body,
            'photos' => $review->photos ?? [],
            'author' => Auth::user()?->name ?? 'Анонім',
            'created_at' => $review->created_at->toISOString(),
        ]);
    }

    public function myReviews(): JsonResponse
    {
        $reviews = ProductReview::with('product:id,slug')
            ->where('user_id', Auth::id())
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'product_slug' => $r->product?->slug,
                'product_id' => $r->product_id,
                'rating' => $r->rating,
                'title' => $r->title,
                'body' => $r->body,
                'photos' => $r->photos ?? [],
                'created_at' => $r->created_at->toISOString(),
            ]);

        return self::successfulResponseWithData($reviews);
    }
}
