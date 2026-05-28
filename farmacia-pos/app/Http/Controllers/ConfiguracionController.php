<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ConfiguracionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:administrador');
    }

    public function edit()
    {
        $config = Configuracion::first();

        return Inertia::render('Configuracion/Edit', [
            'configuracion' => $config,
        ]);
    }

    public function update(ConfiguracionUpdateRequest $request): RedirectResponse
    {
        $config = Configuracion::first();

        if (!$config) {
            Configuracion::create($request->validated());
        } else {
            $config->update($request->validated());
        }

        return Redirect::route('configuracion.edit')->with('success', 'Configuración actualizada correctamente.');
    }
}
