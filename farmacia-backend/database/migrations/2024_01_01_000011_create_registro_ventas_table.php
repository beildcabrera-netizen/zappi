<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->string('cuf', 100)->unique()->nullable();
            $table->string('codigo_autorizacion', 50)->nullable();
            $table->string('estado_sin', 50)->default('pendiente');
            $table->text('xml_envio')->nullable();
            $table->text('xml_respuesta')->nullable();
            $table->integer('numero_factura')->nullable();
            $table->dateTime('fecha_envio')->nullable();
            $table->text('mensaje_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_ventas');
    }
};
