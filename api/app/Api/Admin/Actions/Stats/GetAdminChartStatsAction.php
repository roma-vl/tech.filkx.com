<?php

namespace App\Api\Admin\Actions\Stats;

class GetAdminChartStatsAction
{
    public function execute(): array
    {
        return [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'datasets' => [
                'revenue' => [12000, 19000, 3000, 5000, 2000, 3000, 45000],
                'streams' => [12, 19, 3, 5, 2, 3, 45],
                'signups' => [5, 10, 15, 8, 12, 6, 20],
            ],
        ];
    }
}
