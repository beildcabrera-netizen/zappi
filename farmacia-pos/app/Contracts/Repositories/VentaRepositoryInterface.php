<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface VentaRepositoryInterface
{
    public function findById(int $id): ?Model;
    public function create(array $data): Model;
    public function update(Model $sale, array $data): Model;
    public function delete(Model $sale): bool;
    public function pendientes(): iterable;
    public function enCaja(): iterable;
    public function completadas(): iterable;
    public function paginate(int $perPage = 15): iterable;
    public function reporteVentas(array $filters = []): iterable;
}
