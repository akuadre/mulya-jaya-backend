<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {

        // Mengambil semua data pengguna dari database.
        $users = User::withCount('orders')->get();

        // Mengembalikan respons JSON yang berisi data pengguna.
        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diambil.',
            'data' => $users
        ]);
    }
}
