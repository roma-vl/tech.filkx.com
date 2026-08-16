<?php

namespace App\Api\V1\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ConfirmTwoFactorAuthenticationRequest",
 *     title="Confirm 2FA Request",
 *     required={"code"},
 *
 *     @OA\Property(property="code", type="string", example="123456")
 * )
 */
class ConfirmTwoFactorAuthenticationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:6'],
        ];
    }
}
