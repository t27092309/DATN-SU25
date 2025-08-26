<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Chỉ cho phép user thường truy cập (không phải admin/staff)
        if ($user->role === 'admin' || $user->role === 'staff') {
            return response()->json(['message' => 'Forbidden - Admin/Staff cannot access user features'], 403);
        }

        return $next($request);
    }
}
