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
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // POST /orders → bikin order baru
    public function store(Request $request)
    {
        // --- 1. VALIDASI DATA (Order + Foto) ---
        $validator = Validator::make($request->all(), [
            'user_id'     => 'required|exists:users,id',
            'product_id'  => 'required|exists:products,id',
            'total_price' => 'required|numeric|min:0',
            'quantity'    => 'sometimes|integer|min:1',
            'payment_method' => 'sometimes|string|in:bca,mandiri',
            'photo'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',

            // Parameter lensa type sebagai enum
            'lensa_type' => 'required|in:without,normal,custom',
        ]);

        // Jika validasi gagal → langsung balikan error
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $validatedData = $validator->validated();
        $order = null;
        $photoUrl = null;

        // --- 2. TRANSAKSI DATABASE ---
        try {
            DB::beginTransaction();

            // Ambil data user untuk auto-fill alamat
            $user = User::findOrFail($validatedData['user_id']);

            // --- 3. SIMPAN FOTO KE PUBLIC FOLDER ---
            $photoPath = null;

            // DEBUG: Log file info
            Log::info('File received:', [
                'hasFile' => $request->hasFile('photo'),
                'file' => $request->file('photo') ? $request->file('photo')->getClientOriginalName() : 'No file'
            ]);

            // Validasi: Jika lensa_type = custom, harus ada photo
            if ($validatedData['lensa_type'] === 'custom' && !$request->hasFile('photo')) {
                throw new \Exception('Untuk lensa custom, wajib upload resep dokter');
            }

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');

                // Validasi tambahan untuk file
                if (!$file->isValid()) {
                    throw new \Exception('File tidak valid');
                }

                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

                // Pastikan folder exists
                $folderPath = public_path('images/doctorRecipes');
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }

                // Simpan ke public/images/doctorRecipes/
                $file->move($folderPath, $fileName);
                $photoPath = 'images/doctorRecipes/' . $fileName;
                $photoUrl = asset('images/doctorRecipes/' . $fileName);

                Log::info('File saved:', [
                    'path' => $photoPath,
                    'url' => $photoUrl,
                    'full_path' => $folderPath . '/' . $fileName
                ]);
            }

            // --- 4. CREATE ORDER ---
            $orderData = [
                'user_id'     => $validatedData['user_id'],
                'product_id'  => $validatedData['product_id'],
                'address'     => $user->address ?? 'Alamat tidak diatur',
                'order_date'  => now(),
                'total_price' => $validatedData['total_price'],
                'status'      => 'pending',
                'photo'       => $photoPath,
                'lensa_type'  => $validatedData['lensa_type'], // TAMBAH INI
            ];

            // Tambahkan payment_method jika ada
            if (isset($validatedData['payment_method'])) {
                $orderData['payment_method'] = $validatedData['payment_method'];
            }

            $order = Order::create($orderData);

            // --- 5. AMBIL & KURANGI STOK PRODUK ---
            $product = Product::findOrFail($validatedData['product_id']);
            $quantity = $request->input('quantity', 1);

            if ($product->stock < $quantity) {
                throw new \Exception('Stok produk tidak mencukupi. Stok tersedia: ' . $product->stock);
            }

            $product->stock -= $quantity;
            $product->save();

            // ✅ LOG AUDIT - Order dibuat
            AuditLogService::logOrderAction('create', $order->id, "#ORD-{$order->id}");

            DB::commit(); // aman → simpan database

            Log::info('Order created successfully:', [
                'order_id' => $order->id,
                'lensa_type' => $validatedData['lensa_type'],
                'photo_path' => $photoPath,
                'photo_url' => $photoUrl
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // error → rollback semua data

            Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan.',
                'error'   => $e->getMessage()
            ], 500);
        }

        // Reload order dengan relationships (HAPUS 'lense')
        $order->load('user', 'product');

        // --- 7. BALIKKAN RESPONSE KE ANDROID ---
        $response = [
            'success' => true,
            'message' => 'Order berhasil dibuat',
            'order_id' => $order->id,
            'data'    => $order,
        ];

        // Tambahkan photo_url ke data order untuk konsistensi
        if ($photoUrl) {
            $response['photo_url'] = $photoUrl;
            $response['data']->photo_url = $photoUrl;
        }

        return response()->json($response, 201);
    }

    // GET /orders → list semua order
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        $orders = Order::with('product', 'user') // HAPUS 'lense'
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get()
            ->map(function ($order) {
                // Tambahkan photo_url yang lengkap
                if ($order->photo) {
                    $order->photo_url = asset($order->photo);
                } else {
                    $order->photo_url = null;
                }
                return $order;
            });

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

        // Tambahkan photo_url
        if ($order->photo) {
            $order->photo_url = asset($order->photo);
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

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // ✅ LOG AUDIT - Status order diupdate
        AuditLogService::log(
            'update',
            'orders',
            "Mengubah status pesanan #ORD-{$order->id} dari {$oldStatus} menjadi {$order->status}",
            null, null, $order->id,
            ['status' => $oldStatus],
            ['status' => $order->status]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status order berhasil diperbarui',
            'data'    => $order->load('user', 'product'), // HAPUS 'lense'
        ], 200);
    }

    // GET /orders/recent → order terbaru (dashboard)
    public function recent()
    {
        $orders = Order::with('user', 'product') // HAPUS 'lense'
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
                DB::raw('MONTH(updated_at) as month'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->where('status', 'completed')
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
                DB::raw('YEAR(updated_at) as year'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->where('status', 'completed')
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
                DB::raw('DATE(updated_at) as date'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->where('status', 'completed')
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $orderNumber = "#ORD-{$order->id}";
            $orderId = $order->id;

            $order->delete();

            // ✅ LOG AUDIT - Order dihapus
            AuditLogService::logOrderAction('delete', $orderId, $orderNumber);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}
