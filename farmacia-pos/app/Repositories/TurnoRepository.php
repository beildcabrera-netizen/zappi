<?php

namespace App\Repositories;

use App\Contracts\Repositories\TurnoRepositoryInterface;
use App\Models\CashShift;
use App\Models\User;

class TurnoRepository extends BaseRepository implements TurnoRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new CashShift);
    }

    public function findActivoByUser(User $user): ?CashShift
    {
        return CashShift::where('user_id', $user->id)
            ->whereNull('cierre')
            ->first();
    }

    public function findActivoByCaja(int $cajaId): ?CashShift
    {
        return CashShift::where('caja_id', $cajaId)
            ->whereNull('cierre')
            ->first();
    }

    public function close(CashShift $shift, array $data): CashShift
    {
        $shift->update(array_merge($data, ['cierre' => now()]));
        return $shift->fresh();
    }

    public function historial(User $user, int $limit = 10): iterable
    {
        return CashShift::where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
