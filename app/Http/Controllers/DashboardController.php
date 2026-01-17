<?php 

namespace App\Http\Controllers;

use App\Models\Denuncia;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function adminStats()
    {
        // 1. Conteo por categoría para el gráfico
        $porCategoria = Denuncia::select('categoria_id', DB::raw('count(*) as total'))
            ->with('categoria:id,name,slug')
            ->groupBy('categoria_id')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria->name,
                    'slug' => $item->categoria->slug,
                    'total' => $item->total
                ];
            });

        // 2. Conteo por estado para métricas clave
        $porEstado = Denuncia::select('estado_id', DB::raw('count(*) as total'))
            ->with('estado:id,name,slug,color')
            ->groupBy('estado_id')
            ->get()
            ->map(function($item) {
                return [
                    'estado' => $item->estado->name,
                    'slug' => $item->estado->slug,
                    'color' => $item->estado->color,
                    'total' => $item->total
                ];
            });

        // 3. Datos anonimizados para el mapa público
        $mapaPuntos = Denuncia::select('ubicacion_lat', 'ubicacion_lng', 'estado_id')
            ->with('estado:id,name,color')
            ->whereNotNull('ubicacion_lat')
            ->whereNotNull('ubicacion_lng')
            ->get()
            ->map(function($item) {
                return [
                    'lat' => $item->ubicacion_lat,
                    'lng' => $item->ubicacion_lng,
                    'estado' => $item->estado->name,
                    'color' => $item->estado->color
                ];
            });

        return response()->json([
            'stats_categorias' => $porCategoria,
            'stats_estados' => $porEstado,
            'mapa_calor' => $mapaPuntos,
            'mensaje' => 'Datos anonimizados para consulta pública'
        ]);
    }
}


?>