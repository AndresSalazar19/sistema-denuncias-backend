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
}