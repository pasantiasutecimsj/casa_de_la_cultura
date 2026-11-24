<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alumno extends Model
{
    use SoftDeletes;

    protected $table = 'alumno';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'apellido',
        'edad',
        'ci',
        'telefono',
        'direccion',
        'correo',
        'fecha_ingreso',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'edad' => 'date',
            'fecha_ingreso' => 'date',
        ];
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'idAlumno');
    }
}
