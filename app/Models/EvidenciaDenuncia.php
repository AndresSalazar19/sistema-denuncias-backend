<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidenciaDenuncia extends Model
{
    use HasFactory;

    protected $table = 'evidencia_denuncias';

    protected $fillable = [
        'denuncia_id',
        'file_path',
        'file_name',
        'file_size',
    ];

    // Relación inversa
    public function denuncia()
    {
        return $this->belongsTo(Denuncia::class);
    }
}
