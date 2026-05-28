<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\CashShift;
use App\Services\Caja\CajaService;
use App\Services\Caja\TurnoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class TurnoController extends Controller
{
    public function __construct(
        protected TurnoService $turnoService,
        protected CajaService $cajaService,
    ) {}

    public function apertura()
    {
        $user = auth()->user();

        if ($user->turnoActivo) {
            return Redirect::route('caja.venta')->with('info', 'Ya tienes un turno activo.');
        }

        $cajas = CashRegister::where('activa', true)->get();

        $cajaPreferida = $user->cajaPreferida_id
            ? CashRegister::find($user->cajaPreferida_id)
            : null;

        $modo = $this->turnoService->detectarModoActual();

        return Inertia::render('Caja/TurnoApertura', [
            'cajas' => $cajas,
            'cajaPreferida' => $cajaPreferida,
            'modo' => $modo,
        ]);
    }

    public function abrir(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->turnoActivo) {
            return Redirect::back()->with('error', 'Ya tienes un turno activo.');
        }

        $validated = $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'monto_inicial' => 'required|numeric|min:0',
            'tipo_turno' => 'nullable|string|in:apertura,reingreso',
        ]);

        $this->cajaService->abrirTurno(
            $user,
            $validated['cash_register_id'],
            $validated['monto_inicial'],
            $validated['tipo_turno'] ?? 'apertura',
        );

        return Redirect::route('caja.venta')->with('success', 'Turno abierto correctamente.');
    }

    public function cerrar(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $turno = $user->turnoActivo;

        if (!$turno) {
            return Redirect::back()->with('error', 'No tienes un turno activo.');
        }

        $validated = $request->validate([
            'monto_final_declarado' => 'required|numeric|min:0',
            'observaciones_cierre' => 'nullable|string|max:500',
        ]);

        $this->cajaService->cerrarTurno(
            $turno,
            $validated['monto_final_declarado'],
            $validated['observaciones_cierre'] ?? null,
        );

        return Redirect::route('dashboard')->with('success', 'Turno cerrado correctamente.');
    }
}
