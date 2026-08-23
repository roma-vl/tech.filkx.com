<?php

namespace App\Api\V1\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateUserLocaleRequest',
    required: ['locale'],
    properties: [
        new OA\Property(property: 'locale', type: 'string', enum: ['uk', 'en', 'ua', 'ukrainian', 'english'], example: 'en'),
    ],
)]
class UpdateUserLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'in:uk,en,ua,ukrainian,english'],
        ];
    }
}
