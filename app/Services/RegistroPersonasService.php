<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LogicException;

class RegistroPersonasService
{
    public function validarCedula(string|int $ci): Response
    {
        return $this->get('utils', 'validarCedula', $ci);
    }

    public function getPersonas(): Response
    {
        return $this->get('personas');
    }

    public function getPersona(string|int $ci): Response
    {
        return $this->get('personas', $ci);
    }

    public function createPersona(array $persona): Response
    {
        return $this->post($persona, 'personas');
    }

    public function updatePersona(string|int $id, array $persona): Response
    {
        return $this->put($persona, 'personas', $id);
    }

    public function patchPersona(string|int $id, array $persona): Response
    {
        return $this->patch($persona, 'personas', $id);
    }

    public function deletePersona(string|int $id): Response
    {
        return $this->delete('personas', $id);
    }

    public function updateOrCreatePersona(array $persona): Response
    {
        return $this->post($persona, 'personas', 'updateOrCreate');
    }

    public function getOrganizaciones(): Response
    {
        return $this->get('organizaciones');
    }

    public function getOrganizacion(string|int $id): Response
    {
        return $this->get('organizaciones', $id);
    }

    public function createOrganizacion(array $organizacion): Response
    {
        return $this->post($organizacion, 'organizaciones');
    }

    public function updateOrganizacion(string|int $id, array $organizacion): Response
    {
        return $this->put($organizacion, 'organizaciones', $id);
    }

    public function patchOrganizacion(string|int $id, array $organizacion): Response
    {
        return $this->patch($organizacion, 'organizaciones', $id);
    }

    public function deleteOrganizacion(string|int $id): Response
    {
        return $this->delete('organizaciones', $id);
    }

    public function getCiudades(): Response
    {
        return $this->get('ciudades');
    }

    public function getCiudad(string|int $id): Response
    {
        return $this->get('ciudades', $id);
    }

    public function getCiudadByNombre(string $nombre): Response
    {
        return $this->get('ciudades', 'nombre', $nombre);
    }

    public function createCiudad(array $ciudad): Response
    {
        return $this->post($ciudad, 'ciudades');
    }

    public function updateCiudad(string|int $id, array $ciudad): Response
    {
        return $this->put($ciudad, 'ciudades', $id);
    }

    public function patchCiudad(string|int $id, array $ciudad): Response
    {
        return $this->patch($ciudad, 'ciudades', $id);
    }

    public function deleteCiudad(string|int $id): Response
    {
        return $this->delete('ciudades', $id);
    }

    public function getDirecciones(): Response
    {
        return $this->get('direcciones');
    }

    public function getDireccionesFullData(): Response
    {
        return $this->get('direcciones', 'full');
    }

    public function getDireccion(string|int $id): Response
    {
        return $this->get('direcciones', $id);
    }

    public function getDireccionesByPersona(string|int $ci): Response
    {
        return $this->get('direcciones', 'persona', $ci);
    }

    public function getDireccionesByOrganizacion(string|int $id): Response
    {
        return $this->get('direcciones', 'organizacion', $id);
    }

    public function buscarDireccion(array $filtros): Response
    {
        return $this->post($filtros, 'direcciones', 'buscar');
    }

    public function createDireccionConPersona(array $data): Response
    {
        return $this->post($data, 'direcciones', 'crear', 'persona');
    }

    public function createDireccionConOrganizacion(array $data): Response
    {
        return $this->post($data, 'direcciones', 'crear', 'organizacion');
    }

    public function updateDireccion(string|int $id, array $direccion): Response
    {
        return $this->put($direccion, 'direcciones', $id);
    }

    public function patchDireccion(string|int $id, array $direccion): Response
    {
        return $this->patch($direccion, 'direcciones', $id);
    }

    public function updatePersonaDireccion(string|int $id, array $data): Response
    {
        return $this->put($data, 'direcciones', 'persona-direccion', $id);
    }

    public function patchPersonaDireccion(string|int $id, array $data): Response
    {
        return $this->patch($data, 'direcciones', 'persona-direccion', $id);
    }

    public function deleteDireccion(string|int $id): Response
    {
        return $this->delete('direcciones', $id);
    }

    public function deletePersonaDireccion(string|int $id): Response
    {
        return $this->delete('direcciones', 'persona-direccion', $id);
    }

    private function client(): PendingRequest
    {
        $baseUrl = rtrim((string) config('services.registro_personas.base_url'), '/');

        if ($baseUrl === '') {
            throw new LogicException('Falta configurar REGISTRO_PERSONAS_URL.');
        }

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->timeout((int) config('services.registro_personas.timeout', 10));
    }

    private function get(string|int ...$segments): Response
    {
        return $this->client()->get($this->path(...$segments));
    }

    private function post(array $data, string|int ...$segments): Response
    {
        return $this->client()->post($this->path(...$segments), $data);
    }

    private function put(array $data, string|int ...$segments): Response
    {
        return $this->client()->put($this->path(...$segments), $data);
    }

    private function patch(array $data, string|int ...$segments): Response
    {
        return $this->client()->patch($this->path(...$segments), $data);
    }

    private function delete(string|int ...$segments): Response
    {
        return $this->client()->delete($this->path(...$segments));
    }

    private function path(string|int ...$segments): string
    {
        return '/'.implode('/', array_map(
            fn (string|int $segment): string => rawurlencode((string) $segment),
            $segments
        ));
    }
}
