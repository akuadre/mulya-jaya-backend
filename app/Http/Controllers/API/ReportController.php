<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Menghasilkan laporan komprehensif untuk dashboard admin.
     */
    public function generateReport(Request $request)
    {
        // === 1. DATA RINGKASAN (SUMMARY CARDS) ===
        $completedOrders = Order::where('status', 'completed');

        $summary = [
            'totalRevenueAllTime'   => (int) $completedOrders->sum('total_price'),
            'totalRevenueCurrentYear' => (int) Order::where('status', 'completed')->whereYear('order_date', now()->year)->sum('total_price'),
            'totalOrdersCurrentMonth' => Order::whereMonth('order_date', now()->month)->whereYear('order_date', now()->year)->count(),
            'averageOrderValue'     => (int) $completedOrders->avg('total_price'),
        ];

        // === 2. PRODUK TERLARIS (TOP 5) ===
        $bestSellingProducts = DB::table('orders')
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.image_url',
                DB::raw('count(orders.product_id) as sales_count')
            )
            ->where('orders.status', 'completed')
            ->groupBy('products.id', 'products.name', 'products.image_url') // Group by semua kolom produk
            ->orderBy('sales_count', 'desc')
            ->take(5)
            ->get();

        // === 3. PRODUK DENGAN STOK RENDAH (STOK <= 10) ===
        $lowStockProducts = Product::orderBy('stock', 'asc')
            ->where('stock', '<=', 10)
            ->take(10)
            ->get();

        // === 4. DATA PENJUALAN UNTUK GRAFIK (DINAMIS BERDASARKAN FILTER) ===
        $period = $request->query('period', 'monthly'); // Default 'monthly'
        $salesOverTime = $this->getSalesDataByPeriod($period);

        return response()->json([
            'success' => true,
            'data' => [
                'summary'               => $summary,
                'salesOverTime'         => $salesOverTime,
                'bestSellingProducts'   => $bestSellingProducts,
                'lowStockProducts'      => $lowStockProducts,
            ]
        ]);
    }

    /**
     * Helper function untuk mengambil data penjualan berdasarkan periode.
     */
    private function getSalesDataByPeriod($period)
    {
        $query = Order::where('status', 'completed');

        if ($period === 'monthly') {
            $query->whereYear('order_date', now()->year);
            $results = $query->select(
                    DB::raw('MONTH(order_date) as month'),
                    DB::raw('SUM(total_price) as total')
                )
                ->groupBy('month')->pluck('total', 'month')->all();

            $data = array_fill(1, 12, 0);
            foreach ($results as $month => $total) {
                $data[$month] = $total;
            }

            return [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                'data'   => array_values($data),
            ];
        }

        if ($period === 'daily') {
            $endDate = now()->endOfDay();
            $startDate = now()->subDays(29)->startOfDay();
            $query->whereBetween('order_date', [$startDate, $endDate]);

            $results = $query->select(
                    DB::raw('DATE(order_date) as date'),
                    DB::raw('SUM(total_price) as total')
                )
                ->groupBy('date')->pluck('total', 'date')->all();

            $data = [];
            $labels = [];
            for ($i = 0; $i < 30; $i++) {
                $date = $startDate->copy()->addDays($i);
                $dateString = $date->format('Y-m-d');
                $labels[] = $date->format('d/m');
                $data[] = $results[$dateString] ?? 0;
            }
            return ['labels' => $labels, 'data' => $data];
        }

        // Default: yearly
        $results = $query->select(
                DB::raw('YEAR(order_date) as year'),
                DB::raw('SUM(total_price) as total')
            )
            ->groupBy('year')->orderBy('year', 'asc')->pluck('total', 'year')->all();

        return [
            'labels' => array_keys($results),
            'data'   => array_values($results),
        ];
    }
}
