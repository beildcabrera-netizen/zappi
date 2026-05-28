<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talonario_id')->constrained();
            $table->string('numero_factura', 20);
            $table->string('numero_completo', 30);
            $table->string('codigo_autorizacion', 50);
            $table->string('codigo_control', 50);
            $table->date('fecha_emision');
            $table->string('nit_cliente', 15);
            $table->string('complemento', 5)->nullable();
            $table->string('razon_social_cliente', 150)->nullable();
            $table->decimal('importe_total', 12, 2);
            $table->decimal('importe_ice', 12, 2)->default(0);
            $table->decimal('importe_iehd', 12, 2)->default(0);
            $table->decimal('importe_ipj', 12, 2)->default(0);
            $table->decimal('importe_tasas', 12, 2)->default(0);
            $table->decimal('importe_otros_no_sujeto_iva', 12, 2)->default(0);
            $table->decimal('exportaciones', 12, 2)->default(0);
            $table->decimal('ventas_tasa_cero', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuentos', 12, 2)->default(0);
            $table->decimal('importe_gift_card', 12, 2)->default(0);
            $table->decimal('base_debito_fiscal', 12, 2);
            $table->decimal('debito_fiscal', 12, 2);
            $table->enum('estado', ['V', 'A', 'C', 'L'])->default('V');
            $table->foreignId('vendedor_id')->constrained('users');
            $table->foreignId('caja_id')->nullable()->constrained('cash_registers');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_invoices');
    }
};
