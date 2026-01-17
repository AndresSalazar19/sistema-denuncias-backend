<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_estado_denuncias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('denuncia_id')->constrained('denuncias')->onDelete('cascade');
            $table->foreignId('estado_nuevo_id')->constrained('estado_denuncias')->onDelete('restrict');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('created_at');
            
            $table->index('denuncia_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_estado_denuncias');
    }
};