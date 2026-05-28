<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_invoices', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('manual_invoices', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropColumn('sale_id');
        });
    }
};
