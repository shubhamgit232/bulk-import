<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ChunkUploadController;

Route::post('/upload/chunk', [ChunkUploadController::class, 'uploadChunk']);
Route::post('/upload/complete', [ChunkUploadController::class, 'completeUpload']);


Route::get('/import', [ProductImportController::class, 'index']);
Route::post('/import', [ProductImportController::class, 'store']);

Route::get('/import-history', function () {
    $imports = \App\Models\Import::latest()->get();
    return view('import.history', compact('imports'));
});


Route::get('/upload-ui', function () {
    return view('upload.drag_drop');
});


Route::get('/', function () {
    return view('welcome');
});
