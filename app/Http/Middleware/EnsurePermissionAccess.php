<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermissionAccess
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()?->canDo($permission)) {
            abort(403, 'No tiene acceso a esta funcionalidad.');
        }

        return $next($request);
    }
}
