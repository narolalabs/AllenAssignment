<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class RateLimitPerUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = 'api_limit:' . $ip;
        $count = Cache::get($key, 0);

        if ($count >= 10) {
            return response()->json([
                'message' => 'Daily API limit reached. Try again tomorrow.'
            ], 429);
        }

        Cache::put($key, $count + 1, now()->addDay());
        return $next($request);
    }
}
