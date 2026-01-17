<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NotasInternas extends Model
{
    protected $table = 'notas_internas_denuncias'; // Nombre de tu tabla

    protected $fillable = [
        'denuncia_id',
        'admin_id',
        'nota',
        'created_at',
    ];

    const UPDATED_AT = null;

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}

?>