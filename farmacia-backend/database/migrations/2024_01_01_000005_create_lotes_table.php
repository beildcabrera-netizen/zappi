<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('numero_lote', 100);
            $table->date('fecha_vencimiento');
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_inicial')->default(0);
            $table->decimal('precio_compra', 12, 2)->default(0);
            $table->date('fecha_recepcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['producto_id', 'numero_lote']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
