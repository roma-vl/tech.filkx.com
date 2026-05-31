<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ConvertCamelCaseToSnakeCase
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $request->replace($this->snakeCaseKeys($input));
        return $next($request);
    }

    /**
     * Recursively convert array keys to snake_case.
     */
    private function snakeCaseKeys(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $snake = [];
        foreach ($data as $key => $value) {
            $newKey = Str::snake($key);
            $snake[$newKey] = $this->snakeCaseKeys($value);
        }
        return $snake;
    }
}
