<?php

namespace App\Http\Middleware;

use App\Models\RegistroUsuarios\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectAccess
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->estaActivo()) {
            return $this->closeSession($request);
        }

        $proyectoClave = (string) config('services.registro_usuarios.proyecto_clave');
        abort_if($proyectoClave === '', 500, 'Falta configurar REGISTRO_USUARIOS_PROYECTO_CLAVE.');

        $proyecto = $user->proyectoConRoles($proyectoClave);

        if (! $proyecto) {
            return $this->closeSession($request);
        }

        if ($roles !== []) {
            $rolesUsuario = $proyecto->roles
                ->pluck('clave')
                ->filter()
                ->map(fn ($rol): string => Str::lower((string) $rol));
            $rolesRequeridos = collect($roles)->map(fn (string $rol): string => Str::lower($rol));

            abort_if($rolesUsuario->intersect($rolesRequeridos)->isEmpty(), 403, 'No tiene permisos para esta acción.');
        }

        $request->attributes->set('current_project', $proyecto);

        return $next($request);
    }

    private function closeSession(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => 'El usuario no tiene acceso a este proyecto.',
        ]);
    }
}
