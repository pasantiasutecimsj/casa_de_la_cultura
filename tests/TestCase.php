<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        config([
            'app.url' => 'http://localhost',
            'auth.user_connection' => null,
            'services.registro_usuarios.proyecto_clave' => 'plantilla',
        ]);
        URL::forceRootUrl('http://localhost');

        $this->createRegistroUsuariosSchema();
    }

    private function createRegistroUsuariosSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('clave');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id');
            $table->string('nombre');
            $table->string('clave');
            $table->text('descripcion')->nullable();
            $table->boolean('por_defecto')->default(false);
            $table->timestamps();
        });

        Schema::create('proyecto_usuario', function (Blueprint $table) {
            $table->foreignId('proyecto_id');
            $table->foreignId('usuario_id');
            $table->timestamps();
        });

        Schema::create('rol_usuario', function (Blueprint $table) {
            $table->foreignId('rol_id');
            $table->foreignId('usuario_id');
            $table->timestamps();
        });
    }
}
