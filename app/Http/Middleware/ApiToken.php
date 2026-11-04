<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $apiKey = config('services.jomoto_api.key');
        $demoKey = app()->isProduction() ? null : config('services.jomoto_api.demo_key');

        if (! $token || ((! $apiKey || ! hash_equals($apiKey, $token)) && (! $demoKey || ! hash_equals($demoKey, $token)))) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
