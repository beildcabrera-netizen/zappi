<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;

class ProveedorController extends Controller
{
    public function index(): JsonResponse
    {
        $proveedores = Proveedor::withCount('compras')->orderBy('nombre')->get();

        return response()->json(['success' => true, 'data' => $proveedores]);
    }

    public function store(ProveedorRequest $request): JsonResponse
    {
        $proveedor = Proveedor::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Proveedor creado exitosamente.',
            'data' => $proveedor,
        ], 201);
    }

    public function show(Proveedor $proveedor): JsonResponse
    {
        $proveedor->load('compras');

        return response()->json(['success' => true, 'data' => $proveedor]);
    }

    public function update(ProveedorRequest $request, Proveedor $proveedor): JsonResponse
    {
        $proveedor->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Proveedor actualizado exitosamente.',
            'data' => $proveedor,
        ]);
    }

    public function destroy(Proveedor $proveedor): JsonResponse
    {
        if ($proveedor->compras()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el proveedor porque tiene compras asociadas.',
            ], 409);
        }

        $proveedor->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Proveedor desactivado exitosamente.',
        ]);
    }

    public function buscar(string $query): JsonResponse
    {
        $proveedores = Proveedor::where('activo', true)
            ->where(function ($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                    ->orWhere('nit', 'like', "%{$query}%")
                    ->orWhere('contacto', 'like', "%{$query}%");
            })
            ->orderBy('nombre')
            ->limit(20)
            ->get();

        return response()->json(['success' => true, 'data' => $proveedores]);
    }
}
