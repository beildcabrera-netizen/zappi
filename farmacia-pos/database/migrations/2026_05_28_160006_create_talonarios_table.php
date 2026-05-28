<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talonarios', function (Blueprint $table) {
            $table->id();
            $table->string('numero_autorizacion', 50);
            $table->string('numero_tramite', 50);
            $table->string('sucursal', 100);
            $table->string('actividad_economica', 100);
            $table->date('fecha_autorizacion');
            $table->date('fecha_limite_emision');
            $table->integer('rango_inicio');
            $table->integer('rango_fin');
            $table->integer('siguiente_numero');
            $table->integer('cantidad_solicitada');
            $table->string('pin_entrega', 20)->nullable();
            $table->date('fecha_activacion')->nullable();
            $table->enum('estado', ['generado', 'asignado', 'para_entrega', 'activado', 'agotado', 'vencido'])->default('activado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talonarios');
    }
};
