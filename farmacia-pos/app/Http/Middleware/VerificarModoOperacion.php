<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarModoOperacion
{
    protected array $modosPermitidos = [];

    public function handle(Request $request, Closure $next, string ...$modos): Response
    {
        $this->modosPermitidos = $modos;

        if (empty($this->modosPermitidos)) {
            return $next($request);
        }

        $modoActual = app(\App\Services\Caja\TurnoService::class)->detectarModoActual();

        if (!in_array($modoActual, $this->modosPermitidos)) {
            return redirect()->back()
                ->with('error', "Esta acción no está disponible en el modo de operación actual ({$modoActual}).");
        }

        return $next($request);
    }
}
