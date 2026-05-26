<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_factura', 50)->unique()->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('nombre_cliente', 150)->nullable();
            $table->string('documento_cliente', 20)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('ice', 12, 2)->default(0);
            $table->decimal('iehd', 12, 2)->default(0);
            $table->decimal('ipj', 12, 2)->default(0);
            $table->string('metodo_pago', 50)->default('efectivo');
            $table->enum('estado', ['completada', 'anulada', 'pendiente'])->default('completada');
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
