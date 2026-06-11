<?php

namespace App\Api\V1\Dto;

class PlaceOrderDto
{
    public function __construct(
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly string $customerEmail,
        public readonly ?string $shippingCountry,
        public readonly ?string $shippingCity,
        public readonly string $shippingAddress,
        public readonly string $deliveryMethod,
        public readonly string $paymentMethod,
        public readonly ?string $sessionId,
        public readonly ?string $couponCode
    ) {}

    public static function fromRequest($request): self
    {
        return new self(
            customerName: $request->input('customerName'),
            customerPhone: $request->input('customerPhone'),
            customerEmail: $request->input('customerEmail'),
            shippingCountry: $request->input('shippingCountry'),
            shippingCity: $request->input('shippingCity'),
            shippingAddress: $request->input('shippingAddress'),
            deliveryMethod: $request->input('deliveryMethod'),
            paymentMethod: $request->input('paymentMethod'),
            sessionId: $request->header('X-Cart-Session-ID') ?: $request->input('sessionId'),
            couponCode: $request->input('couponCode')
        );
    }

    public function toArray(): array
    {
        return [
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'customer_email' => $this->customerEmail,
            'shipping_country' => $this->shippingCountry,
            'shipping_city' => $this->shippingCity,
            'shipping_address' => $this->shippingAddress,
            'delivery_method' => $this->deliveryMethod,
            'payment_method' => $this->paymentMethod,
            'coupon_code' => $this->couponCode,
        ];
    }
}
