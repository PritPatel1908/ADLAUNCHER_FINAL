<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class DeviceApiRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $deviceId = $request->input('device_id');

        if (!$deviceId) {
            return $next($request);
        }

        // Rate limiting per device
        $cacheKey = "rate_limit_device_{$deviceId}";
        $requests = Cache::get($cacheKey, 0);

        // Allow 100 requests per minute per device
        $maxRequests = 100;
        $windowMinutes = 1;

        if ($requests >= $maxRequests) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rate limit exceeded. Too many requests from this device.',
                'retry_after' => 60 // seconds
            ], 429);
        }

        // Increment request count
        Cache::put($cacheKey, $requests + 1, now()->addMinutes($windowMinutes));

        return $next($request);
    }
}
