<?php

namespace App\Http\Controllers;

use App\Models\Denuncia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DenunciaController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validar los datos según tu estructura de tabla
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'categoria' => 'required|in:infraestructura,seguridad,servicios_publicos,medio_ambiente,corrupcion',
            'ubicacion_direccion' => 'nullable|string|max:255',
            'ubicacion_lat' => 'nullable|numeric',
            'ubicacion_lng' => 'nullable|numeric',
            'imagenes' => 'nullable|array'
        ]);

        // 2. Generar el código único (ej: DEN-2025-XXXX) [cite: 54, 72]
        $validated['codigo_seguimiento'] = 'DEN-' . date('Y') . '-' . strtoupper(Str::random(6));
        
        // Estado inicial por defecto [cite: 30]
        $validated['estado'] = 'nueva';

        // 3. Crear el registro en Google Cloud
        $denuncia = Denuncia::create($validated);

        return response()->json([
            'message' => 'Denuncia registrada con éxito',
            'codigo' => $denuncia->codigo_seguimiento,
            'data' => $denuncia
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        // 1. Validar que el estado sea uno de los definidos en tu base de datos
        $request->validate([
            'estado' => 'required|in:nueva,en_revision,en_proceso,resueltas,rechazada'
        ]);

        // 2. Buscar la denuncia en Google Cloud
        $denuncia = \App\Models\Denuncia::find($id);

        if (!$denuncia) {
            return response()->json(['message' => 'Denuncia no encontrada'], 404);
        }

        // 3. Actualizar el estado
        $denuncia->estado = $request->estado;
        $denuncia->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'denuncia' => [
                'id' => $denuncia->id,
                'codigo_seguimiento' => $denuncia->codigo_seguimiento,
                'nuevo_estado' => $denuncia->estado
            ]
        ]);
    }

    public function showByCode($codigo)
    {
        // 1. Buscar la denuncia por el código único de seguimiento 
        $denuncia = \App\Models\Denuncia::where('codigo_seguimiento', $codigo)->first();

        // 2. Validar si existe
        if (!$denuncia) {
            return response()->json([
                'message' => 'El código de seguimiento no es válido o no existe.'
            ], 404);
        }

        // 3. Retornar la información para el prototipo de consulta [cite: 73, 230]
        return response()->json([
            'codigo_seguimiento' => $denuncia->codigo_seguimiento,
            'estado'             => $denuncia->estado, // Nueva, En Revisión, etc. [cite: 30]
            'categoria'          => $denuncia->categoria,
            'fecha_registro'     => $denuncia->created_at->format('d/m/Y - H:i'),
            'fecha_actualizacion'=> $denuncia->updated_at->format('d/m/Y - H:i'),
            'ubicacion' => [
                'direccion' => $denuncia->ubicacion_direccion,
                'lat'       => $denuncia->ubicacion_lat,
                'lng'       => $denuncia->ubicacion_lng
            ],
            'imagenes'           => $denuncia->imagenes // Máximo 3 imágenes [cite: 43]
        ]);
    }

    public function search(Request $request)
    {
        // Iniciamos la consulta
        $query = Denuncia::query();

        // Filtro por Categoría 
        if ($request->has('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        // Filtro por Estado 
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por Código de Seguimiento [cite: 73]
        if ($request->has('codigo')) {
            $query->where('codigo_seguimiento', $request->codigo);
        }

        // Búsqueda por palabras clave en Título o Descripción 
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('titulo', 'LIKE', "%$keyword%")
                ->orWhere('descripcion', 'LIKE', "%$keyword%");
            });
        }

        // Filtro por Rango de Fechas 
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
        }

        // Ordenar resultados por fecha descendente (más recientes primero) 
        $denuncias = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'total' => $denuncias->count(),
            'data' => $denuncias
        ]);
    }
    
    public function publicStats()
    {
        // 1. Conteo por categoría para el gráfico de pastel [cite: 73]
        $porCategoria = \App\Models\Denuncia::select('categoria', \DB::raw('count(*) as total'))
            ->groupBy('categoria')
            ->get();

        // 2. Conteo por estado para métricas clave [cite: 73]
        $porEstado = \App\Models\Denuncia::select('estado', \DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        // 3. Datos anonimizados para el mapa público [cite: 74]
        // Solo enviamos coordenadas y estado para proteger la identidad
        $mapaPuntos = \App\Models\Denuncia::select('ubicacion_lat', 'ubicacion_lng', 'estado')
            ->whereNotNull('ubicacion_lat')
            ->get();

        return response()->json([
            'stats_categorias' => $porCategoria,
            'stats_estados'    => $porEstado,
            'mapa_calor'       => $mapaPuntos,
            'mensaje'          => 'Datos anonimizados para consulta pública'
        ]);
    }
    
}