<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\AuditLogService;
use App\Models\AuditLog;

class LogActivity
{
    protected $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log login attempts
        if ($request->routeIs('login.post')) {
            if (auth()->check()) {
                $this->auditLogService->logAction(
                    'login',
                    auth()->user(),
                    "User logged in from IP: {$request->ip()}"
                );
            } else {
                // Log failed login attempt
                AuditLog::create([
                    'model_type' => \App\Models\User::class,
                    'model_id' => 0,
                    'action' => 'login_failed',
                    'description' => "Failed login attempt with email: {$request->input('email')} from IP: {$request->ip()}",
                    'user_id' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        }

        // Log logout
        if ($request->routeIs('logout')) {
            if (auth()->check()) {
                $this->auditLogService->logAction(
                    'logout',
                    auth()->user(),
                    "User logged out"
                );
            }
        }

        return $response;
    }
}

