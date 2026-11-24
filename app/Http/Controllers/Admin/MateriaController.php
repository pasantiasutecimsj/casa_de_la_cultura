<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\ProfesorMateria;
use App\Models\RegistroUsuarios\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MateriaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Materias/Index', [
            'materias' => Materia::query()->orderBy('nombre')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Materias/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        Materia::query()->create($this->validatedData($request));

        return redirect()->route('admin.materias.index')
            ->with('success', 'Materia creada correctamente.');
    }

    public function show(Materia $materia): Response
    {
        $profesorIds = ProfesorMateria::query()
            ->where('materia_id', $materia->getKey())
            ->pluck('usuario_id');

        return Inertia::render('Admin/Materias/Show', [
            'materia' => $materia,
            'profesoresAsignados' => User::query()
                ->whereIn('id', $profesorIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function edit(Request $request, Materia $materia): Response
    {
        return Inertia::render('Admin/Materias/Edit', [
            'materia' => $materia,
            'profesoresDisponibles' => $this->profesoresDelProyecto($request),
            'profesoresAsignados' => ProfesorMateria::query()
                ->where('materia_id', $materia->getKey())
                ->pluck('usuario_id')
                ->map(fn ($id): int => (int) $id)
                ->values(),
        ]);
    }

    public function update(Request $request, Materia $materia): RedirectResponse
    {
        $materia->update($this->validatedData($request));

        return redirect()->route('admin.materias.index')
            ->with('success', 'Materia actualizada correctamente.');
    }

    public function destroy(Materia $materia): RedirectResponse
    {
        DB::transaction(function () use ($materia): void {
            $materia->calificaciones()->delete();
            $materia->asignacionesProfesores()->delete();
            $materia->delete();
        });

        return redirect()->route('admin.materias.index')
            ->with('success', 'Materia eliminada correctamente.');
    }

    public function updateProfesores(Request $request, Materia $materia): RedirectResponse
    {
        $data = $request->validate([
            'usuario_ids' => ['present', 'array'],
            'usuario_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $usuarioIds = collect($data['usuario_ids'])
            ->map(fn ($id): int => (int) $id)
            ->values();

        $idsDeProfesoresActivos = $this->profesoresDelProyecto($request)
            ->whereIn('id', $usuarioIds)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id);

        if ($idsDeProfesoresActivos->count() !== $usuarioIds->count()) {
            throw ValidationException::withMessages([
                'usuario_ids' => 'Solo se pueden asignar profesores activos de este proyecto.',
            ]);
        }

        $asignaciones = ProfesorMateria::withTrashed()
            ->where('materia_id', $materia->getKey())
            ->get()
            ->keyBy(fn (ProfesorMateria $asignacion): int => (int) $asignacion->usuario_id);

        $asignados = $asignaciones
            ->filter(fn (ProfesorMateria $asignacion): bool => ! $asignacion->trashed())
            ->keys()
            ->map(fn ($id): int => (int) $id);

        $paraEliminar = $asignados->diff($usuarioIds);
        $paraAgregar = $usuarioIds->diff($asignados);

        DB::transaction(function () use ($materia, $paraEliminar, $paraAgregar): void {
            if ($paraEliminar->isNotEmpty()) {
                ProfesorMateria::query()
                    ->where('materia_id', $materia->getKey())
                    ->whereIn('usuario_id', $paraEliminar)
                    ->delete();
            }

            foreach ($paraAgregar as $usuarioId) {
                $asignacion = ProfesorMateria::withTrashed()
                    ->where('materia_id', $materia->getKey())
                    ->where('usuario_id', $usuarioId)
                    ->first();

                if ($asignacion?->trashed()) {
                    $asignacion->restore();
                } elseif (! $asignacion) {
                    ProfesorMateria::query()->create([
                        'usuario_id' => $usuarioId,
                        'materia_id' => $materia->getKey(),
                    ]);
                }
            }
        });

        return redirect()->route('admin.materias.edit', $materia)
            ->with('success', 'Profesores asignados correctamente.');
    }

    private function validatedData(Request $request): array
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:50'],
            'division' => ['required', 'string', 'in:mayores,menores'],
        ];

        if ($request->isMethod('patch')) {
            foreach ($rules as &$fieldRules) {
                array_unshift($fieldRules, 'sometimes');
            }
        }

        return $request->validate($rules);
    }

    private function profesoresDelProyecto(Request $request): Builder
    {
        $proyecto = $request->attributes->get('current_project');

        return User::query()
            ->where('activo', true)
            ->whereHas('roles', fn (Builder $query) => $query
                ->where('roles.clave', 'profesor')
                ->where('roles.proyecto_id', $proyecto->getKey()))
            ->orderBy('name');
    }
}
