<?php

namespace App\Api\V1\Requests\Auth;

use App\Api\V1\Rules\Recaptcha;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     required={"email", "password"},
 *
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="password", type="string", format="password")
 * )
 */
class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'recaptcha_token' => ['sometimes', 'nullable', 'string', new Recaptcha],
        ];
    }
}
