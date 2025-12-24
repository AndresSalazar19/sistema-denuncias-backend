<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DenunciaController;

Route::post('/denuncias', [DenunciaController::class, 'store']);
Route::put('/cambiar-estado/{id}', [DenunciaController::class, 'updateStatus']);
Route::get('/denuncias/consultar/{codigo}', [DenunciaController::class, 'showByCode']);
Route::get('/denuncias/buscar', [DenunciaController::class, 'search']);
Route::get('/estadisticas-publicas', [DenunciaController::class, 'publicStats']);