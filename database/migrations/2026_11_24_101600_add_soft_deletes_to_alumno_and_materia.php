<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('materia', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('calificacion', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('materia', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('calificacion', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('alumno', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
