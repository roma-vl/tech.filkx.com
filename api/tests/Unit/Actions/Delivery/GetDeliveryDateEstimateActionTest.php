<?php

namespace Tests\Unit\Actions\Delivery;

use App\Api\V1\Actions\Delivery\GetDeliveryDateEstimateAction;
use App\Api\V1\Exceptions\DeliveryProviderUnavailableException;
use App\Services\Delivery\NovaPoshtaService;
use Tests\TestCase;

class GetDeliveryDateEstimateActionTest extends TestCase
{
    public function test_execute_returns_unavailable_when_the_service_cannot_estimate(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('canEstimateDeliveryDate')->once()->andReturn(false);
            $mock->shouldNotReceive('getDeliveryDateEstimate');
        });

        $result = app(GetDeliveryDateEstimateAction::class)->execute('city-ref-1');

        $this->assertSame(['available' => false, 'date' => null], $result);
    }

    public function test_execute_returns_unavailable_when_the_service_throws(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('canEstimateDeliveryDate')->once()->andReturn(true);
            $mock->shouldReceive('getDeliveryDateEstimate')
                ->once()
                ->with('city-ref-1')
                ->andThrow(new DeliveryProviderUnavailableException);
        });

        $result = app(GetDeliveryDateEstimateAction::class)->execute('city-ref-1');

        $this->assertSame(['available' => false, 'date' => null], $result);
    }

    public function test_execute_returns_unavailable_when_the_delivery_date_key_is_missing(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('canEstimateDeliveryDate')->once()->andReturn(true);
            $mock->shouldReceive('getDeliveryDateEstimate')->once()->andReturn([]);
        });

        $result = app(GetDeliveryDateEstimateAction::class)->execute('city-ref-1');

        $this->assertSame(['available' => false, 'date' => null], $result);
    }

    public function test_execute_returns_the_formatted_date_when_available(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('canEstimateDeliveryDate')->once()->andReturn(true);
            $mock->shouldReceive('getDeliveryDateEstimate')
                ->once()
                ->with('city-ref-1')
                ->andReturn([
                    'DeliveryDate' => ['date' => '2026-08-28 00:00:00.000000'],
                ]);
        });

        $result = app(GetDeliveryDateEstimateAction::class)->execute('city-ref-1');

        $this->assertSame(['available' => true, 'date' => '2026-08-28'], $result);
    }

    public function test_execute_returns_unavailable_when_the_returned_date_is_unparsable(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('canEstimateDeliveryDate')->once()->andReturn(true);
            $mock->shouldReceive('getDeliveryDateEstimate')
                ->once()
                ->andReturn(['DeliveryDate' => ['date' => 'not-a-date']]);
        });

        $result = app(GetDeliveryDateEstimateAction::class)->execute('city-ref-1');

        $this->assertSame(['available' => false, 'date' => null], $result);
    }
}
