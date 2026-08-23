<?php

namespace Tests\Unit\Actions\Delivery;

use App\Api\V1\Actions\Delivery\GetDeliveryAvailabilityAction;
use App\Services\Delivery\NovaPoshtaService;
use Tests\TestCase;

class GetDeliveryAvailabilityActionTest extends TestCase
{
    public function test_execute_returns_available_true_when_the_service_is_configured(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        });

        $result = app(GetDeliveryAvailabilityAction::class)->execute();

        $this->assertSame(['available' => true], $result);
    }

    public function test_execute_returns_available_false_when_the_service_is_not_configured(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(false);
        });

        $result = app(GetDeliveryAvailabilityAction::class)->execute();

        $this->assertSame(['available' => false], $result);
    }
}
