<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notas_internas_denuncias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('denuncia_id')->constrained('denuncias')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('admins')->onDelete('restrict');
            $table->text('nota');
            $table->timestamp('created_at');
            
            $table->index('denuncia_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notas_internas_denuncias');
    }
};