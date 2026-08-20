<?php

namespace App\Api\V1\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateNotificationPreferencesRequest',
    required: ['newsletter', 'productUpdates', 'marketingEmails'],
    properties: [
        new OA\Property(property: 'newsletter', type: 'boolean'),
        new OA\Property(property: 'productUpdates', type: 'boolean'),
        new OA\Property(property: 'marketingEmails', type: 'boolean'),
    ],
)]
class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'newsletter' => ['required', 'boolean'],
            'productUpdates' => ['required', 'boolean'],
            'marketingEmails' => ['required', 'boolean'],
        ];
    }
}
