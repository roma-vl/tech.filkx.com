<?php

namespace Tests\Unit\Actions\Delivery;

use App\Api\V1\Actions\Delivery\SearchDeliveryWarehousesAction;
use App\Services\Delivery\NovaPoshtaService;
use Tests\TestCase;

class SearchDeliveryWarehousesActionTest extends TestCase
{
    public function test_execute_passes_the_city_ref_and_query_through_to_the_service(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('getWarehouses')->once()->with('city-ref-1', '14')->andReturn([]);
        });

        $result = app(SearchDeliveryWarehousesAction::class)->execute('city-ref-1', '14');

        $this->assertSame([], $result);
    }

    public function test_execute_passes_a_null_query_through_when_none_is_given(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('getWarehouses')->once()->with('city-ref-1', null)->andReturn([]);
        });

        app(SearchDeliveryWarehousesAction::class)->execute('city-ref-1');
    }

    public function test_execute_maps_ref_number_and_description(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('getWarehouses')->once()->andReturn([
                [
                    'Ref' => 'warehouse-ref-1',
                    'Number' => '14',
                    'Description' => 'Відділення №14: вул. Хрещатик, 1',
                    'ShortAddress' => 'вул. Хрещатик, 1',
                ],
            ]);
        });

        $result = app(SearchDeliveryWarehousesAction::class)->execute('city-ref-1');

        $this->assertSame([
            [
                'ref' => 'warehouse-ref-1',
                'number' => '14',
                'description' => 'Відділення №14: вул. Хрещатик, 1',
            ],
        ], $result);
    }

    public function test_execute_falls_back_to_short_address_when_description_is_missing(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('getWarehouses')->once()->andReturn([
                [
                    'Ref' => 'warehouse-ref-1',
                    'Number' => '14',
                    'ShortAddress' => 'вул. Хрещатик, 1',
                ],
            ]);
        });

        $result = app(SearchDeliveryWarehousesAction::class)->execute('city-ref-1');

        $this->assertSame('вул. Хрещатик, 1', $result[0]['description']);
    }

    public function test_execute_defaults_missing_fields_to_empty_strings(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('getWarehouses')->once()->andReturn([[]]);
        });

        $result = app(SearchDeliveryWarehousesAction::class)->execute('city-ref-1');

        $this->assertSame([
            ['ref' => '', 'number' => '', 'description' => ''],
        ], $result);
    }

    public function test_execute_maps_every_warehouse_in_the_result_set(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('getWarehouses')->once()->andReturn([
                ['Ref' => 'warehouse-ref-1', 'Number' => '1', 'Description' => 'Відділення №1'],
                ['Ref' => 'warehouse-ref-2', 'Number' => '2', 'Description' => 'Відділення №2'],
            ]);
        });

        $result = app(SearchDeliveryWarehousesAction::class)->execute('city-ref-1');

        $this->assertCount(2, $result);
        $this->assertSame('warehouse-ref-1', $result[0]['ref']);
        $this->assertSame('warehouse-ref-2', $result[1]['ref']);
    }
}
