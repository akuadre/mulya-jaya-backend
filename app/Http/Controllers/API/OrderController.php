<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Lense;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // POST /orders → bikin order baru
    public function store(Request $request)
    {
        // --- 1. VALIDASI DATA GABUNGAN (ORDER + LENSE) ---
        $validator = Validator::make($request->all(), [
            // Validasi untuk Order
            'user_id'     => 'required|exists:users,id',
            'product_id'  => 'required|exists:products,id',
            'total_price' => 'required|numeric|min:0',

            // Validasi untuk Lense (datanya dikirim dalam objek 'lense_data')
            'lense_data'                => 'sometimes|array', // 'lense_data' boleh ada atau tidak
            'lense_data.lense_type'     => 'required_with:lense_data|string',
            'lense_data.is_custom'      => 'required_with:lense_data|boolean',

            // Validasi kondisional: jika is_custom = true, maka field resep wajib diisi
            'lense_data.left_sph'       => 'required_if:lense_data.is_custom,true|nullable|numeric',
            'lense_data.left_cyl'       => 'required_if:lense_data.is_custom,true|nullable|numeric',
            'lense_data.left_axis'      => 'required_if:lense_data.is_custom,true|nullable|numeric',
            'lense_data.right_sph'      => 'required_if:lense_data.is_custom,true|nullable|numeric',
            'lense_data.right_cyl'      => 'required_if:lense_data.is_custom,true|nullable|numeric',
            'lense_data.right_axis'     => 'required_if:lense_data.is_custom,true|nullable|numeric',
            'lense_data.pd'             => 'required_if:lense_data.is_custom,true|nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $order = null;

        // --- 2. GUNAKAN DATABASE TRANSACTION ---
        // Ini untuk memastikan data Order dan Lense sama-sama berhasil dibuat, atau keduanya gagal.
        try {
            DB::beginTransaction();

            // Ambil data user untuk alamat
            $user = User::findOrFail($validatedData['user_id']);

            // Buat Order terlebih dahulu
            $order = Order::create([
                'user_id'     => $validatedData['user_id'],
                'product_id'  => $validatedData['product_id'],
                'address'     => $user->address ?? 'Alamat tidak diatur',
                'order_date'  => now(),
                'total_price' => $validatedData['total_price'],
                'status'      => 'pending',
            ]);

            // Ambil produk
            $product = Product::findOrFail($validatedData['product_id']);

            // Pastikan quantity ada (kalau kamu kirim dari frontend)
            $quantity = $request->input('quantity', 1); // default 1

            // Cek stok cukup
            if ($product->stock < $quantity) {
                throw new \Exception('Stok produk tidak mencukupi');
            }

            // Kurangi stok
            $product->stock -= $quantity;
            $product->save();

            DB::commit(); // Jika semua berhasil, simpan perubahan ke database
        } catch (\Exception $e) {
            DB::rollBack(); // Jika ada error, batalkan semua query
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan.',
                'error'   => $e->getMessage()
            ], 500);
        }

        // --- 3. KEMBALIKAN RESPONSE DENGAN DATA LENGKAP ---
        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibuat',
            // Load relasi user, product, dan lense yang baru
            'data'    => $order->load('user', 'product', 'lense'),
        ], 201);
    }

    // GET /orders → list semua order
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        $orders = Order::with('product', 'user')
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();

        return response()->json([
            'success' => true,
            'message' => $orders->isEmpty() ? 'Belum ada pesanan' : 'Daftar pesanan berhasil diambil',
            'data'    => $orders,
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
            'data'    => $order,
        ], 200);
    }

    // PUT /orders/{id}/status → update status (admin)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,sending,completed,cancelled'
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
            'data'    => $order->load('user', 'product'),
        ], 200);
    }

    // GET /orders/recent → order terbaru (dashboard)
    public function recent()
    {
        $orders = Order::with('user', 'product')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Order terbaru',
            'data'    => $orders,
        ], 200);
    }

    // GET /orders/stats → total pending, processing, completed, cancelled
    public function stats()
    {
        $stats = [
            'pending'    => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'sending'    => Order::where('status', 'sending')->count(),
            'completed'  => Order::where('status', 'completed')->count(),
            'cancelled'  => Order::where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Stats order',
            'data'    => $stats,
        ], 200);
    }

    // GET /orders/sales-monthly → total penjualan bulanan tahun ini
    public function salesMonthly()
    {
        $currentYear = Carbon::now()->year;

        $salesData = Order::select(
            // DB::raw('MONTH(order_date) as month'),
                DB::raw('MONTH(updated_at) as month'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->where('status', 'completed')
            // ->whereYear('order_date', $currentYear)
            ->whereYear('updated_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_sales', 'month')
            ->toArray();

        $monthlySales = array_fill(1, 12, 0);
        foreach ($salesData as $month => $sales) {
            // Memastikan total penjualan sebagai integer
            $monthlySales[$month] = (int) $sales;
        }

        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan bulanan berhasil diambil',
            'labels'  => $monthLabels,
            'data'    => array_values($monthlySales),
        ]);
    }

    // GET /orders/sales-annual → Total Penjualan per Tahun (menggantikan countAnnual)
    public function salesAnnual()
    {
        // Tetapkan tahun mulai ke 2024
        $startYear = 2024;

        $annualSales = Order::select(
                // DB::raw('YEAR(order_date) as year'),
                DB::raw('YEAR(updated_at) as year'),

                // Mengambil total harga (Rupiah)
                DB::raw('SUM(total_price) as total_sales')
            )
            ->where('status', 'completed')
            // Filter hanya data dari tahun 2024 dan seterusnya
            // ->whereYear('order_date', '>=', $startYear)
            ->whereYear('updated_at', '>=', $startYear)
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $labels = $annualSales->pluck('year')->map(fn($y) => (string) $y)->toArray();

        // Memastikan total penjualan sebagai integer (bilangan bulat)
        $data   = $annualSales->pluck('total_sales')->map(fn($s) => (int) $s)->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Data total penjualan tahunan berhasil diambil',
            'labels'  => $labels,
            'data'    => $data,
        ]);
    }

    // GET /orders/sales-daily → total penjualan harian (7 hari terakhir)
    public function salesDaily()
    {
        $endDate   = Carbon::now();
        $startDate = Carbon::now()->subDays(6);

        // Inisialisasi semua tanggal dengan 0
        $dailySales = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dailySales[$date] = 0;
        }

        // Ambil data penjualan dari DB
        $salesData = Order::select(
                // DB::raw('DATE(order_date) as date'),
                DB::raw('DATE(updated_at) as date'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->where('status', 'completed')
            // ->whereBetween('order_date', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->whereBetween('updated_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total_sales', 'date')
            ->toArray();

        // Gabungkan hasil DB dengan array default
        foreach ($salesData as $date => $sales) {
            // Memastikan total penjualan sebagai integer
            $dailySales[$date] = (int) $sales;
        }

        // Format label DD/MM
        $labels = array_map(fn($date) => Carbon::parse($date)->format('d/m'), array_keys($dailySales));

        return response()->json([
            'success' => true,
            'message' => 'Data penjualan harian (7 hari terakhir) berhasil diambil',
            'labels'  => $labels,
            'data'    => array_values($dailySales),
        ]);
    }

    // --- METHOD BARU UNTUK DASHBOARD SUMMARY ---
    public function getDashboardSummary()
    {
        // Menghitung total pendapatan dari order yang statusnya 'completed'
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');

        $stats = [
            'totalRevenue' => (int) $totalRevenue, // Tambahkan totalRevenue di sini
            'pending'      => Order::where('status', 'pending')->count(),
            'processing'   => Order::where('status', 'processing')->count(),
            'sending'      => Order::where('status', 'sending')->count(),
            'completed'    => Order::where('status', 'completed')->count(),
            'cancelled'    => Order::where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard summary stats',
            'data'    => $stats,
        ], 200);
    }

    // --- METHOD BARU UNTUK DASHBOARD SALES ---
    public function getDashboardSales()
    {
        // Kita panggil saja method yang sudah ada untuk merangkum data
        $dailyResponse = $this->salesDaily()->getData(true);
        $monthlyResponse = $this->salesMonthly()->getData(true);
        $annualResponse = $this->salesAnnual()->getData(true);

        $salesData = [
            'daily'   => [
                'labels' => $dailyResponse['labels'],
                'data'   => $dailyResponse['data'],
            ],
            'monthly' => [
                'labels' => $monthlyResponse['labels'],
                'data'   => $monthlyResponse['data'],
            ],
            'annual'  => [
                'labels' => $annualResponse['labels'],
                'data'   => $annualResponse['data'],
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard sales data',
            'data'    => $salesData,
        ], 200);
    }

}
