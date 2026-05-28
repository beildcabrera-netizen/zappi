<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('tipo_turno', ['vendedor_cobra', 'cajero', 'vendedor_solo']);
            $table->timestamp('fecha_apertura');
            $table->timestamp('fecha_cierre')->nullable();
            $table->decimal('monto_inicial', 12, 2);
            $table->decimal('monto_final_declarado', 12, 2)->nullable();
            $table->decimal('monto_final_calculado', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->nullable();
            $table->enum('estado', ['abierta', 'cerrada', 'cuadrada', 'descuadrada'])->default('abierta');
            $table->integer('ventas_propias_count')->default(0);
            $table->integer('ventas_cobradas_otros_count')->default(0);
            $table->decimal('total_ventas_propias', 12, 2)->default(0);
            $table->decimal('total_ventas_otros', 12, 2)->default(0);
            $table->text('observaciones_cierre')->nullable();
            $table->foreignId('cerrado_por')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_shifts');
    }
};
