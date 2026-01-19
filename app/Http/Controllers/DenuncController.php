<?php

namespace App\Http\Controllers;

use App\Models\Denuncia;
use App\Models\Categoria;
use App\Models\EstadoDenuncia;
use App\Models\HistorialEstadoDenuncia;
use App\Models\EvidenciaDenuncia; 
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; 
class DenuncController extends Controller
{
    public function store(Request $request)
    {

        if ($request->has('categoria')) {
            $cat = Categoria::where('slug', $request->categoria)->first();
            if ($cat) {
                $request->merge(['categoria_id' => $cat->id]); // Inyectamos el ID correcto
            }
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'required|string',
            'categoria_id' => 'required|exists:categorias,id', // Ahora esto pasará
            'ubicacion_lat' => 'required', // React lo envía como string o number
            'ubicacion_lng' => 'required',
            // Validar que sean imágenes reales (máx 5MB)
            'imagenes.*' => 'nullable|image|max:5120' 
        ]);

        // Iniciar una transacción de base de datos (por si algo falla, no guardar nada)
        return DB::transaction(function () use ($validated, $request) {
            
            // Generar código
            $codigo = $this->generarCodigoUnico();
            
            // Estado inicial (Nueva)
            $estadoInicial = EstadoDenuncia::where('slug', 'nueva')->first();
            if (!$estadoInicial) {
                 // Fallback por si la DB está vacía
                 $estadoInicial = EstadoDenuncia::firstOrCreate(['slug' => 'nueva'], ['name' => 'Nueva', 'color' => '#3b82f6']);
            }

            // Crear la Denuncia
            $denuncia = Denuncia::create([
                'codigo_seguimiento' => $codigo,
                'titulo' => $validated['titulo'],
                'descripcion' => $validated['descripcion'],
                'categoria_id' => $validated['categoria_id'],
                'estado_id' => $estadoInicial->id,
                'ubicacion_lat' => $validated['ubicacion_lat'],
                'ubicacion_lng' => $validated['ubicacion_lng'],
                // Asumimos que la dirección no la envía el frontend aún, o es null
                'ubicacion_direccion' => $request->input('ubicacion_direccion', 'Ubicación en mapa'),
            ]);

            // Registrar historial
            HistorialEstadoDenuncia::create([
                'denuncia_id' => $denuncia->id,
                'estado_nuevo_id' => $estadoInicial->id,
                'created_at' => now()
            ]);

            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $archivo) {
                    
                    // Subir al Bucket configurado en .env
                    $path = $archivo->store('evidencias', 'gcs');
                    //Obtener URL Pública
                    $url = Storage::disk('gcs')->url($path);

                    // Guardar en tabla 'evidencia_denuncias'
                    if (class_exists(EvidenciaDenuncia::class)) {
                        EvidenciaDenuncia::create([
                            'denuncia_id' => $denuncia->id,
                            'file_path'   => $url, 
                            'file_name'   => $archivo->getClientOriginalName(), 
                            'file_size'   => $archivo->getSize(),
                        ]);
                    } else {
                        DB::table('evidencia_denuncias')->insert([
                            'denuncia_id' => $denuncia->id,
                            'url_archivo' => $url,
                            'tipo_archivo' => $archivo->getClientOriginalExtension(),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Denuncia registrada con éxito',
                'codigo' => $denuncia->codigo_seguimiento,
                // Cargamos la relación de evidencias para que el frontend vea que se subieron
                'data' => $denuncia->load('categoria', 'estado') 
            ], 201);
        });
    }

    public function showByCode($codigo)
    {
        $denuncia = Denuncia::with(['categoria', 'estado', 'evidencias']) // Asegúrate que la relación 'evidencias' exista en el Modelo Denuncia
            ->where('codigo_seguimiento', $codigo)
            ->first();
            
        if (!$denuncia) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->json([
            'codigo_seguimiento' => $denuncia->codigo_seguimiento,
            'estado' => $denuncia->estado->name,
            'estado_color' => $denuncia->estado->color,
            'categoria' => $denuncia->categoria->name,
            'fecha_registro' => $denuncia->created_at->format('d/m/Y - H:i'),
            'evidencias' => $denuncia->evidencias->map(function($evidencia) {
                return [
                    'url' => $evidencia->url_archivo // Aquí usamos directo la URL de Google
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