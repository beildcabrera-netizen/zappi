<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SINConfigRequest;
use App\Models\ConfiguracionSin;
use App\Services\SINService;
use Illuminate\Http\JsonResponse;

class SINController extends Controller
{
    public function __construct(
        private SINService $sinService
    ) {}

    public function configuracion(): JsonResponse
    {
        $config = ConfiguracionSin::where('activo', true)->first();

        if (!$config) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No hay configuración SIN registrada.',
            ]);
        }

        return response()->json(['success' => true, 'data' => $config]);
    }

    public function guardarConfiguracion(SINConfigRequest $request): JsonResponse
    {

        $config = ConfiguracionSin::where('activo', true)->first();

        if ($config) {
            $config->update($request->validated());
        } else {
            $config = ConfiguracionSin::create($request->validated());
        }

        return response()->json([
            'success' => true,
            'message' => 'Configuración SIN guardada exitosamente.',
            'data' => $config->fresh(),
        ]);
    }

    public function solicitarCUIS(): JsonResponse
    {
        try {
            $resultado = $this->sinService->solicitarCUIS();

            return response()->json([
                'success' => $resultado,
                'message' => $resultado
                    ? 'CUIS solicitado exitosamente.'
                    : 'Error al solicitar CUIS.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function solicitarCUFD(): JsonResponse
    {
        try {
            $resultado = $this->sinService->solicitarCUFD();

            return response()->json([
                'success' => $resultado,
                'message' => $resultado
                    ? 'CUFD solicitado exitosamente.'
                    : 'Error al solicitar CUFD.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function estado(): JsonResponse
    {
        $estado = $this->sinService->verificarEstado();

        return response()->json([
            'success' => true,
            'data' => $estado,
        ]);
    }
}
