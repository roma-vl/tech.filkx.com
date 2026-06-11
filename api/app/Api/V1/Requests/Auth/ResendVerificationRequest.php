<?php

namespace App\Api\V1\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ResendVerificationRequest",
 *     required={"email"},
 *
 *     @OA\Property(property="email", type="string", format="email")
 * )
 */
class ResendVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }
}
