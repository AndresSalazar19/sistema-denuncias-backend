<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('denuncias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_seguimiento', 20)->unique();
            $table->string('titulo', 200);
            $table->text('descripcion');
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('restrict');
            $table->foreignId('estado_id')->default(1)->constrained('estado_denuncias')->onDelete('restrict');
            $table->foreignId('responsable_id')
                ->nullable()
                ->constrained('responsables')
                ->nullOnDelete();
            $table->string('ubicacion_direccion', 255)->nullable();
            $table->decimal('ubicacion_lat', 10, 8)->nullable();
            $table->decimal('ubicacion_lng', 11, 8)->nullable();
            $table->timestamps();
            
            $table->index('codigo_seguimiento');
            $table->index('responsable_id');
            $table->index('categoria_id');
            $table->index('estado_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('denuncias');
    }
};