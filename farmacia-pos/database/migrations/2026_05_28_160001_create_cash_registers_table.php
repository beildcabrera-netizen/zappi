<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('ubicacion', 100)->nullable();
            $table->string('impresora_tickets', 100)->nullable();
            $table->enum('modo_override', ['vendedor_cobra', 'cajero_dedicado'])->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
