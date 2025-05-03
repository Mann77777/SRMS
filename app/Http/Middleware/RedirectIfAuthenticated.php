<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$guards
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Redirect based on the guard
                if ($guard === 'admin') {
                    // Redirect admins to their dashboard
                    return redirect(RouteServiceProvider::ADMIN_HOME); 
                }
                // Default redirect for 'web' guard or others
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
