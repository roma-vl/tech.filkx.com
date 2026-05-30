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
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'required|email|max:255',
            'shipping_country' => 'nullable|string|max:100',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_address' => 'required|string|max:500',
            'delivery_method' => 'required|string|max:100',
            'payment_method' => 'required|string|max:100',
            'session_id' => 'nullable|string',
            'coupon_code' => 'nullable|string',
        ];
    }
}
