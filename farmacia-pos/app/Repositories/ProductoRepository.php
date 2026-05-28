<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductoRepositoryInterface;
use App\Models\Product;

class ProductoRepository extends BaseRepository implements ProductoRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Product);
    }

    public function findByCodigo(string $codigo): ?Product
    {
        return Product::where('codigo_barras', $codigo)
            ->orWhere('codigo_interno', $codigo)
            ->first();
    }

    public function search(string $query): iterable
    {
        return Product::where('activo', true)
            ->where(function ($q) use ($query) {
                $q->where('codigo_barras', 'like', "%{$query}%")
                  ->orWhere('codigo_interno', 'like', "%{$query}%")
                  ->orWhere('nombre_comercial', 'like', "%{$query}%")
                  ->orWhere('nombre_generico', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();
    }

    public function stockBajo(): iterable
    {
        return Product::where('activo', true)
            ->whereColumn('stock_unidades', '<=', 'stock_minimo_alertas')
            ->get();
    }
}
