<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, lk-id, X-Requested-With, csrf-token, Accept')
                ->header('Access-Control-Max-Age', '86400'); // 24 hours
        }

        try {
            $response = $next($request);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('API Error in CORS middleware', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method()
            ]);
            
            // Create error response with CORS headers - ensure it's JSON
            $response = response()->json([
                'success' => false,
                'message' => 'Server error occurred',
                'error' => $e->getMessage()
            ], 500);
        }

        // Ensure response is JSON for API routes and block HTML redirects
        if ($request->is('api/*')) {
            // Convert redirects on API routes to JSON (likely guest redirect)
            if ($response instanceof \Symfony\Component\HttpFoundation\RedirectResponse) {
                $targetUrl = method_exists($response, 'getTargetUrl') ? $response->getTargetUrl() : null;
                $response = response()->json([
                    'success' => false,
                    'message' => 'Unauthorized or invalid access for API route',
                    'redirect' => $targetUrl
                ], 401);
            }

            // If response is not JSON, convert content to JSON envelope
            if (!$response instanceof \Illuminate\Http\JsonResponse) {
                $response = response()->json([
                    'success' => false,
                    'message' => 'Invalid response format',
                    'data' => method_exists($response, 'getContent') ? $response->getContent() : null
                ], 500);
            }
        }

        // Add CORS headers to all responses (including errors)
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, lk-id, X-Requested-With, csrf-token, Accept')
            ->header('Access-Control-Allow-Credentials', 'false');
    }
} 