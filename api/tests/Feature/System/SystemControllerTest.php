<?php

namespace Tests\Feature\System;

use Tests\TestCase;

class SystemControllerTest extends TestCase
{
    public function test_status_reports_maintenance_mode_version_and_timestamp(): void
    {
        $response = $this->getJson('/api/system/status');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.maintenanceMode', false)
            ->assertJsonStructure(['data' => ['maintenanceMode', 'version', 'timestamp']]);
    }
}
