<?php

use App\Http\Controllers\Admin\AlumnoController as AdminAlumnoController;
use App\Http\Controllers\Admin\MateriaController as AdminMateriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Profesor\MateriaController as ProfesorMateriaController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware(['auth', 'project.access'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'project.access:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('materias', AdminMateriaController::class);
        Route::put(
            'materias/{materia}/profesores',
            [AdminMateriaController::class, 'updateProfesores']
        )->name('materias.profesores.update');
        Route::resource('alumnos', AdminAlumnoController::class);
    });

Route::middleware(['auth', 'project.access:profesor,Admin'])
    ->prefix('profesor')
    ->name('profesor.')
    ->group(function () {
        Route::get('materias', [ProfesorMateriaController::class, 'index'])
            ->name('materias.index');
        Route::get('materias/{materia}', [ProfesorMateriaController::class, 'show'])
            ->name('materias.show');
        Route::post('materias/{materia}/inscripciones', [ProfesorMateriaController::class, 'store'])
            ->name('materias.inscripciones.store');
        Route::patch(
            'materias/{materia}/inscripciones/{calificacion}',
            [ProfesorMateriaController::class, 'update']
        )->name('materias.inscripciones.update');
    });

require __DIR__.'/auth.php';
