<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denuncia extends Model
{
    protected $table = 'denuncias'; // Nombre de tu tabla

    protected $fillable = [
        'codigo_seguimiento',
        'titulo',
        'descripcion',
        'categoria',
        'estado',
        'ubicacion_direccion',
        'ubicacion_lat',
        'ubicacion_lng',
        'imagenes'
    ];

    // Convierte el JSON de la base de datos a un array de PHP automáticamente
    protected $casts = [
        'imagenes' => 'array',
    ];
}