<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveContext
{
    public function handle(Request $request, Closure $next, string $module = ''): Response
    {
        if ($module) {
            session(['active_module' => $module]);
        }

        return $next($request);
    }
}
