<?php

namespace App\Services\Auditoria;

use App\Models\AuditLog;

class AuditoriaService
{
    public function registrar(
        string $accion,
        string $tabla,
        ?int $registroId = null,
        ?array $valoresViejos = null,
        ?array $valoresNuevos = null,
        ?int $userId = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'accion' => $accion,
            'tabla' => $tabla,
            'registro_id' => $registroId,
            'valores_viejos' => $valoresViejos ? json_encode($valoresViejos) : null,
            'valores_nuevos' => $valoresNuevos ? json_encode($valoresNuevos) : null,
        ]);
    }

    public function listar(array $filtros = [], int $perPage = 50): iterable
    {
        $query = AuditLog::with('user');

        if (!empty($filtros['accion'])) {
            $query->where('accion', $filtros['accion']);
        }
        if (!empty($filtros['tabla'])) {
            $query->where('tabla', $filtros['tabla']);
        }
        if (!empty($filtros['user_id'])) {
            $query->where('user_id', $filtros['user_id']);
        }
        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_desde']);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_hasta']);
        }

        return $query->orderByDesc('id')->paginate($perPage);
    }
}
