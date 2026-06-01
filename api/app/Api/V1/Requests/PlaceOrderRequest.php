<?php

namespace App\Api\V1\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customerName'    => 'required|string|max:255',
            'customerPhone'   => 'required|string|max:50',
            'customerEmail'   => 'required|email|max:255',
            'shippingCountry' => 'nullable|string|max:100',
            'shippingCity'    => 'nullable|string|max:100',
            'shippingAddress' => 'required|string|max:500',
            'deliveryMethod'  => 'required|string|max:100',
            'paymentMethod'   => 'required|string|max:100',
            'sessionId'       => 'nullable|string',
            'couponCode'      => 'nullable|string',
        ];
    }
}
