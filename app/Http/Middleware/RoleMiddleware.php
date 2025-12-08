<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Load role relationship if not already loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }
        
        // Check if user has a role
        if (!$user->role || !$user->role_id) {
            abort(403, 'You do not have a role assigned. Please contact your administrator.');
        }

        // Check if user's role slug is in the allowed roles
        if (empty($roles) || !in_array($user->role->slug, $roles)) {
            abort(403, 'You do not have permission to access this resource. Required role: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}

