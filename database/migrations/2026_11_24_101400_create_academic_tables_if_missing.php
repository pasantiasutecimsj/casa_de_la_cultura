<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('materia')) {
            Schema::create('materia', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre', 50);
                $table->string('division');
            });
        }

        if (! Schema::hasTable('alumno')) {
            Schema::create('alumno', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre', 20);
                $table->string('apellido', 20);
                $table->date('edad');
                $table->string('ci', 8);
                $table->string('telefono', 9);
                $table->string('direccion', 20);
                $table->string('correo', 30);
                $table->date('fecha_ingreso');
                $table->string('notas', 100);
            });
        }

        if (! Schema::hasTable('calificacion')) {
            Schema::create('calificacion', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('idMateria');
                $table->date('fecha_inscripcion');
                $table->date('fecha_examen')->nullable();
                $table->integer('nota');
                $table->integer('idAlumno');
            });
        }
    }

    public function down(): void
    {
        // These legacy tables predate the application's migration history and must be preserved.
    }
};
