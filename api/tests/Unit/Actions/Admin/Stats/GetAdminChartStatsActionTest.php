<?php

namespace Tests\Unit\Actions\Admin\Stats;

use App\Api\Admin\Actions\Stats\GetAdminChartStatsAction;
use Tests\TestCase;

class GetAdminChartStatsActionTest extends TestCase
{
    public function test_execute_returns_seven_days_of_data_for_each_series(): void
    {
        $result = app(GetAdminChartStatsAction::class)->execute();

        $this->assertCount(7, $result['labels']);
        $this->assertCount(7, $result['datasets']['revenue']);
        $this->assertCount(7, $result['datasets']['streams']);
        $this->assertCount(7, $result['datasets']['signups']);
    }
}
