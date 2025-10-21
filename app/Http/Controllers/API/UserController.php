<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\AuditLogService;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Mengambil semua data pengguna dari database.
            $users = User::withCount('orders')->get();

            // ✅ LOG AUDIT - Hanya di sini ketika melihat daftar user (1x saja)
            AuditLogService::log('view', 'users', 'Melihat daftar semua pengguna');

            // Mengembalikan respons JSON yang berisi data pengguna.
            return response()->json([
                'success' => true,
                'message' => 'Data pengguna berhasil diambil.',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data pengguna: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'role' => 'required|string|in:admin,user,staff',
                'address' => 'nullable|string',
                'phone' => 'nullable|string',
            ]);

            $validated['password'] = bcrypt($validated['password']);
            $user = User::create($validated);

            // ✅ LOG AUDIT - User dibuat
            AuditLogService::logUserAction('create', $user->id, $user->name);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User berhasil dibuat'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $oldData = $user->toArray();

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:8',
                'role' => 'sometimes|string|in:admin,user,staff',
                'address' => 'nullable|string',
                'phone' => 'nullable|string',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            // ✅ LOG AUDIT - User diupdate
            AuditLogService::logUserAction(
                'update', 
                $user->id, 
                $user->name,
                $oldData,
                $user->toArray()
            );

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'User berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $userName = $user->name;
            $userId = $user->id;

            $user->delete();

            // ✅ LOG AUDIT - User dihapus
            AuditLogService::logUserAction('delete', $userId, $userName);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = User::withCount('orders')->find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // ❌ TIDAK LOG AUDIT di sini (tidak log ketika melihat detail user)

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Detail user berhasil diambil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail user: ' . $e->getMessage()
            ], 500);
        }
    }
}