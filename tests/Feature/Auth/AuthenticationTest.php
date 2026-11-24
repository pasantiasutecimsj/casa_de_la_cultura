<?php

use App\Models\RegistroUsuarios\User;

test('la pantalla de acceso está disponible para invitados', function () {
    $this->get(route('login'))->assertOk();
});

test('la portada está disponible para invitados', function () {
    $this->get(route('home'))->assertOk();
});

test('un usuario activo asignado al proyecto puede ingresar', function () {
    $user = userWithProject(roleKeys: ['profesor']);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('profesor.materias.index', absolute: false));
});

test('un administrador conserva el acceso inicial al dashboard', function () {
    $user = userWithProject(roleKeys: ['Admin']);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
});

test('las credenciales incorrectas no crean una sesión', function () {
    $user = userWithProject();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'incorrecta',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('un usuario sin el proyecto configurado no puede ingresar', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

test('un usuario inactivo no puede ingresar', function () {
    $user = userWithProject(attributes: ['activo' => false]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('un usuario puede cerrar su sesión', function () {
    $user = userWithProject();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect('/');
});
