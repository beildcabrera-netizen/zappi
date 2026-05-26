<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_sin', function (Blueprint $table) {
            $table->id();
            $table->string('nit', 20);
            $table->string('razon_social', 200);
            $table->string('nombre_comercial', 200)->nullable();
            $table->string('codigo_sucursal', 4)->default('0');
            $table->string('direccion', 300)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('pais', 100)->default('Bolivia');
            $table->string('cuis', 20)->nullable();
            $table->string('cufd', 50)->nullable();
            $table->dateTime('cuis_fecha')->nullable();
            $table->dateTime('cufd_fecha')->nullable();
            $table->string('tipo_modalidad', 2)->default('1');
            $table->string('tipo_emision', 2)->default('1');
            $table->string('tipo_documento_sector', 2)->default('1');
            $table->string('leyenda_1', 300)->nullable();
            $table->string('leyenda_2', 300)->nullable();
            $table->string('codigo_control', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sin');
    }
};
