<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface ProductoRepositoryInterface
{
    public function findById(int $id): ?Model;
    public function findByCodigo(string $codigo): ?Model;
    public function search(string $query): iterable;
    public function create(array $data): Model;
    public function update(Model $product, array $data): Model;
    public function delete(Model $product): bool;
    public function paginate(int $perPage = 15): iterable;
    public function stockBajo(): iterable;
}
