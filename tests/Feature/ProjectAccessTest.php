<?php

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('el dashboard comparte solo el proyecto y los roles actuales', function () {
    $user = userWithProject(['lector', 'editor']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.id', $user->id)
            ->where('auth.project.key', 'plantilla')
            ->has('auth.roles', 2)
            ->where('auth.roles.0.key', 'lector')
            ->where('auth.roles.1.key', 'editor'));
});

test('el middleware permite cualquiera de los roles requeridos', function () {
    Route::middleware(['web', 'auth', 'project.access:admin,editor'])
        ->get('/area-restringida', fn () => 'permitido');

    $user = userWithProject(['editor']);

    $this->actingAs($user)->get('/area-restringida')->assertOk();
});

test('el middleware rechaza usuarios sin un rol requerido', function () {
    Route::middleware(['web', 'auth', 'project.access:admin'])
        ->get('/area-administrativa', fn () => 'permitido');

    $user = userWithProject(['lector']);

    $this->actingAs($user)->get('/area-administrativa')->assertForbidden();
});

test('los invitados son enviados al acceso', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
