<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class IdentifyImpersonation
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        try {
            if ($user && method_exists($user, 'token')) {
                $token = $user->token();

                if ($token) {
                    $dbToken = DB::table('oauth_access_tokens')
                        ->where('id', $token->id)
                        ->first();

                    if ($dbToken && $dbToken->impersonator_id) {
                        Log::info("Impersonation detected for user {$user->id} by admin {$dbToken->impersonator_id}");
                        $request->merge(['impersonator_id' => $dbToken->impersonator_id]);
                        $request->merge(['is_impersonating' => true]);
                    }
                }
            }
        } catch (Throwable $e) {
            Log::error('IdentifyImpersonation middleware error: '.$e->getMessage());
        }

        return $next($request);
    }
}
