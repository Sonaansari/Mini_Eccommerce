<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware {
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Access denied: Admins only',
                'data' => new \stdClass()
            ], 403);
        }
        return $next($request);
    }
}
