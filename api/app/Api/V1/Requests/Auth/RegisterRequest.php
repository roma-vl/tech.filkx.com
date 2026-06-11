<?php

namespace App\Api\V1\Requests\Auth;

use App\Api\V1\Rules\Recaptcha;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     required={"name", "email", "password"},
 *
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="password", type="string", format="password", minLength=8)
 * )
 */
class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'recaptcha_token' => ['sometimes', 'nullable', 'string', new Recaptcha],
        ];
    }
}
