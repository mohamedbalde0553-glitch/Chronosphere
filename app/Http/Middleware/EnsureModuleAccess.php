<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    private array $modulePermissions = [
        'timetable' => 'timetable.view',
        'shifts'    => 'shifts.view',
        'calendar'  => 'calendar.view',
        'booking'   => 'booking.view',
        'project'   => 'project.view',
    ];

    public function handle(Request $request, Closure $next, string $module = ''): Response
    {
        if ($module && isset($this->modulePermissions[$module])) {
            $permission = $this->modulePermissions[$module];

            if (!$request->user()?->can($permission)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Accès refusé à ce module.'], 403);
                }
                abort(403, 'Accès refusé à ce module.');
            }
        }

        return $next($request);
    }
}
