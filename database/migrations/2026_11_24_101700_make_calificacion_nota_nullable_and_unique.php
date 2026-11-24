<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calificacion', function (Blueprint $table) {
            $table->integer('nota')->nullable()->change();
            $table->unique(
                ['idMateria', 'idAlumno'],
                'calificacion_materia_alumno_unique'
            );
        });
    }

    public function down(): void
    {
        if (DB::table('calificacion')->whereNull('nota')->exists()) {
            throw new LogicException(
                'No se puede revertir la nulabilidad de nota mientras existan alumnos sin calificar.'
            );
        }

        Schema::table('calificacion', function (Blueprint $table) {
            $table->dropUnique('calificacion_materia_alumno_unique');
            $table->integer('nota')->nullable(false)->change();
        });
    }
};
