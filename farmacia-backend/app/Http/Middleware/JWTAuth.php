<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth as JWTAuthFacade;

class JWTAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuthFacade::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token no válido o expirado.',
            ], 401);
        }

        if (!$user || !$user->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado o inactivo.',
            ], 401);
        }

        return $next($request);
    }
}
