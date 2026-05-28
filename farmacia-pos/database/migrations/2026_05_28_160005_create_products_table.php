<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_barras', 50)->nullable()->unique();
            $table->string('codigo_interno', 20)->unique();
            $table->string('nombre_comercial', 150);
            $table->string('nombre_generico', 150);
            $table->string('principio_activo', 200);
            $table->string('concentracion', 50);
            $table->enum('forma_farmaceutica', ['tableta', 'capsula', 'jarabe', 'crema', 'inyeccion', 'gotas', 'polvo', 'supositorio', 'parche', 'inhalador', 'solucion']);
            $table->string('laboratorio', 100);
            $table->string('registro_sanitario', 50)->nullable();
            $table->enum('presentacion_entrada', ['unidad', 'blister', 'caja', 'frasco', 'tubo']);
            $table->smallInteger('unidades_por_blister')->nullable()->default(0);
            $table->smallInteger('blisters_por_caja')->nullable()->default(0);
            $table->boolean('fraccionamiento_habilitado')->default(false);
            $table->decimal('precio_venta_unidad', 12, 2);
            $table->decimal('precio_venta_blister', 12, 2)->nullable();
            $table->decimal('precio_venta_caja', 12, 2)->nullable();
            $table->decimal('costo_compra_unidad', 12, 2);
            $table->integer('stock_unidades')->default(0);
            $table->integer('stock_blisters')->default(0);
            $table->integer('stock_cajas')->default(0);
            $table->integer('stock_minimo_alertas')->default(10);
            $table->string('estante', 10);
            $table->string('seccion', 50);
            $table->string('ubicacion_detalle', 100)->nullable();
            $table->boolean('controlado')->default(false);
            $table->enum('tipo_controlado', ['receta_simple', 'receta_retenida', 'estupefaciente'])->nullable();
            $table->boolean('refrigerado')->default(false);
            $table->string('foto_url', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
