<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesor_materia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->integer('materia_id');
            $table->index('materia_id');
            $table->unique(['usuario_id', 'materia_id']);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesor_materia');
    }
};
