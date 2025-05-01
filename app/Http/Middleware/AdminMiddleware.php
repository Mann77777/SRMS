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
        // Check if user is authenticated using the 'admin' guard
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            // Allow access if the user has either 'Admin' or 'UITC Staff' role
            if ($user->role === 'Admin' || $user->role === 'UITC Staff') {
                return $next($request);
            }
        }
        
        // If not authenticated via 'admin' guard or doesn't have the required role, redirect to admin login
        return redirect()->route('sysadmin_login')->with('error', 'You do not have admin access.');
    }
}
