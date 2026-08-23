<?php

namespace Tests\Unit\Actions\Delivery;

use App\Api\V1\Actions\Delivery\SearchDeliveryCitiesAction;
use App\Services\Delivery\NovaPoshtaService;
use Tests\TestCase;

class SearchDeliveryCitiesActionTest extends TestCase
{
    public function test_execute_passes_the_query_through_to_the_service(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('searchCities')->once()->with('Ки')->andReturn([]);
        });

        $result = app(SearchDeliveryCitiesAction::class)->execute('Ки');

        $this->assertSame([], $result);
    }

    public function test_execute_maps_delivery_city_present_and_area_when_present(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('searchCities')->once()->andReturn([
                [
                    'Ref' => 'settlement-ref-1',
                    'DeliveryCity' => 'city-ref-1',
                    'Present' => 'м. Київ, Київська обл.',
                    'MainDescription' => 'Київ',
                    'Area' => 'Київська',
                    'RegionsDescription' => 'Оболонський р-н',
                ],
            ]);
        });

        $result = app(SearchDeliveryCitiesAction::class)->execute('Ки');

        $this->assertSame([
            [
                'ref' => 'city-ref-1',
                'name' => 'м. Київ, Київська обл.',
                'area' => 'Київська',
            ],
        ], $result);
    }

    public function test_execute_falls_back_to_ref_main_description_and_regions_description(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('searchCities')->once()->andReturn([
                [
                    'Ref' => 'settlement-ref-1',
                    'MainDescription' => 'Київ',
                    'RegionsDescription' => 'Оболонський р-н',
                ],
            ]);
        });

        $result = app(SearchDeliveryCitiesAction::class)->execute('Ки');

        $this->assertSame([
            [
                'ref' => 'settlement-ref-1',
                'name' => 'Київ',
                'area' => 'Оболонський р-н',
            ],
        ], $result);
    }

    public function test_execute_defaults_missing_fields_to_empty_strings(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('searchCities')->once()->andReturn([[]]);
        });

        $result = app(SearchDeliveryCitiesAction::class)->execute('Ки');

        $this->assertSame([
            ['ref' => '', 'name' => '', 'area' => ''],
        ], $result);
    }

    public function test_execute_maps_every_city_in_the_result_set(): void
    {
        $this->mock(NovaPoshtaService::class, function ($mock) {
            $mock->shouldReceive('searchCities')->once()->andReturn([
                ['DeliveryCity' => 'city-ref-1', 'Present' => 'Київ', 'Area' => 'Київська'],
                ['DeliveryCity' => 'city-ref-2', 'Present' => 'Львів', 'Area' => 'Львівська'],
            ]);
        });

        $result = app(SearchDeliveryCitiesAction::class)->execute('в');

        $this->assertCount(2, $result);
        $this->assertSame('city-ref-1', $result[0]['ref']);
        $this->assertSame('city-ref-2', $result[1]['ref']);
    }
}
