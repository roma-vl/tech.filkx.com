<?php

namespace App\Api\Admin\Actions\Stats;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class GetAdminOverviewStatsAction
{
    public function execute(): array
    {
        $totalUsers = User::count();
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');

        return [
            'overview' => [
                ['label' => 'Total Customers', 'value' => number_format($totalUsers), 'trend' => 12.5, 'icon' => 'UsersIcon', 'bgClass' => 'bg-blue-500'],
                ['label' => 'Orders Completed', 'value' => number_format($totalOrders), 'trend' => 8.2, 'icon' => 'CheckBadgeIcon', 'bgClass' => 'bg-green-500'],
                ['label' => 'Total Revenue', 'value' => '₴'.number_format($totalRevenue, 2), 'trend' => 15.3, 'icon' => 'BanknotesIcon', 'bgClass' => 'bg-orange-500'],
                ['label' => 'Products Active', 'value' => number_format($totalProducts), 'trend' => 4.1, 'icon' => 'Square3Stack3DIcon', 'bgClass' => 'bg-purple-500'],
            ],
        ];
    }
}
