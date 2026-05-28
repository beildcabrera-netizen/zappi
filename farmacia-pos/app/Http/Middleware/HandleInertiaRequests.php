<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'nombre' => $request->user()->nombre,
                    'email' => $request->user()->email,
                    'rol' => $request->user()->rol,
                    'puede_cobrar' => $request->user()->puede_cobrar,
                    'activo' => $request->user()->activo,
                ] : null,
            ],
            'turnoActivo' => fn () => $request->user()?->turnoActivo()?->with('caja')->first(),
            'configuracion' => fn () => \App\Models\Configuracion::first()?->only([
                'nombre_farmacia', 'nit_farmacia', 'direccion', 'telefono', 'ciudad',
                'moneda_simbolo', 'iva_porcentaje',
            ]),
        ]);
    }
}
