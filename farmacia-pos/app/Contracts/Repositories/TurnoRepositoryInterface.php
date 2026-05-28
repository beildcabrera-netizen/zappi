<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

interface TurnoRepositoryInterface
{
    public function findActivoByUser(User $user): ?Model;
    public function findActivoByCaja(int $cajaId): ?Model;
    public function create(array $data): Model;
    public function close(Model $shift, array $data): Model;
    public function historial(User $user, int $limit = 10): iterable;
}
