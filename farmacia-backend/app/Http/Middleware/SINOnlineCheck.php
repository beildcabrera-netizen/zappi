<?php

namespace App\Http\Middleware;

use App\Models\ConfiguracionSin;
use App\Services\SINService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SINOnlineCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        $sinService = app(SINService::class);
        $estado = $sinService->verificarEstado();

        if (!$estado['online']) {
            return response()->json([
                'success' => false,
                'message' => 'El sistema SIN no está disponible. Verifique la configuración CUIS/CUFD.',
                'estado_sin' => $estado,
            ], 503);
        }

        return $next($request);
    }
}
