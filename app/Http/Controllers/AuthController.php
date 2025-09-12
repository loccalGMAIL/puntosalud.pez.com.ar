<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Intentar autenticación con usuarios activos únicamente
        if (Auth::attempt($credentials) && Auth::user()->isActive()) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('dashboard'));
        }

        // Si el usuario existe pero no está activo
        $user = User::where('email', $credentials['email'])->first();
        if ($user && !$user->isActive()) {
            return back()->withErrors([
                'email' => 'Su cuenta está desactivada. Contacte al administrador.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Mostrar formulario de registro (solo para admin)
     */
    public function showRegister()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'No tiene permisos para acceder a esta página.');
        }
        
        return view('auth.register');
    }

    /**
     * Procesar registro de nuevo usuario
     */
    public function register(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,receptionist'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }
}