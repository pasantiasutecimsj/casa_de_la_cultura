<?php

namespace App\Http\Requests\Auth;

use App\Models\RegistroUsuarios\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim($this->string('email')->toString())),
        ]);
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $proyectoClave = (string) config('services.registro_usuarios.proyecto_clave');

        if ($proyectoClave === '') {
            throw new LogicException('Falta configurar REGISTRO_USUARIOS_PROYECTO_CLAVE.');
        }

        $authenticated = Auth::attempt([
            'email' => $this->string('email')->toString(),
            'password' => $this->string('password')->toString(),
            'activo' => true,
        ], $this->boolean('remember'));

        $user = Auth::user();

        if (! $authenticated || ! $user instanceof User) {
            $this->failAuthentication();
        }

        if (! $user->proyectoConRoles($proyectoClave)) {
            Auth::logout();
            $this->failAuthentication();
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    private function failAuthentication(): never
    {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => 'Las credenciales no son válidas o el usuario no tiene acceso al proyecto.',
        ]);
    }
}
