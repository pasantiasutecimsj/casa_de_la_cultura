<?php

namespace App\Models\RegistroUsuarios;

use App\Models\Concerns\UsesRegistroUsuariosConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    use UsesRegistroUsuariosConnection;

    protected $table = 'roles';

    protected $fillable = [
        'proyecto_id',
        'nombre',
        'clave',
        'descripcion',
        'por_defecto',
    ];

    protected function casts(): array
    {
        return ['por_defecto' => 'boolean'];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'rol_usuario',
            'rol_id',
            'usuario_id'
        )->withTimestamps();
    }
}
