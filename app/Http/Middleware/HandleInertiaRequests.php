<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name'),
                'logoUrl' => asset('images/logoLetras.png'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'auth' => function () use ($request): array {
                $proyecto = $request->attributes->get('current_project');

                return [
                    'user' => $request->user()?->only('id', 'name', 'email'),
                    'project' => $proyecto ? [
                        'id' => $proyecto->id,
                        'name' => $proyecto->nombre,
                        'key' => $proyecto->clave,
                    ] : null,
                    'roles' => $proyecto?->roles
                        ->map(fn ($rol): array => [
                            'id' => $rol->id,
                            'name' => $rol->nombre,
                            'key' => $rol->clave,
                        ])
                        ->values()
                        ->all() ?? [],
                ];
            },
        ]);
    }
}
