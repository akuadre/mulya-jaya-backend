<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    // Login admin
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            // throw ValidationException::withMessages([
            //     'email' => ['The provided credentials are incorrect.'],
            // ]);

            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah!'
            ], 401);
        }

        // Hapus token lama (opsional)
        // $admin->tokens()->delete();

        $token = $admin->createToken('adminToken')->plainTextToken;

        return response()->json([
            'admin' => $admin,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    // Logout admin
    public function logout(Request $request)
    {
        // Cek apakah ada token aktif
        $token = $request->user()?->currentAccessToken();

        if (!$token) {
            return response()->json([
                'message' => 'No active token found or already logged out.'
            ], 401);
        }

        // Hapus token
        $token->delete();


        return response()->json([
            'message' => 'Admin logged out successfully.'
        ]);
    }
}
