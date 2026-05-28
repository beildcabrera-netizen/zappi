<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Sale $sale): Response
    {
        return Response::allow();
    }

    public function create(User $user): Response
    {
        return $user->hasAnyRole(['administrador', 'vendedor'])
            ? Response::allow()
            : Response::deny('No autorizado.');
    }

    public function collect(User $user, Sale $sale): Response
    {
        if (!$user->puede_cobrar) {
            return Response::deny('No tienes permiso para cobrar en el modo actual.');
        }

        if ($sale->estado_venta !== 'en_caja') {
            return Response::deny('La venta no está en estado de caja.');
        }

        return Response::allow();
    }

    public function cancel(User $user, Sale $sale): Response
    {
        if (!$user->hasRole('administrador')) {
            return Response::deny('Solo el administrador puede anular ventas.');
        }

        if ($sale->estado_venta === 'anulada') {
            return Response::deny('La venta ya está anulada.');
        }

        return Response::allow();
    }
}
