<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureOAuthMetadata
{
    /**
     * Capture user agent and IP for OAuth token creation
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Store user agent and IP in request attributes for later use
        if ($request->is('oauth/token')) {
            $request->attributes->set('oauth_user_agent', $request->userAgent());
            $request->attributes->set('oauth_ip_address', $request->ip());
        }

        return $next($request);
    }
}
