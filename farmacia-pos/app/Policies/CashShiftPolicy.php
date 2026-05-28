<?php

namespace App\Policies;

use App\Models\CashShift;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CashShiftPolicy
{
    public function open(User $user): Response
    {
        if ($user->turnoActivo) {
            return Response::deny('Ya tienes un turno activo.');
        }

        return Response::allow();
    }

    public function close(User $user, CashShift $cashShift): Response
    {
        if ($cashShift->user_id !== $user->id && !$user->hasRole('administrador')) {
            return Response::deny('Solo puedes cerrar tus propios turnos.');
        }

        if ($cashShift->estado === 'cerrada') {
            return Response::deny('El turno ya está cerrado.');
        }

        return Response::allow();
    }

    public function viewReports(User $user, CashShift $cashShift): Response
    {
        return $user->hasRole('administrador')
            ? Response::allow()
            : Response::deny('No autorizado.');
    }
}
