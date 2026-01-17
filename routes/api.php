<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DenuncController;
use App\Http\Controllers\UtilitariosController;
use Symfony\Component\Routing\Router;

Route::post('/denuncias', [DenuncController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/denuncias/consultar/{codigo}', [DenuncController::class, 'showByCode']); //revisar
Route::get('/denuncias/buscar', [DenuncController::class, 'search']); //revisar
Route::get('/getDenuncias', [UtilitariosController::class, 'getDenuncias']);
Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::post('/notas-internas/{codigo_seguimiento}', [AdminController::class, 'addNotas']);
    Route::get('/notas-internas/{codigo_seguimiento}', [AdminController::class, 'getNotasInternas']);
    Route::patch('/asignar-responsable/{codigo_seguimiento}', [AdminController::class, 'asignResponsable']);
    Route::get('/historial-estados/{codigo_seguimiento}', [AdminController::class, 'getHistorialEstados']);
    Route::put('/cambiar-estado/{codigo_seguimiento}', [AdminController::class, 'updateStatus']);
    Route::get('/estadisticas', [DashboardController::class, 'adminStats']);
    Route::get('/responsables', [UtilitariosController::class, 'getResponsables']);
});
