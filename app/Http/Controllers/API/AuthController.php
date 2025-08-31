<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/register
    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email',
            'password'      => 'required|string|min:8|confirmed',
            'phone_number'  => 'required|string|max:20',
            'address'       => 'required|string',
        ]);

        // check email sudah ada
        $existingEmail = User::where('email', $request->email)->first();
        if ($existingEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah terdaftar'
            ], 409);
        }

        // check phone number sudah ada
        $existingPhone = User::where('phone_number', $request->phone_number)->first();
        if ($existingPhone) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor telepon sudah terdaftar'
            ], 409);
        }

        User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'phone_number'  => $request->phone_number,
            'address'       => $request->address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
        ], 201);
    }

    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data'    => $user
        ], 200);
    }

    // POST /api/logout (opsional buat Android, bisa kosong)
    public function logout(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    // GET /api/profile
    public function profile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $user
        ]);
    }

    // PUT /api/profile
    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $user->update($request->only(['name','email','phone_number','address']));

        return response()->json([
            'success' => true,
            'message' => 'Profile diperbarui',
            'data'    => $user
        ]);
    }
}
