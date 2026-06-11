<?php

namespace App\Api\V1\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ResetPasswordRequest",
 *     required={"token", "email", "password", "password_confirmation"},
 *
 *     @OA\Property(property="token", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="password", type="string", format="password", minLength=8),
 *     @OA\Property(property="password_confirmation", type="string", format="password")
 * )
 */
class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
