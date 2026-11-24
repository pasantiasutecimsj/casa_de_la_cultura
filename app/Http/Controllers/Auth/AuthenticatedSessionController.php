<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\RegistroUsuarios\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $proyecto = $user instanceof User
            ? $user->proyectoConRoles((string) config('services.registro_usuarios.proyecto_clave'))
            : null;
        $esAdministrador = $proyecto?->roles->contains(
            fn ($rol): bool => Str::lower((string) $rol->clave) === 'admin'
        ) ?? false;
        $destino = $esAdministrador
            ? route('dashboard', absolute: false)
            : route('profesor.materias.index', absolute: false);

        return redirect()->intended($destino);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
