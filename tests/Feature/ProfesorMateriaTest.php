<?php

use App\Models\Materia;
use App\Models\ProfesorMateria;
use Illuminate\Database\QueryException;

it('permite asignar varias materias al mismo usuario', function () {
    $materias = collect(['Danza', 'Teatro'])->map(
        fn (string $nombre) => Materia::query()->create([
            'nombre' => $nombre,
            'division' => 'mayores',
        ])
    );

    foreach ($materias as $materia) {
        ProfesorMateria::query()->create([
            'usuario_id' => 42,
            'materia_id' => $materia->id,
        ]);
    }

    expect(ProfesorMateria::query()
        ->where('usuario_id', 42)
        ->orderBy('materia_id')
        ->with('materia')
        ->get()
        ->pluck('materia.nombre')
        ->all())->toBe(['Danza', 'Teatro']);
});

it('evita asignar dos veces la misma materia al mismo usuario', function () {
    $materia = Materia::query()->create([
        'nombre' => 'Danza',
        'division' => 'mayores',
    ]);

    ProfesorMateria::query()->create([
        'usuario_id' => 42,
        'materia_id' => $materia->id,
    ]);

    expect(fn () => ProfesorMateria::query()->create([
        'usuario_id' => 42,
        'materia_id' => $materia->id,
    ]))->toThrow(QueryException::class);
});
