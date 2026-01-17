<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoDenuncia extends Model
{
    protected $table = 'estado_denuncias'; // Nombre de tu tabla

    protected $fillable = [
        'id',
        'name',
        'slug',
        'color'
    ];
}

?>