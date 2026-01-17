<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Categorías
        DB::table('categorias')->insert([
            ['name' => 'Infraestructura', 'slug' => 'infraestructura', 'descripcion' => 'Problemas de calles, puentes, edificios públicos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Seguridad', 'slug' => 'seguridad', 'descripcion' => 'Delincuencia, iluminación, vigilancia', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Servicios Públicos', 'slug' => 'servicios-publicos', 'descripcion' => 'Agua, luz, recolección de basura', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medio Ambiente', 'slug' => 'medio-ambiente', 'descripcion' => 'Contaminación, áreas verdes, reciclaje', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Corrupción', 'slug' => 'corrupcion', 'descripcion' => 'Denuncias de actos de corrupción', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Estados
        DB::table('estado_denuncias')->insert([
            ['name' => 'Nueva', 'slug' => 'nueva', 'color' => '#3B82F6', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'En Revisión', 'slug' => 'en-revision', 'color' => '#F59E0B', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'En Proceso', 'slug' => 'en-proceso', 'color' => '#8B5CF6', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Resuelta', 'slug' => 'resuelta', 'color' => '#10B981', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rechazada', 'slug' => 'rechazada', 'color' => '#EF4444', 'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}