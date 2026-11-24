<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AlumnoController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Alumnos/Index', [
            'alumnos' => Alumno::query()
                ->orderBy('apellido')
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Alumnos/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        Alumno::query()->create($this->validatedData($request));

        return redirect()->route('admin.alumnos.index')
            ->with('success', 'Alumno creado correctamente.');
    }

    public function show(Alumno $alumno): Response
    {
        return Inertia::render('Admin/Alumnos/Show', [
            'alumno' => $alumno->load('calificaciones.materia'),
        ]);
    }

    public function escolaridad(Alumno $alumno)
    {
        $alumno->load('calificaciones.materia');

        return Pdf::loadView('pdf.escolaridad', [
            'alumno' => $alumno,
        ])
            ->setPaper('a4')
            ->download('escolaridad-'.$alumno->ci.'.pdf');
    }

    public function edit(Alumno $alumno): Response
    {
        return Inertia::render('Admin/Alumnos/Edit', [
            'alumno' => $alumno,
        ]);
    }

    public function update(Request $request, Alumno $alumno): RedirectResponse
    {
        $alumno->update($this->validatedData($request));

        return redirect()->route('admin.alumnos.index')
            ->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(Alumno $alumno): RedirectResponse
    {
        DB::transaction(function () use ($alumno): void {
            $alumno->calificaciones()->delete();
            $alumno->delete();
        });

        return redirect()->route('admin.alumnos.index')
            ->with('success', 'Alumno eliminado correctamente.');
    }

    private function validatedData(Request $request): array
    {
        $rules = [
            'nombre' => ['required', 'string', 'max:20'],
            'apellido' => ['required', 'string', 'max:20'],
            'edad' => ['required', 'date'],
            'ci' => ['required', 'string', 'max:8'],
            'telefono' => ['required', 'string', 'max:9'],
            'direccion' => ['required', 'string', 'max:20'],
            'correo' => ['required', 'email', 'max:30'],
            'fecha_ingreso' => ['required', 'date'],
            'notas' => ['required', 'string', 'max:100'],
        ];

        if ($request->isMethod('patch')) {
            foreach ($rules as &$fieldRules) {
                array_unshift($fieldRules, 'sometimes');
            }
        }

        return $request->validate($rules);
    }
}
