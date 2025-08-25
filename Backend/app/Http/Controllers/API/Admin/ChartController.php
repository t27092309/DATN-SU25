<?php

namespace App\Http\Controllers\api\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ChartController extends Controller
{
    //Bieu do tong quan
    public function getSummaryReport(Request $request)
    {
        Log::info('Raw date input', ['start_date' => $request->input('start_date')]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        Log::info('Parsed start_date', ['start_date' => $startDate->toDateString()]);

        Log::info('getSummaryReport called', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        // Lọc các đơn hàng đã hoàn tất trong khoảng thời gian đã chọn
        $completedOrders = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        Log::info('Completed orders count', ['count' => $completedOrders->count()]);

        // 1. Tính toán các chỉ số chính (Key Metrics)
        $totalRevenue = $completedOrders->sum('total_price');
        $orderCount = $completedOrders->count();
        $avgOrderValue = $orderCount > 0 ? $totalRevenue / $orderCount : 0;

        // 3. Log các chỉ số chính đã tính toán
        Log::info('Calculated metrics', [
            'total_revenue' => $totalRevenue,
            'order_count' => $orderCount,
            'avg_order_value' => $avgOrderValue
        ]);
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

    // do not write any php code block, just the code
    public function getProductsReport(Request $request)
    {
        // Thêm dòng log để kiểm tra các tham số ngày tháng được gửi lên
        Log::info('getProductsReport called', [
            'start_date_raw' => $request->input('start_date'),
            'end_date_raw' => $request->input('end_date')
        ]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Thêm dòng log để kiểm tra ngày tháng sau khi được Carbon xử lý
        Log::info('Parsed dates', [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ]);

        $productsReport = OrderItem::selectRaw('
        products.name as product_name,
        sum(order_items.quantity) as total_sold,
        sum(order_items.price_each * order_items.quantity) as total_revenue
    ')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('orders.status', 'delivered')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Thêm dòng log để kiểm tra kết quả cuối cùng của câu truy vấn
        Log::info('Products report query result', ['data' => $productsReport->toArray()]);

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
            ->where('orders.status', 'delivered')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('users.name')
            ->orderByDesc('total_spent')
            ->get();

        return response()->json($customersReport);
    }

    public function getWeeklyRevenue(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $weeklyRevenue = Order::where('status', 'delivered')
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
        $monthlyRevenue = Order::where('status', 'delivered')
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
        $yearlyRevenue = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$request->input('start_date'), $request->input('end_date')])
            ->selectRaw('YEAR(created_at) as year, SUM(total_price) as total')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return response()->json($yearlyRevenue);
    }

    public function getCustomerGrowth(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        // Đếm số lượng khách hàng cũ (có 2 đơn hàng trở lên)
        $loyalCustomers = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('user_id')
            ->havingRaw('count(user_id) >= 2')
            ->get();
        $loyalCustomersCount = $loyalCustomers->count();

        // Đếm số lượng khách hàng mới (chỉ có 1 đơn hàng)
        $newCustomers = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('user_id')
            ->havingRaw('count(user_id) = 1')
            ->get();
        $newCustomersCount = $newCustomers->count();

        return response()->json([
            'new_customers_count' => $newCustomersCount,
            'loyal_customers_count' => $loyalCustomersCount,
            'total_customers' => $newCustomersCount + $loyalCustomersCount,
        ]);
    }

    public function getTopCoupons(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

        $topCoupons = Order::whereIn('status', ['delivered', 'completed'])
            ->whereNotNull('coupon_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('
            coupons.code as coupon_code,
            count(orders.id) as usage_count,
            sum(orders.total_price) as total_revenue,
            sum(coupons.discount_value) as total_discount
        ')
            ->join('coupons', 'orders.coupon_id', '=', 'coupons.id')
            ->groupBy('coupons.code')
            ->orderByDesc('usage_count')
            ->get();

        return response()->json($topCoupons);
    }
}
