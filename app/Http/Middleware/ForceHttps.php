<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only force HTTPS in production
        if (app()->environment('production')) {
            // Check if request is secure (handles proxy headers)
            $isSecure = $request->secure() || 
                       $request->header('X-Forwarded-Proto') === 'https' ||
                       $request->server('HTTP_X_FORWARDED_PROTO') === 'https';
            
            if (!$isSecure) {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }
}
