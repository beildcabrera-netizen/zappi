<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $stats = [];
        if ($user->rol === 'administrador') {
            $stats = [
                'ventas_hoy' => Sale::whereDate('created_at', today())->count(),
                'ventas_monto_hoy' => Sale::whereDate('created_at', today())->where('estado_venta', 'completada')->sum('total_final'),
                'productos_bajo_stock' => Product::whereRaw('stock_unidades <= stock_minimo_alertas')->count(),
                'ventas_pendientes' => Sale::where('estado_venta', 'en_caja')->count(),
            ];
        }

        $turnoActivo = $user->turnoActivo()->with('caja')->first();

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'turnoActivo' => $turnoActivo,
        ]);
    }
}
