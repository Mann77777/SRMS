<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // If not authenticated at all
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('sysadmin_login');
        }

        // Check if role is allowed
        // Ensure the role string matches exactly what's expected from the route definition and the database
        if ($role === 'UITC Staff' && Auth::guard('admin')->user()->role === 'UITC Staff') { // Changed 'UITC_Staff' to 'UITC Staff'
            return $next($request);
        }
        
        if ($role === 'Admin' && Auth::guard('admin')->user()->role === 'Admin') {
            return $next($request);
        }
        
        // If user doesn't have the required role
        return redirect()->back()->with('error', 'You do not have permission to access this page.');
    }
}
