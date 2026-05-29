<?php

namespace App\Api\Admin\Actions\Support;

use App\Models\SupportTicket;

class GetSupportStatsAction
{
    public function execute(): array
    {
        $last30Days = now()->subDays(30);

        $totalTickets = SupportTicket::where('created_at', '>=', $last30Days)->count();
        $resolvedTickets = SupportTicket::where('status', 'done')
            ->where('updated_at', '>=', $last30Days)
            ->count();

        // Tickets by day
        $chartData = SupportTicket::selectRaw('DATE(created_at) as date, count(*) as total')
            ->where('created_at', '>=', $last30Days)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'totalTickets' => $totalTickets,
            'resolvedTickets' => $resolvedTickets,
            'chartData' => $chartData,
        ];
    }
}
