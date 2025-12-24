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
}