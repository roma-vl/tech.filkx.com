<?php

namespace Tests\Unit\Services\Delivery;

use App\Api\V1\Exceptions\DeliveryProviderUnavailableException;
use App\Services\Delivery\NovaPoshtaService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NovaPoshtaServiceTest extends TestCase
{
    private NovaPoshtaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new NovaPoshtaService;
        config(['services.nova_poshta.api_key' => null]);
    }

    public function test_is_configured_returns_false_when_api_key_is_not_set(): void
    {
        $this->assertFalse($this->service->isConfigured());
    }

    public function test_is_configured_returns_true_when_api_key_is_set(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        $this->assertTrue($this->service->isConfigured());
    }

    public function test_search_cities_throws_without_making_an_http_call_when_not_configured(): void
    {
        Http::fake();

        $this->expectException(DeliveryProviderUnavailableException::class);

        try {
            $this->service->searchCities('Ки');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_get_warehouses_throws_without_making_an_http_call_when_not_configured(): void
    {
        Http::fake();

        $this->expectException(DeliveryProviderUnavailableException::class);

        try {
            $this->service->getWarehouses('city-ref-1');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_search_cities_sends_the_expected_request_payload(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        $this->service->searchCities('Ки');

        Http::assertSent(function ($request) {
            return $request->url() === NovaPoshtaService::API_URL
                && $request['apiKey'] === 'test-key'
                && $request['modelName'] === 'Address'
                && $request['calledMethod'] === 'searchSettlements'
                && $request['methodProperties'] === ['CityName' => 'Ки', 'Limit' => 20];
        });
    }

    public function test_search_cities_returns_the_addresses_from_the_first_result_entry(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        $addresses = [
            ['Ref' => 'settlement-ref-1', 'DeliveryCity' => 'city-ref-1', 'Present' => 'м. Київ'],
        ];

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => true,
                'data' => [
                    ['Addresses' => $addresses],
                ],
            ]),
        ]);

        $result = $this->service->searchCities('Ки');

        $this->assertSame($addresses, $result);
    }

    public function test_search_cities_returns_an_empty_array_when_the_addresses_key_is_missing(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => true,
                'data' => [[]],
            ]),
        ]);

        $this->assertSame([], $this->service->searchCities('Ки'));
    }

    public function test_get_warehouses_sends_the_expected_request_payload_without_a_query(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        $this->service->getWarehouses('city-ref-1');

        Http::assertSent(function ($request) {
            return $request->url() === NovaPoshtaService::API_URL
                && $request['apiKey'] === 'test-key'
                && $request['modelName'] === 'Address'
                && $request['calledMethod'] === 'getWarehouses'
                && $request['methodProperties'] === ['CityRef' => 'city-ref-1', 'Limit' => 50];
        });
    }

    public function test_get_warehouses_includes_find_by_string_when_a_query_is_given(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => true,
                'data' => [],
            ]),
        ]);

        $this->service->getWarehouses('city-ref-1', '14');

        Http::assertSent(function ($request) {
            return $request['methodProperties'] === [
                'CityRef' => 'city-ref-1',
                'Limit' => 50,
                'FindByString' => '14',
            ];
        });
    }

    public function test_get_warehouses_returns_the_data_array_from_the_response(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        $warehouses = [
            ['Ref' => 'warehouse-ref-1', 'Number' => '14', 'Description' => 'Відділення №14'],
        ];

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => true,
                'data' => $warehouses,
            ]),
        ]);

        $this->assertSame($warehouses, $this->service->getWarehouses('city-ref-1'));
    }

    public function test_search_cities_throws_when_the_http_request_fails(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake([
            NovaPoshtaService::API_URL => Http::response(null, 500),
        ]);

        $this->expectException(DeliveryProviderUnavailableException::class);

        $this->service->searchCities('Ки');
    }

    public function test_search_cities_throws_when_nova_poshta_reports_success_false(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => false,
                'data' => [],
                'errors' => ['Invalid API key'],
            ]),
        ]);

        $this->expectException(DeliveryProviderUnavailableException::class);

        $this->service->searchCities('Ки');
    }

    public function test_get_warehouses_throws_when_the_underlying_request_throws(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake(function () {
            throw new ConnectionException('Connection timed out');
        });

        $this->expectException(ConnectionException::class);

        $this->service->getWarehouses('city-ref-1');
    }
}
