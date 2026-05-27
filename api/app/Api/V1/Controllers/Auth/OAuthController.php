<?php

namespace App\Api\V1\Controllers\Auth;

use App\Api\V1\Controllers\BaseApiController;
use App\Services\Auth\OAuth\OAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OAuthController extends BaseApiController
{
    public function __construct(
        private readonly OAuthService $service
    ) {}

    public function redirectToProvider(string $provider): JsonResponse
    {
        try {
            $redirectUrl = $this->service->getRedirectUrl($provider);

            return self::successfulResponseWithData(['url' => $redirectUrl]);
        } catch (Exception $e) {
            return self::errorResponse($e->getMessage(), 500);
        }
    }

    public function callback(string $provider, Request $request): RedirectResponse
    {
        try {
            $result = $this->service->handleCallback($provider, $request);

            $token = $result['token']['access_token'];

            // Pick the best frontend URL from the allowed list based on request origin or default to first
            $origins = explode(',', config('app.frontend_url', 'http://localhost:3000'));
            $origin = $request->header('Origin') ?: $request->header('Referer');
            $frontendUrl = $origins[0];

            if ($origin) {
                foreach ($origins as $o) {
                    if (str_contains($origin, parse_url($o, PHP_URL_HOST))) {
                        $frontendUrl = $o;
                        break;
                    }
                }
            }

            $accountRestored = isset($result['account_restored']) && $result['account_restored'];

            $redirectUrl = "{$frontendUrl}/auth/callback/success?token=".urlencode($token);
            if ($accountRestored) {
                $redirectUrl .= '&restored=true';
            }

            return redirect($redirectUrl);

        } catch (Exception $e) {
            // Pick the best frontend URL for error redirect too
            $origins = explode(',', config('app.frontend_url', 'http://localhost:3000'));
            $origin = $request->header('Origin') ?: $request->header('Referer');
            $frontendUrl = $origins[0];

            if ($origin) {
                foreach ($origins as $o) {
                    if (str_contains($origin, parse_url($o, PHP_URL_HOST))) {
                        $frontendUrl = $o;
                        break;
                    }
                }
            }

            $errorMessage = urlencode($e->getMessage());

            return redirect("{$frontendUrl}/auth/callback/error?error=".$errorMessage);
        }
    }

    public function connect(string $provider): JsonResponse
    {
        try {
            $this->service->connect(auth()->user(), $provider);

            return self::successfulResponseWithData(['message' => 'Account connected successfully']);
        } catch (Exception $e) {
            return self::errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    public function disconnect(string $provider): JsonResponse
    {
        try {
            $this->service->disconnect(auth()->user(), $provider);

            return self::successfulResponseWithData(['message' => 'Account disconnected successfully']);
        } catch (Exception $e) {
            return self::errorResponse($e->getMessage());
        }
    }
}
