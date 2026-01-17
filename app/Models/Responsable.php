<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Responsable extends Model
{
    protected $table = 'responsables'; // Nombre de tu tabla

    protected $fillable = [
        'name',
        'surname',
    ];
}

?>