<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait HasAuditableChanges
{
    protected static function bootHasAuditableChanges(): void
    {
        static::created(function ($model) {
            self::guardarAudit($model, 'creacion', null, $model->toArray());
        });

        static::updated(function ($model) {
            $cambios = $model->getChanges();
            if (!empty($cambios)) {
                $original = array_intersect_key($model->getOriginal(), $cambios);
                self::guardarAudit($model, 'actualizacion', $original, $cambios);
            }
        });

        static::deleted(function ($model) {
            self::guardarAudit($model, 'eliminacion', $model->toArray(), null);
        });
    }

    protected static function guardarAudit($model, string $accion, $oldValues, $newValues): void
    {
        try {
            $user = Auth::user();
            AuditLog::create([
                'user_id'    => $user?->id,
                'accion'     => $accion,
                'tabla'      => $model->getTable(),
                'registro_id' => $model->id,
                'valores_anteriores' => $oldValues ? json_encode($oldValues) : null,
                'valores_nuevos' => $newValues ? json_encode($newValues) : null,
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);
        } catch (\Throwable $e) {
        }
    }
}
