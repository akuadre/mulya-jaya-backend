<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // POST /orders → bikin order baru
    public function store(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'total_price'=> 'required|numeric|min:0',
        ]);

        // ambil user
        $user = User::find($request->user_id);

        $order = Order::create([
            'user_id'     => $request->user_id,
            'product_id'  => $request->product_id,
            'address'     => $user->address, // otomatis dari user
            'order_date'  => now(),           // otomatis pakai current datetime
            'total_price' => $request->total_price,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibuat',
            'data'    => $order
        ], 201);
    }

    // GET /orders → list semua order
    public function index(Request $request)
    {
        $userId = $request->query('user_id'); // ambil query param user_id

        if ($userId) {
            // kalau ada user_id, tampilkan hanya pesanan user itu
            $orders = Order::where('user_id', $userId)->with('product', 'user')->get();
        } else {
            // kalau admin, tampilkan semua pesanan
            $orders = Order::with('product', 'user')->get();
        }

        return response()->json([
            'success' => true,
            'message' => $orders->isEmpty() ? 'Belum ada pesanan' : 'Daftar pesanan berhasil diambil',
            'data' => $orders,
        ], 200);
    }


    // GET /orders/{id} → detail order
    public function show($id)
    {
        $order = Order::with('user', 'product')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail order',
            'data'    => $order
        ], 200);
    }

    // PUT /orders/{id}/status → update status (admin)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order = Order::find($id);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Status order berhasil diperbarui',
            'data'    => $order
        ], 200);
    }

    // GET /orders/recent → order terbaru (dashboard)
    public function recent()
    {
        $orders = Order::with('user', 'product')->latest()->take(5)->get();

        return response()->json([
            'success' => true,
            'message' => 'Order terbaru',
            'data'    => $orders
        ], 200);
    }

    // GET /orders/stats → total pending, processing, completed (dashboard)
    public function stats()
    {
        $stats = [
            'pending'    => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed'  => Order::where('status', 'completed')->count(),
            'cancelled'  => Order::where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Stats order',
            'data'    => $stats
        ], 200);
    }
}
