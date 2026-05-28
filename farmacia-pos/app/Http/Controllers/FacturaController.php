<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Talonario;
use App\Models\ManualInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class FacturaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:administrador')->only('storeTalonario');
    }

    public function talonarios()
    {
        $talonarios = Talonario::withCount('facturas')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($talonario) {
                $usadas = $talonario->facturas_count;
                $disponibles = max(0, $talonario->rango_fin - $talonario->siguiente_numero + 1);
                $totalRango = $talonario->rango_fin - $talonario->rango_inicio + 1;

                return array_merge($talonario->toArray(), [
                    'usadas' => $usadas,
                    'disponibles' => $disponibles,
                    'total_rango' => $totalRango,
                    'porcentaje_usado' => $totalRango > 0 ? round(($usadas / $totalRango) * 100, 1) : 0,
                ]);
            });

        return Inertia::render('Facturas/Talonarios', [
            'talonarios' => $talonarios,
        ]);
    }

    public function storeTalonario(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero_autorizacion' => 'required|string|max:50|unique:talonarios',
            'numero_tramite' => 'required|string|max:50',
            'sucursal' => 'required|integer|min:0',
            'actividad_economica' => 'required|string|max:255',
            'fecha_autorizacion' => 'required|date',
            'fecha_limite_emision' => 'required|date|after:fecha_autorizacion',
            'rango_inicio' => 'required|integer|min:1',
            'rango_fin' => 'required|integer|gte:rango_inicio',
            'cantidad_solicitada' => 'nullable|integer|min:1',
            'pin_entrega' => 'nullable|string|max:20',
        ]);

        $validated['siguiente_numero'] = $validated['rango_inicio'];
        $validated['estado'] = 'pendiente';

        Talonario::create($validated);

        return Redirect::route('facturas.talonarios')->with('success', 'Talonario registrado correctamente.');
    }

    public function registroVentas(Request $request)
    {
        $validated = $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        $query = ManualInvoice::with(['sale.items.product', 'talonario', 'vendedor', 'caja']);

        if ($fechaDesde = $validated['fecha_desde'] ?? null) {
            $query->whereDate('fecha_emision', '>=', $fechaDesde);
        }

        if ($fechaHasta = $validated['fecha_hasta'] ?? null) {
            $query->whereDate('fecha_emision', '<=', $fechaHasta);
        }

        $facturas = $query->orderBy('fecha_emision', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Facturas/RegistroVentas', [
            'facturas' => $facturas,
            'filters' => $request->only(['fecha_desde', 'fecha_hasta']),
        ]);
    }
}
