<?php

namespace Tests\Unit\Actions\Admin\System;

use App\Api\Admin\Actions\System\GetSystemHealthAction;
use App\Api\Admin\Dto\System\SystemHealthDTO;
use Tests\TestCase;

class GetSystemHealthActionTest extends TestCase
{
    private GetSystemHealthAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetSystemHealthAction::class);
    }

    public function test_execute_returns_a_system_health_dto_with_the_expected_shape(): void
    {
        $result = $this->action->execute();

        $this->assertInstanceOf(SystemHealthDTO::class, $result);

        $this->assertArrayHasKey('cpu', $result->server);
        $this->assertArrayHasKey('ram', $result->server);
        $this->assertArrayHasKey('disk', $result->server);
        $this->assertArrayHasKey('uptime', $result->server);

        $this->assertArrayHasKey('percentage', $result->server['cpu']);
        $this->assertArrayHasKey('load', $result->server['cpu']);
        $this->assertArrayHasKey('cores', $result->server['cpu']);
        $this->assertGreaterThanOrEqual(1, $result->server['cpu']['cores']);

        $this->assertArrayHasKey('total', $result->server['ram']);
        $this->assertArrayHasKey('used', $result->server['ram']);
        $this->assertArrayHasKey('percentage', $result->server['ram']);

        $this->assertArrayHasKey('total', $result->server['disk']);
        $this->assertArrayHasKey('used', $result->server['disk']);
        $this->assertArrayHasKey('available', $result->server['disk']);
        $this->assertArrayHasKey('percentage', $result->server['disk']);
        $this->assertIsString($result->server['uptime']);

        $this->assertIsArray($result->network);
        $this->assertArrayHasKey('incoming', $result->network);
        $this->assertArrayHasKey('outgoing', $result->network);
        $this->assertArrayHasKey('max', $result->network);
    }

    public function test_execute_reports_four_fixed_services(): void
    {
        $result = $this->action->execute();

        $names = array_column($result->services, 'name');
        $this->assertSame(
            ['API Engine', 'Queue Worker', 'Storage', 'Mail Server'],
            $names
        );
        foreach ($result->services as $service) {
            $this->assertSame('active', $service['status']);
            $this->assertArrayHasKey('endpoint', $service);
            $this->assertArrayHasKey('latency', $service);
        }
    }

    public function test_execute_reports_database_as_offline_when_the_driver_does_not_support_show_max_connections(): void
    {
        // The test suite runs against sqlite (see phpunit.xml), which does not support the
        // Postgres-only "SHOW max_connections" query issued unconditionally by the action;
        // this documents the resulting (real, driver-dependent) fallback behaviour.
        $result = $this->action->execute();

        $this->assertSame('offline', $result->database['status']);
        $this->assertArrayHasKey('error', $result->database);
        $this->assertNotEmpty($result->database['error']);
    }

    public function test_to_array_maps_database_fields_to_camel_case(): void
    {
        $result = $this->action->execute();

        $array = $result->toArray();

        $this->assertArrayHasKey('status', $array['database']);
        $this->assertArrayHasKey('maxConnections', $array['database']);
        $this->assertArrayHasKey('error', $array['database']);
        $this->assertArrayHasKey('totalReceived', $array['network']);
        $this->assertArrayHasKey('totalSent', $array['network']);
    }
}
