<?php

namespace App\Models\RegistroUsuarios;

use App\Models\Concerns\UsesRegistroUsuariosConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    use UsesRegistroUsuariosConnection;

    protected $table = 'proyectos';

    protected $fillable = [
        'nombre',
        'clave',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'proyecto_usuario',
            'proyecto_id',
            'usuario_id'
        )->withTimestamps();
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Rol::class, 'proyecto_id');
    }
}
