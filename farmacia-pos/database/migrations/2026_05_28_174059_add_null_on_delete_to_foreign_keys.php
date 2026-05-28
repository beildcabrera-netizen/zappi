<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('manual_invoices', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->foreign('sale_id')->references('id')->on('sales')->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['vendedor_id']);
            $table->foreign('vendedor_id')->references('id')->on('users')->nullOnDelete();

            $table->dropForeign(['cajero_id']);
            $table->foreign('cajero_id')->references('id')->on('users')->nullOnDelete();

            $table->dropForeign(['caja_id']);
            $table->foreign('caja_id')->references('id')->on('cash_registers')->nullOnDelete();

            $table->dropForeign(['turno_vendedor_id']);
            $table->foreign('turno_vendedor_id')->references('id')->on('cash_shifts')->nullOnDelete();

            $table->dropForeign(['turno_cajero_id']);
            $table->foreign('turno_cajero_id')->references('id')->on('cash_shifts')->nullOnDelete();

            $table->dropForeign(['factura_manual_id']);
            $table->foreign('factura_manual_id')->references('id')->on('manual_invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('manual_invoices', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->foreign('sale_id')->references('id')->on('sales');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['vendedor_id']);
            $table->foreign('vendedor_id')->references('id')->on('users');

            $table->dropForeign(['cajero_id']);
            $table->foreign('cajero_id')->references('id')->on('users');

            $table->dropForeign(['caja_id']);
            $table->foreign('caja_id')->references('id')->on('cash_registers');

            $table->dropForeign(['turno_vendedor_id']);
            $table->foreign('turno_vendedor_id')->references('id')->on('cash_shifts');

            $table->dropForeign(['turno_cajero_id']);
            $table->foreign('turno_cajero_id')->references('id')->on('cash_shifts');

            $table->dropForeign(['factura_manual_id']);
            $table->foreign('factura_manual_id')->references('id')->on('manual_invoices');
        });
    }
};
