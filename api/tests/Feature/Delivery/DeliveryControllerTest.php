<?php

namespace Tests\Feature\Delivery;

use App\Services\Delivery\NovaPoshtaService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeliveryControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Match the "no key configured" state assumed by tests unless a test opts in.
        config(['services.nova_poshta.api_key' => null]);
    }

    public function test_availability_reports_unavailable_when_key_is_not_configured(): void
    {
        $response = $this->getJson('/api/v1/delivery/availability');

        $response->assertOk()
            ->assertJsonPath('data.available', false);
    }

    public function test_availability_reports_available_when_key_is_configured(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        $response = $this->getJson('/api/v1/delivery/availability');

        $response->assertOk()
            ->assertJsonPath('data.available', true);
    }

    public function test_cities_returns_service_unavailable_when_key_is_not_configured(): void
    {
        $response = $this->getJson('/api/v1/delivery/cities?query=Ки');

        $response->assertStatus(503)
            ->assertJsonPath('status', 'error');
    }

    public function test_cities_rejects_a_too_short_query(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        $response = $this->getJson('/api/v1/delivery/cities?query=К');

        $response->assertStatus(422);
    }

    public function test_cities_returns_mapped_results_when_key_is_configured(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => true,
                'data' => [
                    [
                        'Addresses' => [
                            [
                                'Ref' => 'settlement-ref-1',
                                'DeliveryCity' => 'city-ref-1',
                                'Present' => 'м. Київ, Київська обл.',
                                'MainDescription' => 'Київ',
                                'Area' => 'Київська',
                            ],
                        ],
                    ],
                ],
                'errors' => [],
            ]),
        ]);

        $response = $this->getJson('/api/v1/delivery/cities?query=Ки');

        $response->assertOk()
            ->assertJsonPath('data.0.ref', 'city-ref-1')
            ->assertJsonPath('data.0.name', 'м. Київ, Київська обл.')
            ->assertJsonPath('data.0.area', 'Київська');

        Http::assertSent(function ($request) {
            return $request->url() === NovaPoshtaService::API_URL
                && $request['apiKey'] === 'test-key'
                && $request['modelName'] === 'Address'
                && $request['calledMethod'] === 'searchSettlements'
                && $request['methodProperties']['CityName'] === 'Ки';
        });
    }

    public function test_cities_returns_service_unavailable_when_nova_poshta_reports_an_error(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => false,
                'data' => [],
                'errors' => ['Invalid API key'],
            ]),
        ]);

        $response = $this->getJson('/api/v1/delivery/cities?query=Ки');

        $response->assertStatus(503);
    }

    public function test_warehouses_returns_service_unavailable_when_key_is_not_configured(): void
    {
        $response = $this->getJson('/api/v1/delivery/warehouses?cityRef=city-ref-1');

        $response->assertStatus(503);
    }

    public function test_warehouses_requires_a_city_ref(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        $response = $this->getJson('/api/v1/delivery/warehouses');

        $response->assertStatus(422);
    }

    public function test_warehouses_returns_mapped_results_when_key_is_configured(): void
    {
        config(['services.nova_poshta.api_key' => 'test-key']);

        Http::fake([
            NovaPoshtaService::API_URL => Http::response([
                'success' => true,
                'data' => [
                    [
                        'Ref' => 'warehouse-ref-1',
                        'Number' => '14',
                        'Description' => 'Відділення №14: вул. Хрещатик, 1',
                    ],
                ],
                'errors' => [],
            ]),
        ]);

        $response = $this->getJson('/api/v1/delivery/warehouses?cityRef=city-ref-1&query=14');

        $response->assertOk()
            ->assertJsonPath('data.0.ref', 'warehouse-ref-1')
            ->assertJsonPath('data.0.number', '14')
            ->assertJsonPath('data.0.description', 'Відділення №14: вул. Хрещатик, 1');

        Http::assertSent(function ($request) {
            return $request['methodProperties']['CityRef'] === 'city-ref-1'
                && $request['methodProperties']['FindByString'] === '14'
                && $request['calledMethod'] === 'getWarehouses';
        });
    }
}
