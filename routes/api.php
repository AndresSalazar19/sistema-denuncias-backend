<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DenunciaController;

Route::post('/denuncias', [DenunciaController::class, 'store']);