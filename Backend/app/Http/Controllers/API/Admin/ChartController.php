<?php

namespace App\Http\Controllers\api\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ChartController extends Controller
{
    //Bieu do tong quan
    public function getSummaryReport(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Lọc các đơn hàng đã hoàn tất trong khoảng thời gian đã chọn
        $completedOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // 1. Tính toán các chỉ số chính (Key Metrics)
        $totalRevenue = $completedOrders->sum('total_price');
        $orderCount = $completedOrders->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        $diffInDays = $startDate->diffInDays($endDate) > 0 ? $startDate->diffInDays($endDate) : 1;
        $avgDailyRevenue = $diffInDays > 0 ? $totalRevenue / $diffInDays : 0;

        $cancelledOrdersCount = Order::where('status', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $cancellationRate = ($orderCount + $cancelledOrdersCount) > 0
            ? ($cancelledOrdersCount / ($orderCount + $cancelledOrdersCount)) * 100
            : 0;

        // 2. Chuẩn bị dữ liệu cho biểu đồ doanh thu theo ngày
        $dailyRevenue = $completedOrders
            ->groupBy(function ($order) {
                return $order->created_at->format('Y-m-d');
            })
            ->map(function ($group) {
                return $group->sum('total_price');
            });

        return response()->json([
            'key_metrics' => [
                'total_revenue' => round($totalRevenue, 2),
                'avg_daily_revenue' => round($avgDailyRevenue, 2),
                'order_count' => $orderCount,
                'avg_order_value' => round($avgOrderValue, 2),
                'cancellation_rate' => round($cancellationRate, 2),
            ],
            'charts_data' => [
                'daily_revenue' => $dailyRevenue->toArray(),
                // Bạn có thể thêm dữ liệu cho biểu đồ tuần, tháng, năm tại đây
            ]
        ]);
    }

    //doanh thu theo tung san pham
    public function getProductsReport(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $productsReport = OrderItem::selectRaw('
        products.name as product_name,
        sum(order_items.quantity) as total_sold,
        sum(order_items.price_each * order_items.quantity) as total_revenue
    ')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_variant_id', '=', 'products.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.name')
            ->orderByDesc('total_revenue')
            ->get();

        return response()->json($productsReport);
    }

    //doanh thu theo khach hang
    public function getCustomersReport(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $customersReport = Order::selectRaw('
        users.name as customer_name,
        count(orders.id) as order_count,
        sum(orders.total_price) as total_spent
    ')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('users.name')
            ->orderByDesc('total_spent')
            ->get();

        return response()->json($customersReport);
    }

    // ReportController.php
    public function getWeeklyRevenue(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $weeklyRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('WEEK(created_at) as week, SUM(total_price) as total')
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        return response()->json($weeklyRevenue);
    }

    // ReportController.php
    public function getMonthlyRevenue(Request $request)
    {
        $monthlyRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')])
            ->selectRaw('MONTH(created_at) as month, SUM(total_price) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($monthlyRevenue);
    }

    // ReportController.php
    public function getYearlyRevenue(Request $request)
    {
        $yearlyRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')])
            ->selectRaw('YEAR(created_at) as year, SUM(total_price) as total')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return response()->json($yearlyRevenue);
    }
}
