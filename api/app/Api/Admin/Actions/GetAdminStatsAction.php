<?php

namespace App\Api\Admin\Actions;

use App\Models\Billing\Payment;
use App\Models\StreamSession;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Video;

class GetAdminStatsAction
{
    /**
     * Execute the action to get all admin statistics
     */
    public function execute(): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth();
        $startOfLastMonth = $lastMonth->copy()->startOfMonth();
        $totalUsers = User::count();
        $usersLastMonth = User::where('created_at', '<', $startOfMonth)->count();
        $userTrend = $this->calculateTrend($totalUsers, $usersLastMonth);
        $activeStreams = StreamSession::where('status', 'live')->count();
        $streamsYesterday = StreamSession::where('status', 'live')
            ->where('created_at', '<', $now->copy()->subDay())
            ->count();
        $streamTrend = $this->calculateTrend($activeStreams, $streamsYesterday);

        $totalVideos = Video::count();
        $videosLastMonth = Video::where('created_at', '<', $startOfMonth)->count();
        $videoTrend = $this->calculateTrend($totalVideos, $videosLastMonth);
        $monthlyRevenue = Payment::completed()->thisMonth()->sum('amount');
        $revenueLastMonth = Payment::completed()
            ->whereBetween('processed_at', [$startOfLastMonth, $startOfMonth])
            ->sum('amount');
        $revenueTrend = $this->calculateTrend($monthlyRevenue, $revenueLastMonth);

        $recentUsers = User::with(['subscription.plan'])
            ->latest()
            ->take(5)
            ->get();

        $unreadTickets = SupportTicket::whereHas('unreadMessagesForAdmin')->count();

        return [
            'stats' => [
                [
                    'label' => 'Total Users',
                    'value' => number_format($totalUsers),
                    'trend' => round($userTrend, 1),
                    'icon' => 'UsersIcon',
                    'bg_class' => 'bg-blue-500',
                ],
                [
                    'label' => 'Active Streams',
                    'value' => number_format($activeStreams),
                    'trend' => round($streamTrend, 1),
                    'icon' => 'SignalIcon',
                    'bg_class' => 'bg-purple-500',
                ],
                [
                    'label' => 'Total Videos',
                    'value' => number_format($totalVideos),
                    'trend' => round($videoTrend, 1),
                    'icon' => 'VideoCameraIcon',
                    'bg_class' => 'bg-green-500',
                ],
                [
                    'label' => 'Monthly Revenue',
                    'value' => '$'.number_format($monthlyRevenue, 2),
                    'trend' => round($revenueTrend, 1),
                    'icon' => 'BanknotesIcon',
                    'bg_class' => 'bg-orange-500',
                ],
            ],
            'recent_users' => $recentUsers,
            'unread_tickets' => $unreadTickets,
        ];
    }

    /**
     * Calculate trend percentage
     */
    private function calculateTrend(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return 0;
        }

        return (($current - $previous) / $previous) * 100;
    }
}
