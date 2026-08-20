<?php

namespace App\Api\V1\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DisableTwoFactorAuthenticationRequest",
 *     title="Disable 2FA Request",
 *     required={"password"},
 *
 *     @OA\Property(property="password", type="string", format="password")
 * )
 */
class DisableTwoFactorAuthenticationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }
}
