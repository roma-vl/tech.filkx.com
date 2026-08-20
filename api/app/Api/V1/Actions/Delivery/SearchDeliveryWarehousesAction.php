<?php

namespace App\Api\V1\Actions\Delivery;

use App\Services\Delivery\NovaPoshtaService;

class SearchDeliveryWarehousesAction
{
    public function __construct(
        private readonly NovaPoshtaService $novaPoshta
    ) {}

    /**
     * @return array<int, array{ref: string, number: string, description: string}>
     */
    public function execute(string $cityRef, ?string $query = null): array
    {
        $warehouses = $this->novaPoshta->getWarehouses($cityRef, $query);

        return array_map(static fn (array $warehouse) => [
            'ref' => $warehouse['Ref'] ?? '',
            'number' => $warehouse['Number'] ?? '',
            'description' => $warehouse['Description'] ?? $warehouse['ShortAddress'] ?? '',
        ], $warehouses);
    }
}
