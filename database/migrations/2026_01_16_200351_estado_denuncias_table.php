<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('estado_denuncias', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->string('color', 7)->default('#6B7280'); // Color hex para UI
            $table->integer('orden')->default(0); // Para ordenar estados
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estado_denuncias');
    }
};