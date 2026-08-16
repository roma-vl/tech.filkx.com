<?php

namespace App\Api\V1\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="VerifyTwoFactorRequest",
 *     required={"challenge_token", "code"},
 *
 *     @OA\Property(property="challenge_token", type="string"),
 *     @OA\Property(property="code", type="string", example="123456")
 * )
 */
class VerifyTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'challenge_token' => ['required', 'string'],
            'code' => ['required', 'string'],
        ];
    }
}
