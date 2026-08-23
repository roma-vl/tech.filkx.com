<?php

namespace App\Api\V1\Actions\Delivery;

use App\Services\Delivery\NovaPoshtaService;

class SearchDeliveryCitiesAction
{
    public function __construct(
        private readonly NovaPoshtaService $novaPoshta
    ) {}

    /**
     * @return array<int, array{ref: string, name: string, area: string}>
     */
    public function execute(string $query): array
    {
        $cities = $this->novaPoshta->searchCities($query);

        return array_map(static fn (array $city) => [
            // "DeliveryCity" (not "Ref") is the settlement's *city* ref that getWarehouses expects -
            // searchSettlements' "Ref" is the settlement record's own ref, a different identifier.
            'ref' => $city['DeliveryCity'] ?? $city['Ref'] ?? '',
            'name' => $city['Present'] ?? $city['MainDescription'] ?? '',
            'area' => $city['Area'] ?? $city['RegionsDescription'] ?? '',
        ], $cities);
    }
}
