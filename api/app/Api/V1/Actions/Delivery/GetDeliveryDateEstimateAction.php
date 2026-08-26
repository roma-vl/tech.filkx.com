<?php

namespace App\Api\V1\Actions\Delivery;

use App\Api\V1\Exceptions\DeliveryProviderUnavailableException;
use App\Services\Delivery\NovaPoshtaService;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class GetDeliveryDateEstimateAction
{
    public function __construct(
        private readonly NovaPoshtaService $novaPoshta
    ) {}

    /**
     * @return array{available: bool, date: string|null} `date` is a Y-m-d string when available.
     *                                                     Never throws - callers (and the shopper)
     *                                                     should treat `available: false` the same
     *                                                     as any other "no estimate" outcome.
     */
    public function execute(string $cityRecipientRef): array
    {
        if (! $this->novaPoshta->canEstimateDeliveryDate()) {
            return ['available' => false, 'date' => null];
        }

        try {
            $estimate = $this->novaPoshta->getDeliveryDateEstimate($cityRecipientRef);
        } catch (DeliveryProviderUnavailableException) {
            return ['available' => false, 'date' => null];
        }

        $rawDate = $estimate['DeliveryDate']['date'] ?? null;

        if (! is_string($rawDate) || $rawDate === '') {
            return ['available' => false, 'date' => null];
        }

        try {
            return ['available' => true, 'date' => Carbon::parse($rawDate)->format('Y-m-d')];
        } catch (InvalidFormatException) {
            return ['available' => false, 'date' => null];
        }
    }
}
