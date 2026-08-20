<?php

namespace App\Api\V1\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserPasswordRequest',
    required: ['currentPassword', 'newPassword'],
    properties: [
        new OA\Property(property: 'currentPassword', type: 'string', format: 'password'),
        new OA\Property(property: 'newPassword', type: 'string', format: 'password', minLength: 8),
    ],
)]
class UpdateUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currentPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8'],
        ];
    }
}
