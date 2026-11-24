<?php

namespace App\Models\RegistroUsuarios;

use App\Models\Concerns\UsesRegistroUsuariosConnection;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, UsesRegistroUsuariosConnection;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function proyectos(): BelongsToMany
    {
        return $this->belongsToMany(
            Proyecto::class,
            'proyecto_usuario',
            'usuario_id',
            'proyecto_id'
        )->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'rol_usuario',
            'usuario_id',
            'rol_id'
        )->withTimestamps();
    }

    public function proyectoConRoles(string $clave): ?Proyecto
    {
        $proyecto = $this->proyectos()
            ->where('proyectos.clave', $clave)
            ->where('proyectos.activo', true)
            ->first();

        if (! $proyecto) {
            return null;
        }

        $roles = $this->roles()
            ->where('roles.proyecto_id', $proyecto->id)
            ->orderBy('roles.id')
            ->get();

        return $proyecto->setRelation('roles', $roles);
    }

    public function estaActivo(): bool
    {
        return (bool) $this->activo;
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
