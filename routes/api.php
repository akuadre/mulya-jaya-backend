<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminAuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// ---------------------------
// User Authentication
// ---------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/profile/{id}', [AuthController::class, 'profile']);
Route::put('/profile/{id}', [AuthController::class, 'updateProfile']);

// ---------------------------
// User Products
// ---------------------------
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// ---------------------------
// User Orders
// ---------------------------
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);

// ---------------------------
// Admin Authentication
// ---------------------------
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// ---------------------------
// Admin Products Management
// ---------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // --- ROUTE BARU UNTUK DASHBOARD ---
    Route::get('/dashboard-summary', [OrderController::class, 'getDashboardSummary']);
    Route::get('/dashboard-sales', [OrderController::class, 'getDashboardSales']);

     // Penjualan Bulanan (Total Harga Transaksi Selesai per Bulan)
    Route::get('orders-sales-monthly', [OrderController::class, 'salesMonthly']);

    // Jumlah Order Tahunan (Total Count Order per Tahun)
    Route::get('orders-count-annual', [OrderController::class, 'countAnnual']);

    // [PENAMBAHAN] Penjualan Harian (Total Harga Transaksi Selesai per Hari)
    Route::get('orders-sales-daily', [OrderController::class, 'salesDaily']);

    // routes/api.php (Contoh)
    Route::get('orders-sales-annual', [OrderController::class, 'salesAnnual']);


    // Admin Get Users
    Route::get('/users', [UserController::class, 'index']);

    // Admin Get Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    Route::get('/orders-recent', [OrderController::class, 'recent']);
    Route::get('/orders-stats', [OrderController::class, 'stats']);

    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
});

// ---------------------------
// Another Route (if needed)
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


