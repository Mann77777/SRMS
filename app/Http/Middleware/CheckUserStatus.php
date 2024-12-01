<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in (either as regular user or admin)
        if (Auth::check() || Auth::guard('admin')->check()) {
            $user = Auth::user() ?? Auth::guard('admin')->user();
            
            if ($user && ($user->status === 'inactive' || $user->status === 0)) {
                Auth::logout();
                Auth::guard('admin')->logout();
                
                return redirect()->route('login')
                    ->with('error', 'Your account has been deactivated. Please contact the administrator.');
            }
        }

        return $next($request);
    }
}
