<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 200);
            $table->string('principio_activo', 200)->nullable();
            $table->string('concentracion', 100)->nullable();
            $table->string('forma_farmaceutica', 100)->nullable();
            $table->string('presentacion', 100)->nullable();
            $table->string('registro_sanitario', 50)->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->decimal('precio_compra', 12, 2)->default(0);
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->decimal('ganancia_porcentaje', 5, 2)->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
