<?php

use App\Models\RegistroUsuarios\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('un usuario autenticado puede abrir la pantalla de contraseña', function () {
    $user = User::factory()->create();

    $this->withoutVite();

    $this->actingAs($user)
        ->get(route('user.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('User/Edit'));
});

test('un invitado no puede abrir la pantalla de contraseña', function () {
    $this->get(route('user.edit'))->assertRedirect(route('login'));
});

test('un usuario puede cambiar su contraseña', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('user.password.update'), [
            'current_password' => 'password',
            'password' => 'nueva-clave-segura',
            'password_confirmation' => 'nueva-clave-segura',
        ])
        ->assertSessionHasNoErrors();

    expect(Hash::check('nueva-clave-segura', $user->refresh()->password))->toBeTrue();
});

test('la contraseña actual debe ser correcta', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('user.edit'))
        ->put(route('user.password.update'), [
            'current_password' => 'incorrecta',
            'password' => 'nueva-clave-segura',
            'password_confirmation' => 'nueva-clave-segura',
        ])
        ->assertRedirect(route('user.edit'))
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
});

test('la nueva contraseña debe estar confirmada', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('user.edit'))
        ->put(route('user.password.update'), [
            'current_password' => 'password',
            'password' => 'nueva-clave-segura',
            'password_confirmation' => 'otra-clave-segura',
        ])
        ->assertRedirect(route('user.edit'))
        ->assertSessionHasErrors('password');
});
