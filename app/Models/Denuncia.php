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
        'categoria_id',
        'estado_id',
        'ubicacion_direccion',
        'ubicacion_lat',
        'ubicacion_lng',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function estado()
    {
        return $this->belongsTo(EstadoDenuncia::class, 'estado_id');
    }

    public function evidencias()
    {
        return $this->hasMany(EvidenciaDenuncia::class, 'denuncia_id');
    }
    
}