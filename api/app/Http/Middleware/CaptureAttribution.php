<?php

namespace App\Http\Middleware;

use App\Core\Growth\Domain\Services\AttributionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureAttribution
{
    protected $attributionManager;

    public function __construct(AttributionManager $attributionManager)
    {
        $this->attributionManager = $attributionManager;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Capture new data from request (UTMs, etc.)
        // This returns a Cookie object if new data was found/updated, or null if nothing changed.
        $cookie = $this->attributionManager->capture($request);

        // 2. Proceed with the request
        $response = $next($request);

        // 3. Attach the cookie to the response if we have one
        if ($cookie && $response instanceof Response) {
            $response->withCookie($cookie);
        }

        return $response;
    }
}
