<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden', 20)->unique();
            $table->foreignId('supplier_id')->constrained();
            $table->date('fecha_orden');
            $table->date('fecha_recepcion')->nullable();
            $table->enum('estado', ['pendiente', 'parcial', 'recibida', 'cancelada'])->default('pendiente');
            $table->decimal('monto_total', 12, 2);
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained();
            $table->string('nombre_producto_temp', 150)->nullable();
            $table->enum('presentacion_comprada', ['unidad', 'blister', 'caja', 'frasco', 'tubo']);
            $table->decimal('cantidad', 10, 2);
            $table->decimal('costo_unitario', 12, 2);
            $table->decimal('costo_unidad_base', 12, 2);
            $table->string('lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('estante_destino', 10)->nullable();
            $table->string('seccion_destino', 50)->nullable();
            $table->boolean('recibido')->default(false);
            $table->decimal('cantidad_recibida', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
