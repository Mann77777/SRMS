<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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
        // Check if user is authenticated as admin
        if (Auth::guard('admin')->check()) {
            // Check if the authenticated admin has the role 'Admin'
            if (Auth::guard('admin')->user()->role === 'Admin') {
                return $next($request);
            }
        }
        
        // If not authenticated as admin or doesn't have proper role, redirect to admin login
        return redirect()->route('sysadmin_login')->with('error', 'You do not have admin access.');
    }
}