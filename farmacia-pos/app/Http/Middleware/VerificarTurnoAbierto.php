<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarTurnoAbierto
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->turnoActivo) {
            $except = ['turno.apertura', 'turnos.abrir', 'login', 'logout'];

            if (!in_array($request->route()?->getName(), $except)) {
                return redirect()->route('turno.apertura')
                    ->with('error', 'Debe abrir un turno antes de continuar.');
            }
        }

        return $next($request);
    }
}
