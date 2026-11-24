<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materia extends Model
{
    use SoftDeletes;

    protected $table = 'materia';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'division',
    ];

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'idMateria');
    }

    public function asignacionesProfesores(): HasMany
    {
        return $this->hasMany(ProfesorMateria::class, 'materia_id');
    }
}
