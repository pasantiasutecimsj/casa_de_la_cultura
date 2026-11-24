<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calificacion extends Model
{
    use SoftDeletes;

    protected $table = 'calificacion';

    public $timestamps = false;

    protected $fillable = [
        'idMateria',
        'fecha_inscripcion',
        'fecha_examen',
        'nota',
        'idAlumno',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inscripcion' => 'date',
            'fecha_examen' => 'date',
            'nota' => 'integer',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'idAlumno');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'idMateria');
    }
}
