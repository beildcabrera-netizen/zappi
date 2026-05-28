<?php

namespace App\Services\Caja;

use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\User;

class CajaService
{
    public function abrirTurno(User $user, int $cajaId, float $montoInicial, ?string $tipoTurno = 'apertura'): CashShift
    {
        $caja = CashRegister::findOrFail($cajaId);

        if (!$caja->activa) {
            throw new \InvalidArgumentException('La caja seleccionada no está activa.');
        }

        return CashShift::create([
            'cash_register_id' => $cajaId,
            'user_id' => $user->id,
            'tipo_turno' => $tipoTurno ?? 'apertura',
            'fecha_apertura' => now(),
            'monto_inicial' => $montoInicial,
            'estado' => 'abierta',
        ]);
    }

    public function cerrarTurno(CashShift $turno, float $montoDeclarado, ?string $observaciones = null): CashShift
    {
        $turno->loadMissing([
            'ventasPropias' => fn($q) => $q->where('estado_venta', 'completada'),
            'ventasCobradas' => fn($q) => $q->where('estado_venta', 'completada'),
        ]);

        $totalVentasPropias = $turno->ventasPropias->sum('total_final');
        $totalVentasOtros = $turno->ventasCobradas->sum('total_final');
        $montoCalculado = $turno->monto_inicial + $totalVentasPropias + $totalVentasOtros;
        $diferencia = $montoDeclarado - $montoCalculado;

        $turno->update([
            'fecha_cierre' => now(),
            'monto_final_declarado' => $montoDeclarado,
            'monto_final_calculado' => $montoCalculado,
            'diferencia' => $diferencia,
            'observaciones_cierre' => $observaciones,
            'total_ventas_propias' => $totalVentasPropias,
            'total_ventas_otros' => $totalVentasOtros,
            'ventas_propias_count' => $turno->ventasPropias->count(),
            'ventas_cobradas_otros_count' => $turno->ventasCobradas->count(),
            'estado' => 'cerrada',
            'cerrado_por' => $turno->user_id,
        ]);

        return $turno->fresh();
    }
}
