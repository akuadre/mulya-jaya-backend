<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProductController::class, 'index']);   // list
Route::get('/products/{id}', [ProductController::class, 'show']); // detail
Route::post('/products', [ProductController::class, 'store']);  // tambah
Route::put('/products/{id}', [ProductController::class, 'update']); // update
Route::delete('/products/{id}', [ProductController::class, 'destroy']); // hapus


