<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Materia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MateriaController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Profesor/Materias/Index', [
            'materias' => $this->materiasDelProfesor($request)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function show(Request $request, Materia $materia): Response
    {
        $materia = $this->materiaAccesible($request, $materia->getKey());

        return Inertia::render('Profesor/Materias/Show', [
            'materia' => $materia,
            'alumnosInscritos' => Calificacion::query()
                ->where('idMateria', $materia->getKey())
                ->whereHas('alumno')
                ->with('alumno:id,nombre,apellido')
                ->orderBy('fecha_inscripcion')
                ->get(),
            'alumnosDisponibles' => Alumno::query()
                ->whereDoesntHave(
                    'calificaciones',
                    fn (Builder $query) => $query->where('idMateria', $materia->getKey())
                )
                ->orderBy('apellido')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'apellido', 'ci', 'edad']),
        ]);
    }

    public function store(Request $request, Materia $materia): RedirectResponse
    {
        $materia = $this->materiaAccesible($request, $materia->getKey());

        if ($request->filled('alumno_id')) {
            $data = $request->validate([
                'alumno_id' => [
                    'required',
                    'integer',
                    Rule::exists('alumno', 'id')->whereNull('deleted_at'),
                ],
                'fecha_examen' => ['nullable', 'date'],
                ...$this->prohibitedStudentFields(),
            ]);
        } else {
            $data = $request->validate([
                'alumno_id' => ['prohibited'],
                'fecha_examen' => ['nullable', 'date'],
                'nombre' => ['required', 'string', 'max:20'],
                'apellido' => ['required', 'string', 'max:20'],
                'edad' => ['required', 'date'],
                'ci' => ['required', 'string', 'max:8'],
                'telefono' => ['required', 'string', 'max:9'],
                'direccion' => ['required', 'string', 'max:20'],
                'correo' => ['required', 'email', 'max:30'],
                'notas' => ['nullable', 'string', 'max:100'],
            ]);
        }

        DB::transaction(function () use ($data, $materia): void {
            if (isset($data['alumno_id'])) {
                $alumno = Alumno::query()->findOrFail($data['alumno_id']);
            } else {
                $alumno = Alumno::query()->create([
                    ...Arr::only($data, [
                    'nombre',
                    'apellido',
                    'edad',
                    'ci',
                    'telefono',
                    'direccion',
                    'correo',
                    ]),
                    'fecha_ingreso' => today()->toDateString(),
                    'notas' => $data['notas'] ?? '',
                ]);
            }

            $yaInscrito = Calificacion::query()
                ->where('idMateria', $materia->getKey())
                ->where('idAlumno', $alumno->getKey())
                ->exists();

            if ($yaInscrito) {
                throw ValidationException::withMessages([
                    'alumno_id' => 'El alumno ya está inscripto en esta materia.',
                ]);
            }

            Calificacion::query()->create([
                'idMateria' => $materia->getKey(),
                'fecha_inscripcion' => today()->toDateString(),
                'fecha_examen' => $data['fecha_examen'] ?? null,
                'nota' => null,
                'idAlumno' => $alumno->getKey(),
            ]);
        });

        return redirect()->route('profesor.materias.show', $materia)
            ->with('success', 'Alumno inscripto correctamente.');
    }

    public function update(
        Request $request,
        Materia $materia,
        Calificacion $calificacion
    ): RedirectResponse {
        $materia = $this->materiaAccesible($request, $materia->getKey());

        abort_unless((int) $calificacion->idMateria === (int) $materia->getKey(), 404);

        $data = $request->validate([
            'nota' => ['sometimes', 'nullable', 'integer'],
            'fecha_examen' => ['sometimes', 'nullable', 'date'],
            'fecha_inscripcion' => ['prohibited'],
            'idAlumno' => ['prohibited'],
            'idMateria' => ['prohibited'],
            'alumno_id' => ['prohibited'],
            'materia_id' => ['prohibited'],
            'nombre' => ['prohibited'],
            'apellido' => ['prohibited'],
            'edad' => ['prohibited'],
            'ci' => ['prohibited'],
            'telefono' => ['prohibited'],
            'direccion' => ['prohibited'],
            'correo' => ['prohibited'],
            'fecha_ingreso' => ['prohibited'],
            'notas' => ['prohibited'],
        ]);

        if (! $request->hasAny(['nota', 'fecha_examen'])) {
            throw ValidationException::withMessages([
                'nota' => 'Indique una nota o una fecha de examen para actualizar.',
            ]);
        }

        $calificacion->update(Arr::only($data, ['nota', 'fecha_examen']));

        return redirect()->route('profesor.materias.show', $materia)
            ->with('success', 'Calificación actualizada correctamente.');
    }

    private function materiaAccesible(Request $request, int|string $materiaId): Materia
    {
        return $this->materiasDelProfesor($request)
            ->whereKey($materiaId)
            ->firstOrFail();
    }

    private function materiasDelProfesor(Request $request): Builder
    {
        if ($this->esAdministrador($request)) {
            return Materia::query();
        }

        $usuarioId = $request->user()->getAuthIdentifier();

        return Materia::query()->whereHas(
            'asignacionesProfesores',
            fn (Builder $query) => $query->where('usuario_id', $usuarioId)
        );
    }

    private function esAdministrador(Request $request): bool
    {
        $proyecto = $request->attributes->get('current_project');

        return $proyecto?->roles->contains(
            fn ($rol): bool => Str::lower((string) $rol->clave) === 'admin'
        ) ?? false;
    }

    private function prohibitedStudentFields(): array
    {
        return [
            'nombre' => ['prohibited'],
            'apellido' => ['prohibited'],
            'edad' => ['prohibited'],
            'ci' => ['prohibited'],
            'telefono' => ['prohibited'],
            'direccion' => ['prohibited'],
            'correo' => ['prohibited'],
            'fecha_ingreso' => ['prohibited'],
            'notas' => ['prohibited'],
        ];
    }
}
