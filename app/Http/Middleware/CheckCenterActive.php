<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCenterActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Excluir rutas de autenticación (GET y POST /login no tienen el mismo nombre)
        if ($request->is('login') || $request->routeIs('logout')) {
            return $next($request);
        }

        if (setting('center_active', '1') === '1') {
            return $next($request);
        }

        // Centro bloqueado — solo pasan usuarios con módulo 'system'
        if (Auth::check() && Auth::user()->canAccessModule('system')) {
            return $next($request);
        }

        // Usuario autenticado sin módulo system → desloguear
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')->withErrors([
            'center_blocked' => 'El sistema se encuentra temporalmente bloqueado. Contacte al Administrador.',
        ]);
    }
}
