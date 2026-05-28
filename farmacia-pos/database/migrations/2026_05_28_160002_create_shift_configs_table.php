<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_configs', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->json('dias_semana');
            $table->enum('modo_operacion', ['vendedor_cobra', 'cajero_dedicado', 'mixto']);
            $table->json('cajas_activas');
            $table->integer('min_personal')->default(1);
            $table->integer('prioridad')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_configs');
    }
};
