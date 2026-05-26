<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $fillable = [
        'nombre', 'nit', 'contacto', 'telefono',
        'email', 'direccion', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }
}
