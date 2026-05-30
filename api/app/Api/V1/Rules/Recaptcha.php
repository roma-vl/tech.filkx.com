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
            \Log::error('reCAPTCHA failed', [
                'success' => $response->json('success'),
                'error-codes' => $response->json('error-codes'),
                'secret_preview' => substr(config('services.recaptcha.secret'), 0, 10) . '...',
            ]);
            $fail('The reCAPTCHA verification failed. Please try again.');
        }

        // Optional: Check score if needed (v3 returns a score)
        // if ($response->json('score') < 0.5) {
        //     $fail('Suspicious activity detected.');
        // }
    }
}
