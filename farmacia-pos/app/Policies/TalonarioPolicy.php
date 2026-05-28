<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class TalonarioPolicy
{
    public function viewAny(User $user): Response
    {
        return $user->hasAnyRole(['administrador', 'vendedor'])
            ? Response::allow()
            : Response::deny('No autorizado.');
    }

    public function create(User $user): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow()
            : Response::deny('Solo el administrador puede registrar talonarios.');
    }

    public function activate(User $user): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow()
            : Response::deny('Solo el administrador puede activar talonarios.');
    }
}
