<?php

namespace App\Api\Admin\Requests;

use App\Api\V1\Enum\OrderStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(OrderStatusEnum::values())],
            'carrier' => 'nullable|string',
            'tracking_number' => 'nullable|string',
        ];
    }
}
