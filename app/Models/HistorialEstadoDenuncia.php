<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistorialEstadoDenuncia extends Model
{
    protected $table = 'historial_estado_denuncias'; // Nombre de tu tabla

    protected $fillable = [
        'denuncia_id',
        'estado_nuevo_id',
        'admin_id',
        'created_at',
    ];


    const UPDATED_AT = null;

    public function estadoNuevo()
    {
        return $this->belongsTo(EstadoDenuncia::class, 'estado_nuevo_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

}

?>