<?php

namespace App\Api\V1\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $value,
        ]);

        if (!$response->json('success')) {
            $fail('The reCAPTCHA verification failed. Please try again.');
        }

        // Optional: Check score if needed (v3 returns a score)
        // if ($response->json('score') < 0.5) {
        //     $fail('Suspicious activity detected.');
        // }
    }
}
