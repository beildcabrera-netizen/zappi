<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->hasAnyRole(['administrador', 'vendedor', 'cajero'])
            ? Response::allow()
            : Response::deny('No autorizado.');
    }

    public function view(User $user, Product $product): Response
    {
        return Response::allow();
    }

    public function create(User $user): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow()
            : Response::deny('Solo el administrador puede crear productos.');
    }

    public function update(User $user, Product $product): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow()
            : Response::deny('Solo el administrador puede modificar productos.');
    }

    public function delete(User $user, Product $product): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow()
            : Response::deny('Solo el administrador puede eliminar productos.');
    }
}
