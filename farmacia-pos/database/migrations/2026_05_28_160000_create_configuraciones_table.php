<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_farmacia', 100);
            $table->string('nit_farmacia', 15);
            $table->string('razon_social_farmacia', 150);
            $table->string('direccion', 200);
            $table->string('telefono', 20)->nullable();
            $table->string('ciudad', 50);
            $table->string('departamento', 50);
            $table->string('actividad_economica', 100);
            $table->string('logo_url', 255)->nullable();
            $table->string('impresora_default', 100)->nullable();
            $table->decimal('iva_porcentaje', 5, 2)->default(13.00);
            $table->string('moneda_simbolo', 3)->default('Bs');
            $table->boolean('tasa_cero_habilitada')->default(false);
            $table->integer('tiempo_expiracion_venta_caja')->default(15);
            $table->integer('alerta_stock_dias')->default(30);
            $table->string('llave_dosificacion', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
