<?php

namespace App\Api\Admin\Requests\Support;

use App\Api\V1\Enum\SupportStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupportTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(SupportStatusEnum::values())],
        ];
    }
}
