<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('numero_venta', 20)->unique();
            $table->enum('estado_venta', ['pendiente', 'en_caja', 'completada', 'anulada', 'expirada', 'rechazada'])->default('pendiente');
            $table->foreignId('caja_id')->nullable()->constrained('cash_registers');
            $table->foreignId('turno_vendedor_id')->nullable()->constrained('cash_shifts');
            $table->foreignId('turno_cajero_id')->nullable()->constrained('cash_shifts');
            $table->foreignId('vendedor_id')->constrained('users');
            $table->foreignId('cajero_id')->nullable()->constrained('users');
            $table->enum('cliente_tipo', ['consumidor_final', 'con_nit', 'consulado', 'control_tributario', 'venta_menor'])->default('consumidor_final');
            $table->string('cliente_nit', 15)->default('0');
            $table->string('cliente_complemento', 5)->nullable();
            $table->string('cliente_razon_social', 150)->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento_total', 12, 2)->default(0);
            $table->decimal('total_venta', 12, 2);
            $table->decimal('total_final', 12, 2);
            $table->enum('metodo_pago', ['efectivo', 'qr_bancario', 'tarjeta_debito', 'tarjeta_credito', 'transferencia']);
            $table->enum('tipo_documento', ['factura_manual', 'recibo', 'nota_venta']);
            $table->foreignId('factura_manual_id')->nullable()->constrained('manual_invoices');
            $table->decimal('recibido_efectivo', 12, 2)->nullable();
            $table->decimal('cambio', 12, 2)->nullable();
            $table->string('codigo_transaccion_qr', 100)->nullable();
            $table->string('referencia_transferencia', 100)->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users');
            $table->timestamp('anulado_at')->nullable();
            $table->timestamp('enviada_a_caja_at')->nullable();
            $table->timestamp('expira_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->enum('presentacion_vendida', ['unidad', 'blister', 'caja']);
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento_item', 12, 2)->default(0);
            $table->decimal('total_item', 12, 2);
            $table->integer('unidades_descontadas');
            $table->string('receta_numero', 50)->nullable();
            $table->string('receta_medico', 100)->nullable();
            $table->string('receta_foto_url', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
