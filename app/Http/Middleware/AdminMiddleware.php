<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in as admin
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }
        
        if ($request->ajax()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return redirect('login')->with('error', 'You must be an admin to access this page');
    }
}