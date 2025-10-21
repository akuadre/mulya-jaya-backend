<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Create audit log
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        $userId = null,
        $userName = null,
        $recordId = null,
        $oldValues = null,
        $newValues = null
    ): void {
        try {
            $request = request();

            // Handle user data dengan aman
            $currentUser = Auth::user();
            $logUserId = $userId ?? ($currentUser ? $currentUser->id : null);
            $logUserName = $userName ?? ($currentUser ? $currentUser->name : 'System');

            AuditLog::create([
                'user_id' => $logUserId,
                'user_name' => $logUserName,
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'record_id' => $recordId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create audit log: ' . $e->getMessage());
        }
    }

    /**
     * Log user login
     */
    public static function logLogin($userId, $userName): void
    {
        self::log('login', 'auth', "User {$userName} melakukan login", $userId, $userName);
    }

    /**
     * Log user logout
     */
    public static function logLogout($userId, $userName): void
    {
        self::log('logout', 'auth', "User {$userName} melakukan logout", $userId, $userName);
    }

    /**
     * Log product actions
     */
    public static function logProductAction($action, $productId, $productName, $oldValues = null, $newValues = null): void
    {
        $actions = [
            'create' => "Menambahkan produk baru: {$productName}",
            'update' => "Memperbarui produk: {$productName}",
            'delete' => "Menghapus produk: {$productName}",
        ];

        self::log(
            $action,
            'products',
            $actions[$action] ?? "Melakukan aksi {$action} pada produk: {$productName}",
            null, null, $productId, $oldValues, $newValues
        );
    }

    /**
     * Log order actions
     */
    public static function logOrderAction($action, $orderId, $orderNumber, $oldValues = null, $newValues = null): void
    {
        $actions = [
            'create' => "Membuat pesanan baru: {$orderNumber}",
            'update' => "Memperbarui pesanan: {$orderNumber}",
            'delete' => "Menghapus pesanan: {$orderNumber}",
        ];

        self::log(
            $action,
            'orders',
            $actions[$action] ?? "Melakukan aksi {$action} pada pesanan: {$orderNumber}",
            null, null, $orderId, $oldValues, $newValues
        );
    }

    /**
     * Log user management actions
     */
    public static function logUserAction($action, $targetUserId, $targetUserName, $oldValues = null, $newValues = null): void
    {
        $actions = [
            'create' => "Menambahkan user baru: {$targetUserName}",
            'update' => "Memperbarui user: {$targetUserName}",
            'delete' => "Menghapus user: {$targetUserName}",
        ];

        self::log(
            $action,
            'users',
            $actions[$action] ?? "Melakukan aksi {$action} pada user: {$targetUserName}",
            null, null, $targetUserId, $oldValues, $newValues
        );
    }

    /**
     * Log report actions
     */
    public static function logReportAction($action, $reportName): void
    {
        $actions = [
            'view' => "Melihat laporan: {$reportName}",
            'export' => "Mengekspor laporan: {$reportName}",
            'generate' => "Membuat laporan: {$reportName}",
        ];

        self::log(
            $action,
            'reports',
            $actions[$action] ?? "Melakukan aksi {$action} pada laporan: {$reportName}"
        );
    }
}