<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermisoCobro
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->puede_cobrar) {
            return redirect()->back()
                ->with('error', 'No tienes permiso para realizar esta acción en el modo actual.');
        }

        return $next($request);
    }
}
