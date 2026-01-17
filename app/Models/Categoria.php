<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias'; // Nombre de tu tabla

    protected $fillable = [
        'id',
        'nombre',
    ];
}

?>