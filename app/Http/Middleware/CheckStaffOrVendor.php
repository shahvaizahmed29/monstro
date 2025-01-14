<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckStaffOrVendor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->hasRole(\App\Models\User::STAFF) || $request->user()->hasRole(\App\Models\User::VENDOR)) {
            return $next($request);
        } 
        return response()->json(['success' => false, 'status' => 401, 'message' => 'Unauthorized role']);
    }
}