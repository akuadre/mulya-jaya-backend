<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Check jika user terautentikasi
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Silakan login kembali.'
                ], 401);
            }

            $query = AuditLog::query()->latest();

            // Filter berdasarkan pencarian
            if ($request->has('search') && $request->search != '') {
                $query->where(function($q) use ($request) {
                    $q->where('user_name', 'like', "%{$request->search}%")
                      ->orWhere('description', 'like', "%{$request->search}%")
                      ->orWhere('module', 'like', "%{$request->search}%");
                });
            }

            // Filter berdasarkan aksi
            if ($request->has('action') && $request->action != 'all') {
                $query->where('action', $request->action);
            }

            // Filter berdasarkan modul
            if ($request->has('module') && $request->module != 'all') {
                $query->where('module', $request->module);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $auditLogs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $auditLogs,
                'message' => 'Data audit log berhasil diambil'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching audit logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Display the specified audit log.
     */
    public function show($id): JsonResponse
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 401);
            }

            $auditLog = AuditLog::find($id);

            if (!$auditLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Audit log tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $auditLog,
                'message' => 'Detail audit log berhasil diambil'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching audit log: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Get statistics for audit logs
     */
    public function statistics(): JsonResponse
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 401);
            }

            $totalLogs = AuditLog::count();
            $todayLogs = AuditLog::whereDate('created_at', today())->count();
            $uniqueUsers = AuditLog::distinct('user_id')->count('user_id');
            
            $topActions = AuditLog::select('action')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(5)
                ->get();
                
            $topModules = AuditLog::select('module')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('module')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_logs' => $totalLogs,
                    'today_logs' => $todayLogs,
                    'unique_users' => $uniqueUsers,
                    'top_actions' => $topActions,
                    'top_modules' => $topModules,
                ],
                'message' => 'Statistik audit log berhasil diambil'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching audit log statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik audit log'
            ], 500);
        }
    }

    /**
     * Export audit logs to CSV
     */
    public function export(Request $request): JsonResponse
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 401);
            }

            $query = AuditLog::query()->latest();

            // Apply filters
            if ($request->has('search') && $request->search != '') {
                $query->where(function($q) use ($request) {
                    $q->where('user_name', 'like', "%{$request->search}%")
                      ->orWhere('description', 'like', "%{$request->search}%")
                      ->orWhere('module', 'like', "%{$request->search}%");
                });
            }
            if ($request->has('action') && $request->action != 'all') {
                $query->where('action', $request->action);
            }
            if ($request->has('module') && $request->module != 'all') {
                $query->where('module', $request->module);
            }

            $auditLogs = $query->get();

            // Format data untuk CSV
            $csvData = $auditLogs->map(function ($log) {
                return [
                    'user' => $log->user_name,
                    'action' => $this->getActionText($log->action),
                    'module' => $this->getModuleText($log->module),
                    'description' => $log->description,
                    'timestamp' => $log->created_at->format('Y-m-d H:i:s'),
                    'ip_address' => $log->ip_address,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $csvData,
                'message' => 'Data export berhasil diambil'
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting audit logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal export data'
            ], 500);
        }
    }

    /**
     * Helper method untuk translate action
     */
    private function getActionText($action): string
    {
        $actions = [
            'create' => 'Tambah',
            'update' => 'Edit',
            'delete' => 'Hapus',
            'view' => 'Lihat',
            'login' => 'Login',
            'logout' => 'Logout',
        ];

        return $actions[$action] ?? $action;
    }

    /**
     * Helper method untuk translate module
     */
    private function getModuleText($module): string
    {
        $modules = [
            'products' => 'Produk',
            'orders' => 'Pesanan',
            'users' => 'Pengguna',
            'reports' => 'Laporan',
            'auth' => 'Autentikasi',
        ];

        return $modules[$module] ?? $module;
    }
}