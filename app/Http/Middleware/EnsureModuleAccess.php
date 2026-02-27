<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! $request->user()?->canAccessModule($module)) {
            abort(403, 'No tiene acceso a este módulo.');
        }

        return $next($request);
    }
}
