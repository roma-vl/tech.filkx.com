<?php

namespace App\Api\V1\Actions\Delivery;

use App\Services\Delivery\NovaPoshtaService;

class GetDeliveryAvailabilityAction
{
    public function __construct(
        private readonly NovaPoshtaService $novaPoshta
    ) {}

    public function execute(): array
    {
        return ['available' => $this->novaPoshta->isConfigured()];
    }
}
