<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// ---------------------------
// Auth / User
// ---------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/profile/{id}', [AuthController::class, 'profile']);
Route::put('/profile/{id}', [AuthController::class, 'updateProfile']);

// ---------------------------
// Produk
// ---------------------------
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

// ---------------------------
// Orders / Pesanan
// ---------------------------
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders/recent', [OrderController::class, 'recent']);
Route::get('/orders/stats', [OrderController::class, 'stats']);

Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

// user only
Route::post('/orders', [OrderController::class, 'store']);


// ---------------------------
// User Management (Admin)
// ---------------------------
// Route::get('/users', [UserController::class, 'index']);
// Route::get('/users/{id}', [UserController::class, 'show']);

// ---------------------------
// Reports / Laporan
// ---------------------------
// Route::get('/reports/total_income', [ReportController::class, 'totalIncome']);
// Route::get('/reports/stock', [ReportController::class, 'stock']);

// ---------------------------
// Tambahan
// ---------------------------
// Route::post('/payment/confirm', [OrderController::class, 'confirmPayment']);
// Route::get('/lenses', [ProductController::class, 'lenses']); // custom lenses


