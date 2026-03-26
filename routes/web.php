<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;


Route::get('/', [UploadController::class, 'index']);
Route::get('/dashboard-data', [UploadController::class, 'dashboardData']);
Route::get('/collection-products/export', [UploadController::class, 'exportCollectionProducts']);
Route::post('/collection-products/remove', [UploadController::class, 'removeCollectionProduct']);
Route::post('/upload', [UploadController::class, 'store']);
Route::post('/upload/{upload}/remove-from-collection', [UploadController::class, 'removeFromCollection']);
Route::get('/admin', function () {
    abort(404);
});
