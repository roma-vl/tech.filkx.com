<?php

namespace App\Api\V1\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SetUserPasswordRequest',
    required: ['password'],
    properties: [
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
    ],
)]
class SetUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
