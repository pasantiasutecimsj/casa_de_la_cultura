<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfesorMateria extends Model
{
    use SoftDeletes;

    protected $table = 'profesor_materia';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'materia_id',
    ];

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }
}
