<?php

namespace App\Services\Caja;

use App\Exceptions\SinTurnoActivoException;
use App\Models\CashShift;
use App\Models\User;

class TurnoService
{
    public function detectarModoActual(): string
    {
        $now = now();
        $diaSemana = $now->dayOfWeek;
        $hora = $now->format('H:i');

        $config = \App\Models\ShiftConfig::where('activo', true)
            ->whereJsonContains('dias_semana', $diaSemana)
            ->where('hora_inicio', '<=', $hora)
            ->where('hora_fin', '>=', $hora)
            ->orderBy('prioridad', 'desc')
            ->first();

        return $config?->modo_operacion ?? 'vendedor_cobra';
    }

    public function obtenerActivoOError(User $user): CashShift
    {
        $turno = $user->turnoActivo;

        if (!$turno) {
            throw new SinTurnoActivoException();
        }

        return $turno;
    }

    public function puedeCobrar(User $user): bool
    {
        $modo = $this->detectarModoActual();

        return match ($modo) {
            'vendedor_cobra' => true,
            'cajero_cobra' => $user->hasRole('cajero') || $user->hasRole('administrador'),
            'mixto' => true,
            default => true,
        };
    }
}
