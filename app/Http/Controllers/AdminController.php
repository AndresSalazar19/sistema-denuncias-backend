<?php

namespace App\Http\Controllers;

use App\Models\Denuncia;
use App\Models\NotasInternas;
use App\Models\Responsable;
use App\Models\EstadoDenuncia;
use App\Models\HistorialEstadoDenuncia;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function updateStatus(Request $request, $codigo_seguimiento)
    {
        // 1. Validar que el estado exista
        $request->validate([
            'estado' => 'required|string|exists:estado_denuncias,name',
            'admin_id' => 'nullable|exists:admins,id'
        ]);

        $estadoId = EstadoDenuncia::where('name', $request->estado)->value('id');

        if (!$estadoId) {
            return response()->json([
                'message' => 'Estado no válido'
            ], 400);
        }

        // 2. Buscar la denuncia
        $denuncia = Denuncia::where('codigo_seguimiento', $codigo_seguimiento)->first();

        if (!$denuncia) {
            return response()->json(['message' => 'Denuncia no encontrada'], 404);
        }

        // 3. Actualizar el estado
        $denuncia->estado_id = $estadoId;
        $denuncia->save();

        // 4. Registrar en el historial de estados
        HistorialEstadoDenuncia::create([
            'denuncia_id' => $denuncia->id,
            'estado_nuevo_id' => $estadoId,
            'admin_id' => $request->admin_id,
            'created_at' => now()
        ]);

        return response()->json([
            'message' => 'Estado actualizado correctamente',
        ]);
    }

    public function getHistorialEstados($codigo_seguimiento)
    {
        $denuncia = Denuncia::where('codigo_seguimiento', $codigo_seguimiento)->first();

        if (!$denuncia) {
            return response()->json(['message' => 'Denuncia no encontrada'], 404);
        }

        $historial = HistorialEstadoDenuncia::with(['estadoNuevo', 'admin'])
            ->where('denuncia_id', $denuncia->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'denuncia' => [
                'id' => $denuncia->id,
                'codigo_seguimiento' => $denuncia->codigo_seguimiento
            ],
            'historial_estados' => $historial
        ]);
    }
    public function asignResponsable(Request $request, $codigo_seguimiento)
    {
        // 1. Validar que el responsable exista
        $request->validate([
            'responsable' => 'required|exists:responsables,id'
        ]);

        // Buscar responsable
        $responsableId = $request->responsable;

        if (!$responsableId) {
            return response()->json([
                'message' => 'Responsable no válido'
            ], 400);
        }

        // 2. Buscar la denuncia
        $denuncia = Denuncia::where('codigo_seguimiento', $codigo_seguimiento)->first();

        if (!$denuncia) {
            return response()->json(['message' => 'Denuncia no encontrada'], 404);
        }

        // 3. Asignar el responsable
        $denuncia->responsable_id = $responsableId;
        $denuncia->save();

        return response()->json([
            'message' => 'Responsable asignado correctamente',
            'denuncia' => [
                'id' => $denuncia->id,
                'codigo_seguimiento' => $denuncia->codigo_seguimiento,
                'responsable_id' => $denuncia->responsable_id
            ]
        ]);
    }

    public function addNotas(Request $request, $codigo_seguimiento)
    {
        $request->validate([
            'notas' => 'required|string|max:1000',
            'admin_id' => 'nullable|exists:admins,id'
        ]);

        $denuncia = Denuncia::where('codigo_seguimiento', $codigo_seguimiento)->first();

        if (!$denuncia) {
            return response()->json(['message' => 'Denuncia no encontrada'], 404);
        }

        $notaInterna = NotasInternas::create([
            'denuncia_id' => $denuncia->id,
            'nota' => $request->notas,
            'admin_id' => $request->admin_id,
            'created_at' => now()
        ]);
        
        return response()->json([
            'message' => 'Nota interna agregada correctamente',
            'nota' => $notaInterna
        ]);
    }

    public function getNotasInternas($codigo_seguimiento)
    {
        $denuncia = Denuncia::where('codigo_seguimiento', $codigo_seguimiento)->first();

        if (!$denuncia) {
            return response()->json(['message' => 'Denuncia no encontrada'], 404);
        }

        $notas = NotasInternas::with('admin')
            ->where('denuncia_id', $denuncia->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'denuncia' => [
                'id' => $denuncia->id,
                'codigo_seguimiento' => $denuncia->codigo_seguimiento
            ],
            'notas_internas' => $notas
        ]);
    }
}
?>