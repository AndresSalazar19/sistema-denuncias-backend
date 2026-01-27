<?php
namespace App\Http\Controllers;
use App\Models\Denuncia;
use App\Models\Responsable;

Class UtilitariosController extends Controller
{
   public function getDenuncias()
    {
        $denuncias = Denuncia::with(['categoria', 'estado','evidencias'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'total' => $denuncias->count(),
            'data' => $denuncias
        ]);
    }

    public function getResponsables()
    {
        // Suponiendo que tienes un modelo Responsable
        $responsables = Responsable::all();

        return response()->json([
            'total' => $responsables->count(),
            'data' => $responsables
        ]);
    }
}

?>