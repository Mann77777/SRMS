<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Check if the request is for an admin route
            if ($request->is('admin/*') || $request->is('sysadmin/*')) { // Adjust prefix if needed
                return route('sysadmin_login'); // Redirect to admin login
            }
            // Default redirect for other unauthenticated users
            return route('login'); 
        }
        return null; // Return null for JSON requests to let exception handler respond
    }
}
