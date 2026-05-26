<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre', 'documento', 'telefono',
        'email', 'direccion', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
