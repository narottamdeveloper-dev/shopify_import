<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;


Route::get('/', [UploadController::class, 'index']);
Route::get('/dashboard-data', [UploadController::class, 'dashboardData']);
Route::post('/upload', [UploadController::class, 'store']);
