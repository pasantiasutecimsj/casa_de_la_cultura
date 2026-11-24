<?php

use App\Services\RegistroPersonasService;
use Illuminate\Support\Facades\Http;

test('codifica los segmentos de las rutas', function () {
    config(['services.registro_personas.base_url' => 'http://personas.test']);
    Http::fake(['*' => Http::response(['data' => []])]);

    app(RegistroPersonasService::class)->getCiudadByNombre('San José');

    Http::assertSent(fn ($request) => $request->method() === 'GET'
        && $request->url() === 'http://personas.test/ciudades/nombre/San%20Jos%C3%A9');
});

test('envía los datos al endpoint esperado', function () {
    config(['services.registro_personas.base_url' => 'http://personas.test']);
    Http::fake(['*' => Http::response(['id' => 10])]);

    app(RegistroPersonasService::class)->createPersona(['nombre' => 'Ana']);

    Http::assertSent(fn ($request) => $request->method() === 'POST'
        && $request->url() === 'http://personas.test/personas'
        && $request['nombre'] === 'Ana');
});
