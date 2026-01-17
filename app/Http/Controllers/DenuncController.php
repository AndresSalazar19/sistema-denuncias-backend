<?php

namespace App\Http\Controllers;

use App\Models\Denuncia;
use App\Models\Categoria;
use App\Models\EstadoDenuncia;
use App\Models\HistorialEstadoDenuncia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DenuncController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validar los datos según la nueva estructura de tablas
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
            'ubicacion_direccion' => 'nullable|string|max:255',
            'ubicacion_lat' => 'nullable|numeric|between:-90,90',
            'ubicacion_lng' => 'nullable|numeric|between:-180,180'
        ]);

        // 2. Generar el código único de seguimiento
        $validated['codigo_seguimiento'] = $this->generarCodigoUnico();
        
        // 3. Estado inicial por defecto (ID 1 = "Nueva")
        $estadoInicial = EstadoDenuncia::where('slug', 'nueva')->first();
        $validated['estado_id'] = $estadoInicial->id;

        // 4. Crear el registro
        $denuncia = Denuncia::create($validated);

        // 5. Registrar en el historial de estados
        HistorialEstadoDenuncia::create([
            'denuncia_id' => $denuncia->id,
            'estado_nuevo_id' => $estadoInicial->id,
            'admin_id' => null, // Sin admin porque es creación automática
            'created_at' => now()
        ]);

        return response()->json([
            'message' => 'Denuncia registrada con éxito',
            'codigo' => $denuncia->codigo_seguimiento,
            'data' => $denuncia->load('categoria', 'estado')
        ], 201);
    }


    public function showByCode($codigo)
    {
        // 1. Buscar la denuncia por el código único de seguimiento
        $denuncia = Denuncia::with(['categoria', 'estado', 'evidencias'])
            ->where('codigo_seguimiento', $codigo)
            ->first();

        // 2. Validar si existe
        if (!$denuncia) {
            return response()->json([
                'message' => 'El código de seguimiento no es válido o no existe.'
            ], 404);
        }

        // 3. Retornar la información para consulta pública
        return response()->json([
            'codigo_seguimiento' => $denuncia->codigo_seguimiento,
            'estado' => $denuncia->estado->name,
            'estado_color' => $denuncia->estado->color,
            'categoria' => $denuncia->categoria->name,
            'fecha_registro' => $denuncia->created_at->format('d/m/Y - H:i'),
            'fecha_actualizacion' => $denuncia->updated_at->format('d/m/Y - H:i'),
            'ubicacion' => [
                'direccion' => $denuncia->ubicacion_direccion,
                'lat' => $denuncia->ubicacion_lat,
                'lng' => $denuncia->ubicacion_lng
            ],
            'evidencias' => $denuncia->evidencias->map(function($evidencia) {
                return [
                    'tipo' => $evidencia->file_type,
                    'url' => asset('storage/' . $evidencia->file_path)
                ];
            })
        ]);
    }

    public function search(Request $request)
    {
        // Iniciamos la consulta con relaciones
        $query = Denuncia::with(['categoria', 'estado']);

        // Filtro por Categoría (ahora usando categoria_id)
        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtro por slug de categoría (más amigable)
        if ($request->has('categoria')) {
            $categoria = Categoria::where('slug', $request->categoria)->first();
            if ($categoria) {
                $query->where('categoria_id', $categoria->id);
            }
        }

        // Filtro por Estado (ahora usando estado_id)
        if ($request->has('estado_id')) {
            $query->where('estado_id', $request->estado_id);
        }

        // Filtro por slug de estado (más amigable)
        if ($request->has('estado')) {
            $estado = EstadoDenuncia::where('slug', $request->estado)->first();
            if ($estado) {
                $query->where('estado_id', $estado->id);
            }
        }

        // Filtro por Código de Seguimiento
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

        // Ordenar resultados por fecha descendente
        $denuncias = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'total' => $denuncias->count(),
            'data' => $denuncias
        ]);
    }
    

    /**
     * Genera un código único de seguimiento
     */
    private function generarCodigoUnico()
    {
        do {
            $codigo = 'DEN-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (Denuncia::where('codigo_seguimiento', $codigo)->exists());
        
        return $codigo;
    }
}