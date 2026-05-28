<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasCodigoInterno
{
    protected static function bootHasCodigoInterno(): void
    {
        static::creating(function ($model) {
            if (empty($model->codigo_interno)) {
                $model->codigo_interno = static::generarCodigoInterno();
            }
        });
    }

    protected static function generarCodigoInterno(): string
    {
        $table = (new static)->getTable();

        return DB::transaction(function () use ($table) {
            $last = DB::table($table)
                ->where('codigo_interno', 'like', 'PROD-%')
                ->orderBy('codigo_interno', 'desc')
                ->lockForUpdate()
                ->first();

            if ($last) {
                $numero = (int) substr($last->codigo_interno, 5) + 1;
            } else {
                $numero = 1;
            }

            return 'PROD-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
        });
    }
}
