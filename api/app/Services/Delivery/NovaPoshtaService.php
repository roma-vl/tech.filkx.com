<?php

namespace App\Services\Delivery;

use App\Api\V1\Exceptions\DeliveryProviderUnavailableException;
use Illuminate\Support\Facades\Http;

class NovaPoshtaService
{
    public const API_URL = 'https://api.novaposhta.ua/v2.0/json/';

    public function isConfigured(): bool
    {
        return filled(config('services.nova_poshta.api_key'));
    }

    /**
     * @return array<int, array<string, mixed>> Raw "Addresses" entries from Nova Poshta's
     *                                          searchSettlements response (Ref, Present, etc.)
     */
    public function searchCities(string $query): array
    {
        $result = $this->call('Address', 'searchSettlements', [
            'CityName' => $query,
            'Limit' => 20,
        ]);

        return $result[0]['Addresses'] ?? [];
    }

    /**
     * @return array<int, array<string, mixed>> Raw warehouse entries (Ref, Description, Number, etc.)
     */
    public function getWarehouses(string $cityRef, ?string $query = null): array
    {
        $properties = [
            'CityRef' => $cityRef,
            'Limit' => 50,
        ];

        if (filled($query)) {
            $properties['FindByString'] = $query;
        }

        return $this->call('Address', 'getWarehouses', $properties);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function call(string $modelName, string $calledMethod, array $methodProperties): array
    {
        if (! $this->isConfigured()) {
            throw new DeliveryProviderUnavailableException;
        }

        $response = Http::timeout(10)->post(self::API_URL, [
            'apiKey' => config('services.nova_poshta.api_key'),
            'modelName' => $modelName,
            'calledMethod' => $calledMethod,
            'methodProperties' => $methodProperties,
        ]);

        if ($response->failed()) {
            throw new DeliveryProviderUnavailableException('Не вдалося отримати відповідь від Нової Пошти.');
        }

        $body = $response->json();

        if (! ($body['success'] ?? false)) {
            throw new DeliveryProviderUnavailableException('Не вдалося отримати дані від Нової Пошти.');
        }

        return $body['data'] ?? [];
    }
}
