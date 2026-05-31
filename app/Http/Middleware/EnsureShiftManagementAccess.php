<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShiftManagementAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->can('shifts.manage_employees') || $user->can('shifts.manage_department'))) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        abort(403, 'Accès refusé.');
    }
}
