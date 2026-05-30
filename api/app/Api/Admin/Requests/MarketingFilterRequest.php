<?php

namespace App\Api\Admin\Requests;

use App\Api\V1\Enum\MarketingStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarketingFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'status' => ['nullable', 'string', Rule::in(MarketingStatusEnum::values())],
            'sortBy' => 'nullable|string',
            'sortDir' => 'nullable|string|in:asc,desc',
        ];
    }
}
