<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('numero_factura', 50)->nullable();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->string('nit_proveedor', 20)->nullable();
            $table->string('nombre_proveedor', 150)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('credito_fiscal', 12, 2)->default(0);
            $table->date('fecha_compra');
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
