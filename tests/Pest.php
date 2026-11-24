<?php

use App\Models\RegistroUsuarios\Proyecto;
use App\Models\RegistroUsuarios\Rol;
use App\Models\RegistroUsuarios\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function userWithProject(array $roleKeys = ['usuario'], array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $project = Proyecto::query()->create([
        'nombre' => 'Plantilla',
        'clave' => config('services.registro_usuarios.proyecto_clave'),
        'activo' => true,
    ]);

    $user->proyectos()->attach($project);

    foreach ($roleKeys as $key) {
        $role = Rol::query()->create([
            'proyecto_id' => $project->id,
            'nombre' => ucfirst($key),
            'clave' => $key,
        ]);
        $user->roles()->attach($role);
    }

    return $user;
}
