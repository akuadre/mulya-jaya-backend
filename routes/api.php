<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminAuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DatabaseBackupController;

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
Route::get('/products/admin', [ProductController::class, 'indexAdmin']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// ---------------------------
// User Orders
// ---------------------------
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders/user/{userId}', [OrderController::class, 'getByUserId']);

// ---------------------------
// Admin Authentication
// ---------------------------
Route::post('/admin/login', [AdminAuthController::class, 'login']);

// ---------------------------
// Admin Routes (Protected)
// ---------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Admin Products Management
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // --- ROUTE BARU UNTUK DASHBOARD ---
    Route::get('/dashboard-summary', [OrderController::class, 'getDashboardSummary']);
    Route::get('/dashboard-sales', [OrderController::class, 'getDashboardSales']);

    // Penjualan Bulanan (Total Harga Transaksi Selesai per Bulan)
    Route::get('/orders-sales-monthly', [OrderController::class, 'salesMonthly']);

    // Jumlah Order Tahunan (Total Count Order per Tahun)
    Route::get('/orders-count-annual', [OrderController::class, 'countAnnual']);

    // [PENAMBAHAN] Penjualan Harian (Total Harga Transaksi Selesai per Hari)
    Route::get('/orders-sales-daily', [OrderController::class, 'salesDaily']);

    // --- ROUTE BARU UNTUK HALAMAN LAPORAN ---
    Route::get('/reports', [ReportController::class, 'generateReport']);

    // routes/api.php (Contoh)
    Route::get('/orders-sales-annual', [OrderController::class, 'salesAnnual']);

    // Admin Get Users
    Route::get('/users', [UserController::class, 'index']);

    // Admin Get Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    Route::get('/orders-recent', [OrderController::class, 'recent']);
    Route::get('/orders-stats', [OrderController::class, 'stats']);

    // Admin Logout
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);

    Route::get('/audit-logs', [AuditLogController::class, 'index']);
    Route::get('/audit-logs/statistics', [AuditLogController::class, 'statistics']);
    Route::get('/audit-logs/export', [AuditLogController::class, 'export']);
    Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);


    // ✅ ROUTE BACKUP UNTUK FRONTEND (YANG BARU DITAMBAHKAN)
    Route::post('/backup-database', function (Request $request) {
        try {
            // Dapatkan kredensial database dari config
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');

            // Generate nama file - SESUAIKAN dengan frontend
            $filename = 'mulyajaya_backup_' . date('Y-m-d_H-i-s') . '.sql';

            $sqlContent = "-- Database Backup for Mulya Jaya\n";
            $sqlContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

            // List semua tabel yang ada di database Anda
            $tables = ['users', 'admins', 'products', 'orders', 'lenses', 'migrations', 'personal_access_tokens'];

            foreach ($tables as $table) {
                // Skip jika tabel tidak ada
                if (!Schema::hasTable($table)) {
                    continue;
                }

                // Dapatkan struktur tabel
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`")[0];
                $sqlContent .= "--\n-- Table structure for table `{$table}`\n--\n";
                $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sqlContent .= $createTable->{'Create Table'} . ";\n\n";

                // Dapatkan data tabel
                $data = DB::table($table)->get();

                if ($data->count() > 0) {
                    $sqlContent .= "--\n-- Dumping data for table `{$table}`\n--\n";

                    foreach ($data as $row) {
                        $columns = [];
                        $values = [];

                        foreach ((array)$row as $column => $value) {
                            $columns[] = "`{$column}`";
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . addslashes($value) . "'";
                            }
                        }

                        $sqlContent .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sqlContent .= "\n";
                }
            }

            // Return sebagai file download
            return response($sqlContent)
                ->header('Content-Type', 'application/sql')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    });

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
