<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\GetAdminStatsAction;
use App\Api\Admin\Requests\StatsRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminStatsController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/stats",
     *     summary="Get administrative statistics overview",
     *     description="Returns high-level statistics for users, streams, videos, and revenue, including trends and recent activity.",
     *     operationId="getAdminStats",
     *     tags={"Admin Settings"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="stats", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="label", type="string", example="Total Users"),
     *                         @OA\Property(property="value", type="string", example="1,234"),
     *                         @OA\Property(property="trend", type="number", format="float", example=5.2),
     *                         @OA\Property(property="icon", type="string", example="UsersIcon"),
     *                         @OA\Property(property="bgClass", type="string", example="bg-blue-500")
     *                     )
     *                 ),
     *                 @OA\Property(property="recentUsers", type="array",
     *
     *                     @OA\Items(
     *
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="plan", type="string", example="Premium"),
     *                         @OA\Property(property="joined", type="string", example="2 hours ago")
     *                     )
     *                 ),
     *                 @OA\Property(property="unreadTickets", type="integer", example=3)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="This action is unauthorized.")
     *         )
     *     )
     * )
     */
    public function index(StatsRequest $request, GetAdminStatsAction $action): JsonResponse
    {
        $stats = $action->execute();

        return self::successfulResponseWithData($stats);
    }

    public function overview(): JsonResponse
    {
        $totalUsers = User::count();
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');

        $data = [
            'overview' => [
                ['label' => 'Total Customers', 'value' => number_format($totalUsers),   'trend' => 12.5, 'icon' => 'UsersIcon',          'bgClass' => 'bg-blue-500'],
                ['label' => 'Orders Completed', 'value' => number_format($totalOrders),  'trend' => 8.2,  'icon' => 'CheckBadgeIcon',      'bgClass' => 'bg-green-500'],
                ['label' => 'Total Revenue',   'value' => '₴'.number_format($totalRevenue, 2), 'trend' => 15.3, 'icon' => 'BanknotesIcon', 'bgClass' => 'bg-orange-500'],
                ['label' => 'Products Active', 'value' => number_format($totalProducts), 'trend' => 4.1,  'icon' => 'Square3Stack3DIcon',  'bgClass' => 'bg-purple-500'],
            ],
        ];

        return self::successfulResponseWithData($data);
    }

    public function charts(): JsonResponse
    {
        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        $revenueData = [12000, 19000, 3000, 5000, 2000, 3000, 45000];
        $ordersData = [12, 19, 3, 5, 2, 3, 45];
        $signupsData = [5, 10, 15, 8, 12, 6, 20];

        $data = [
            'labels' => $labels,
            'datasets' => [
                'revenue' => $revenueData,
                'streams' => $ordersData,
                'signups' => $signupsData,
            ],
        ];

        return self::successfulResponseWithData($data);
    }

    public function distributions(): JsonResponse
    {
        $categories = Category::take(5)->get();
        $plans = [];

        if ($categories->count() > 0) {
            foreach ($categories as $category) {
                $nameArray = $category->name;
                $label = is_array($nameArray) ? ($nameArray['uk'] ?? $nameArray['en'] ?? $category->slug) : $category->slug;
                $plans[] = [
                    'label' => $label,
                    'value' => Product::whereHas('categories', function ($query) use ($category) {
                        $query->where('categories.id', $category->id);
                    })->count() ?: rand(5, 20),
                ];
            }
        } else {
            $plans = [
                ['label' => 'Смартфони', 'value' => 45],
                ['label' => 'Ноутбуки', 'value' => 30],
                ['label' => 'Аксесуари', 'value' => 15],
                ['label' => 'Побутова техніка', 'value' => 10],
            ];
        }

        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $processingOrders = Order::where('status', 'processing')->count();

        if ($pendingOrders + $completedOrders + $cancelledOrders + $processingOrders === 0) {
            $pendingOrders = 15;
            $completedOrders = 65;
            $cancelledOrders = 5;
            $processingOrders = 10;
        }

        $content = [
            ['label' => 'Completed', 'value' => $completedOrders],
            ['label' => 'Pending', 'value' => $pendingOrders],
            ['label' => 'Processing', 'value' => $processingOrders],
            ['label' => 'Cancelled', 'value' => $cancelledOrders],
        ];

        return self::successfulResponseWithData([
            'plans' => $plans,
            'content' => $content,
        ]);
    }
}
