<?php

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Materia;
use App\Models\ProfesorMateria;
use App\Models\RegistroUsuarios\Rol;
use App\Models\RegistroUsuarios\User;
use Inertia\Testing\AssertableInertia as Assert;

function createMateria(array $attributes = []): Materia
{
    return Materia::query()->create(array_merge([
        'nombre' => 'Danza',
        'division' => 'mayores',
    ], $attributes));
}

function createAlumno(array $attributes = []): Alumno
{
    return Alumno::query()->create(array_merge([
        'nombre' => 'Ana',
        'apellido' => 'Gomez',
        'edad' => '2000-01-15',
        'ci' => '1234567',
        'telefono' => '098123456',
        'direccion' => 'Centro',
        'correo' => 'ana@example.com',
        'fecha_ingreso' => '2026-01-01',
        'notas' => 'Sin observaciones',
    ], $attributes));
}

test('el administrador gestiona materias y alumnos con eliminación lógica', function () {
    $administrador = userWithProject(['Admin']);

    $this->actingAs($administrador)
        ->post(route('admin.materias.store'), [
            'nombre' => 'Teatro',
            'division' => 'menores',
        ])
        ->assertRedirect(route('admin.materias.index'));

    $materia = Materia::query()->where('nombre', 'Teatro')->firstOrFail();

    $this->actingAs($administrador)
        ->delete(route('admin.materias.destroy', $materia))
        ->assertRedirect(route('admin.materias.index'));

    expect(Materia::withTrashed()->find($materia->id)->trashed())->toBeTrue();

    $alumno = createAlumno();

    $this->actingAs($administrador)
        ->delete(route('admin.alumnos.destroy', $alumno))
        ->assertRedirect(route('admin.alumnos.index'));

    expect(Alumno::withTrashed()->find($alumno->id)->trashed())->toBeTrue();
});

test('el profesor solo ve las materias asignadas', function () {
    $profesor = userWithProject(['profesor']);
    $asignada = createMateria();
    $noAsignada = createMateria(['nombre' => 'Teatro']);

    ProfesorMateria::query()->create([
        'usuario_id' => $profesor->id,
        'materia_id' => $asignada->id,
    ]);

    $inscripto = createAlumno();
    $disponible = createAlumno([
        'nombre' => 'Luis',
        'correo' => 'luis@example.com',
        'ci' => '7654321',
    ]);

    Calificacion::query()->create([
        'idMateria' => $asignada->id,
        'idAlumno' => $inscripto->id,
        'fecha_inscripcion' => '2026-01-01',
        'fecha_examen' => '2026-02-01',
        'nota' => 4,
    ]);

    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->actingAs($profesor)
        ->get(route('profesor.materias.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profesor/Materias/Index')
            ->has('materias', 1)
            ->where('materias.0.id', $asignada->id));

    $this->actingAs($profesor)
        ->get(route('profesor.materias.show', $asignada))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profesor/Materias/Show')
            ->has('alumnosInscritos', 1)
            ->where('alumnosInscritos.0.alumno.nombre', $inscripto->nombre)
            ->where('alumnosInscritos.0.fecha_inscripcion', '2026-01-01T00:00:00.000000Z')
            ->where('alumnosInscritos.0.fecha_examen', '2026-02-01T00:00:00.000000Z')
            ->where('alumnosInscritos.0.nota', 4)
            ->has('alumnosDisponibles', 1)
            ->where('alumnosDisponibles.0.id', $disponible->id));

    $this->actingAs($profesor)
        ->get(route('profesor.materias.show', $noAsignada))
        ->assertNotFound();
});

test('el profesor puede inscribir un alumno existente o crear uno nuevo', function () {
    $profesor = userWithProject(['profesor']);
    $materia = createMateria();
    $alumnoExistente = createAlumno();

    ProfesorMateria::query()->create([
        'usuario_id' => $profesor->id,
        'materia_id' => $materia->id,
    ]);

    $this->actingAs($profesor)
        ->post(route('profesor.materias.inscripciones.store', $materia), [
            'alumno_id' => $alumnoExistente->id,
        ])
        ->assertRedirect(route('profesor.materias.show', $materia));

    $this->actingAs($profesor)
        ->post(route('profesor.materias.inscripciones.store', $materia), [
            'nombre' => 'Luis',
            'apellido' => 'Perez',
            'edad' => '2001-02-03',
            'ci' => '7654321',
            'telefono' => '098765432',
            'direccion' => 'Norte',
            'correo' => 'luis@example.com',
            'fecha_ingreso' => '2026-01-02',
            'notas' => 'Sin observaciones',
        ])
        ->assertRedirect(route('profesor.materias.show', $materia));

    $this->assertDatabaseCount('alumno', 2);
    $this->assertDatabaseCount('calificacion', 2);

    $inscripcion = Calificacion::query()
        ->where('idMateria', $materia->id)
        ->where('idAlumno', $alumnoExistente->id)
        ->firstOrFail();

    expect($inscripcion->fecha_inscripcion->toDateString())->toBe(today()->toDateString())
        ->and($inscripcion->fecha_examen)->toBeNull()
        ->and($inscripcion->nota)->toBeNull();
});

test('el profesor solo puede cambiar la nota y la fecha de examen de una inscripción propia', function () {
    $profesor = userWithProject(['profesor']);
    $materia = createMateria();
    $alumno = createAlumno();

    ProfesorMateria::query()->create([
        'usuario_id' => $profesor->id,
        'materia_id' => $materia->id,
    ]);

    $inscripcion = Calificacion::query()->create([
        'idMateria' => $materia->id,
        'idAlumno' => $alumno->id,
        'fecha_inscripcion' => '2026-01-01',
        'fecha_examen' => null,
        'nota' => null,
    ]);

    $this->actingAs($profesor)
        ->patch(route('profesor.materias.inscripciones.update', [$materia, $inscripcion]), [
            'nota' => 4,
            'fecha_examen' => '2026-12-01',
        ])
        ->assertRedirect(route('profesor.materias.show', $materia));

    $inscripcion->refresh();

    expect($inscripcion->nota)->toBe(4)
        ->and($inscripcion->fecha_examen->toDateString())->toBe('2026-12-01');

    $this->actingAs($profesor)
        ->patch(route('profesor.materias.inscripciones.update', [$materia, $inscripcion]), [
            'idAlumno' => 999,
        ])
        ->assertSessionHasErrors('idAlumno');
});

test('el profesor no puede acceder a la administración', function () {
    $profesor = userWithProject(['profesor']);

    $this->actingAs($profesor)
        ->get(route('admin.materias.index'))
        ->assertForbidden();
});

test('el administrador asigna profesores y puede acceder a todas las materias', function () {
    $administrador = userWithProject(['Admin']);
    $proyecto = $administrador->proyectos()->firstOrFail();
    $profesor = User::factory()->create();
    $profesor->proyectos()->attach($proyecto);

    $rolProfesor = Rol::query()->create([
        'proyecto_id' => $proyecto->id,
        'nombre' => 'Profesor',
        'clave' => 'profesor',
    ]);
    $profesor->roles()->attach($rolProfesor);

    $materia = createMateria();

    $this->actingAs($administrador)
        ->put(route('admin.materias.profesores.update', $materia), [
            'usuario_ids' => [$profesor->id],
        ])
        ->assertRedirect(route('admin.materias.edit', $materia));

    $this->assertDatabaseHas('profesor_materia', [
        'usuario_id' => $profesor->id,
        'materia_id' => $materia->id,
    ]);

    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->actingAs($administrador)
        ->get(route('profesor.materias.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profesor/Materias/Index')
            ->has('materias', 1)
            ->where('materias.0.id', $materia->id));
});
