<?php

namespace App\Repositories;

use App\Contracts\Repositories\VentaRepositoryInterface;
use App\Models\Sale;

class VentaRepository extends BaseRepository implements VentaRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Sale);
    }

    public function pendientes(): iterable
    {
        return Sale::pendientes()->with('items.product')->orderByDesc('id')->get();
    }

    public function enCaja(): iterable
    {
        return Sale::enCaja()->with('items.product')->orderByDesc('id')->get();
    }

    public function completadas(): iterable
    {
        return Sale::completadas()->with('items.product')->orderByDesc('id')->get();
    }

    public function reporteVentas(array $filters = []): iterable
    {
        $query = Sale::completadas()->with('items.product', 'vendedor');

        if (!empty($filters['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $filters['fecha_desde']);
        }
        if (!empty($filters['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $filters['fecha_hasta']);
        }
        if (!empty($filters['vendedor_id'])) {
            $query->where('vendedor_id', $filters['vendedor_id']);
        }
        if (!empty($filters['metodo_pago'])) {
            $query->where('metodo_pago', $filters['metodo_pago']);
        }

        return $query->orderByDesc('id')->paginate(50);
    }
}
