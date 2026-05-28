<?php

namespace App\Api\Admin\Actions;

use App\Models\SupportTicket;
use App\Models\User;

class GetAdminStatsAction
{
    /**
     * Execute the action to get all admin statistics
     */
    public function execute(): array
    {
        $totalUsers = User::count();

        $recentUsers = User::latest()
            ->take(5)
            ->get();

        $unreadTickets = SupportTicket::count();

        return [
            'stats' => [
                [
                    'label' => 'Total Users',
                    'value' => number_format($totalUsers),
                    'trend' => 12.5,
                    'icon' => 'UsersIcon',
                    'bg_class' => 'bg-blue-500',
                ],
                [
                    'label' => 'Total Orders',
                    'value' => '142',
                    'trend' => 8.2,
                    'icon' => 'ShoppingCartIcon',
                    'bg_class' => 'bg-purple-500',
                ],
                [
                    'label' => 'Total Products',
                    'value' => '24',
                    'trend' => 4.1,
                    'icon' => 'ShoppingBagIcon',
                    'bg_class' => 'bg-green-500',
                ],
                [
                    'label' => 'Monthly Revenue',
                    'value' => '₴185,400',
                    'trend' => 15.3,
                    'icon' => 'BanknotesIcon',
                    'bg_class' => 'bg-orange-500',
                ],
            ],
            'recent_users' => $recentUsers,
            'unread_tickets' => $unreadTickets,
        ];
    }
}
