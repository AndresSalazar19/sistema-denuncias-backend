<?php

namespace App\Http\Controllers;

use App\Models\Denuncia;
use App\Models\Categoria;
use App\Models\EstadoDenuncia;
use App\Models\EvidenciaDenuncia;
use App\Models\HistorialEstadoDenuncia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DenuncController extends Controller
{
    public function store(Request $request)
    {
        // 🔹 1. Validación
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'categoria_slug' => 'required|exists:categorias,slug',
            'ubicacion_direccion' => 'nullable|string|max:255',
            'ubicacion_lat' => 'nullable|numeric|between:-90,90',
            'ubicacion_lng' => 'nullable|numeric|between:-180,180',
            'imagenes' => 'nullable|array|max:3',
            'imagenes.*' => 'image|max:5120', // 5MB
        ]);

        DB::beginTransaction();

        try {
            // 🔹 2. Obtener categoría
            $categoria = Categoria::where('slug', $validated['categoria_slug'])->first();

            // 🔹 3. Crear denuncia
            $denuncia = Denuncia::create([
                'codigo_seguimiento' => strtoupper(Str::random(10)),
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'],
                'categoria_id' => $categoria->id,
                'estado_id' => 1,
                'ubicacion_direccion' => $validated['ubicacion_direccion'] ?? null,
                'ubicacion_lat' => $validated['ubicacion_lat'] ?? null,
                'ubicacion_lng' => $validated['ubicacion_lng'] ?? null,
            ]);

            // 🔹 4. Guardar imágenes (si hay)
            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $file) {
                    $path = $file->store('denuncias', 'public');

                    EvidenciaDenuncia::create([
                        'denuncia_id' => $denuncia->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            // 🔹 5. Respuesta correcta
            return response()->json([
                'message' => 'Denuncia creada correctamente',
                'codigo_seguimiento' => $denuncia->codigo_seguimiento
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear la denuncia',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function showByCode($codigo)
    {
        // 1. Buscar la denuncia por el código único de seguimiento
        $denuncia = Denuncia::with(['categoria', 'estado'])
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