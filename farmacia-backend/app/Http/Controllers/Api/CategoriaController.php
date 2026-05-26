<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function index(): JsonResponse
    {
        $categorias = Categoria::withCount('productos')
            ->orderBy('nombre')
            ->get();

        return response()->json(['success' => true, 'data' => $categorias]);
    }

    public function store(CategoriaRequest $request): JsonResponse
    {
        $categoria = Categoria::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente.',
            'data' => $categoria,
        ], 201);
    }

    public function show(Categoria $categoria): JsonResponse
    {
        $categoria->load('productos');

        return response()->json(['success' => true, 'data' => $categoria]);
    }

    public function update(CategoriaRequest $request, Categoria $categoria): JsonResponse
    {
        $categoria->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente.',
            'data' => $categoria,
        ]);
    }

    public function destroy(Categoria $categoria): JsonResponse
    {
        if ($categoria->productos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría porque tiene productos asociados.',
            ], 409);
        }

        $categoria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada exitosamente.',
        ]);
    }
}
