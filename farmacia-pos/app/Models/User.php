<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasRoles, HasFactory, Notifiable;

    protected $fillable = ['nombre', 'email', 'password', 'rol', 'puede_cobrar', 'telefono', 'caja_preferida_id', 'activo'];

    protected $casts = [
        'puede_cobrar' => 'boolean',
        'activo' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function esAdministrador(): bool
    {
        return $this->hasRole('administrador');
    }

    public function esVendedor(): bool
    {
        return $this->hasRole('vendedor');
    }

    public function esCajero(): bool
    {
        return $this->hasRole('cajero');
    }

    public function turnos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CashShift::class);
    }

    public function turnoActivo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CashShift::class)->where('estado', 'abierta');
    }

    public function ventasComoVendedor(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class, 'vendedor_id');
    }

    public function ventasComoCajero(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class, 'cajero_id');
    }

    public function cajaPreferida(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CashRegister::class, 'caja_preferida_id');
    }
}
