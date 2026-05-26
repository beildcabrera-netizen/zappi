<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    public function index(): JsonResponse
    {
        $clientes = Cliente::orderBy('nombre')->get();

        return response()->json(['success' => true, 'data' => $clientes]);
    }

    public function store(ClienteRequest $request): JsonResponse
    {
        $cliente = Cliente::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado exitosamente.',
            'data' => $cliente,
        ], 201);
    }

    public function show(Cliente $cliente): JsonResponse
    {
        $cliente->load('ventas');

        return response()->json(['success' => true, 'data' => $cliente]);
    }

    public function update(ClienteRequest $request, Cliente $cliente): JsonResponse
    {
        $cliente->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado exitosamente.',
            'data' => $cliente,
        ]);
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Cliente desactivado exitosamente.',
        ]);
    }

    public function buscar(string $query): JsonResponse
    {
        $clientes = Cliente::where('activo', true)
            ->where(function ($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                    ->orWhere('documento', 'like', "%{$query}%")
                    ->orWhere('telefono', 'like', "%{$query}%");
            })
            ->orderBy('nombre')
            ->limit(20)
            ->get();

        return response()->json(['success' => true, 'data' => $clientes]);
    }
}
