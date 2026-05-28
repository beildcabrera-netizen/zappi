<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Talonario;
use App\Services\Caja\TurnoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CajaController extends Controller
{
    public function __construct(
        protected TurnoService $turnoService
    ) {}

    public function puntoVenta()
    {
        $user = auth()->user();

        $productos = Product::where('activo', true)
            ->where('stock_unidades', '>', 0)
            ->select(
                'id', 'codigo_barras', 'codigo_interno', 'nombre_comercial',
                'nombre_generico', 'presentacion_entrada', 'precio_venta_unidad',
                'precio_venta_blister', 'precio_venta_caja', 'stock_unidades',
                'unidades_por_blister', 'blisters_por_caja',
                'fraccionamiento_habilitado', 'controlado', 'refrigerado',
                'laboratorio', 'seccion', 'estante'
            )
            ->orderBy('nombre_comercial')
            ->get();

        $cajas = CashRegister::where('activa', true)->get();

        $talonarios = Talonario::where('estado', 'activado')
            ->where('fecha_limite_emision', '>=', now()->toDateString())
            ->whereColumn('siguiente_numero', '<=', 'rango_fin')
            ->select('id', 'numero_autorizacion', 'siguiente_numero', 'rango_fin')
            ->orderBy('siguiente_numero')
            ->get();

        $modo = $this->turnoService->detectarModoActual();

        $turnoActivo = $user->turnoActivo()->with('caja')->first();

        return Inertia::render('Caja/PuntoVenta', [
            'productos' => $productos,
            'cajas' => $cajas,
            'talonarios' => $talonarios,
            'modo' => $modo,
            'turnoActivo' => $turnoActivo,
        ]);
    }
}
