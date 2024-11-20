<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::where("user_id", auth()->id())->count();
        $products = Product::count();

        $purchases = Purchase::where("user_id", auth()->id())->count();
        $todayPurchases = Purchase::where('date', today()->format('Y-m-d'))->count();
        $todayProducts = Product::where('created_at', today()->format('Y-m-d'))->count();
        $todayQuotations = Quotation::where('created_at', today()->format('Y-m-d'))->count();
        $todayOrders = Order::where('created_at', today()->format('Y-m-d'))->count();

        $categories = Category::count();
        $quotations = Quotation::where("user_id", auth()->id())->count();

        $analytics = $this->getAnalytics();
        $customer_stats = $this->getCustomerStats();
        return view('dashboard', [
            'products' => $products,
            'orders' => $orders,
            'purchases' => $purchases,
            'todayPurchases' => $todayPurchases,
            'todayProducts' => $todayProducts,
            'todayQuotations' => $todayQuotations,
            'todayOrders' => $todayOrders,
            'categories' => $categories,
            'quotations' => $quotations
        ] + $analytics + $customer_stats);
    }

    public function getAnalytics()
    {
        // Set up time periods
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);
        $previousStartDate = $startDate->copy()->subDays(30);

        // Current period orders
        $currentPeriodOrders = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Previous period orders
        $previousPeriodOrders = Order::query()
            ->whereBetween('created_at', [$previousStartDate, $startDate])
            ->get();

        // Calculate metrics
        $currentPeriodMetrics = [
            'total_orders' => $currentPeriodOrders->count(),
            'revenue' => $currentPeriodOrders->sum('total'),
            'completed_orders' => $currentPeriodOrders->where('pay', '>', 0)->count(),
        ];

        $previousPeriodMetrics = [
            'total_orders' => $previousPeriodOrders->count(),
            'revenue' => $previousPeriodOrders->sum('total'),
        ];

        // Calculate percentages and rates
        $salesGrowth = $previousPeriodMetrics['total_orders'] > 0
            ? (($currentPeriodMetrics['total_orders'] - $previousPeriodMetrics['total_orders']) / $previousPeriodMetrics['total_orders']) * 100
            : 100;

        $conversionRate = $currentPeriodMetrics['total_orders'] > 0
            ? ($currentPeriodMetrics['completed_orders'] / $currentPeriodMetrics['total_orders']) * 100
            : 0;

        $revenueGrowth = $previousPeriodMetrics['revenue'] > 0
            ? (($currentPeriodMetrics['revenue'] - $previousPeriodMetrics['revenue']) / $previousPeriodMetrics['revenue']) * 100
            : 100;

        return [
            'sales_growth_percentage' => round($salesGrowth, 2),
            'conversion_rate' => round($conversionRate, 2),
            'revenue_amount' => $currentPeriodMetrics['revenue'],
            'revenue_growth_percentage' => round($revenueGrowth, 2),
        ];
    }

    public function getCustomerStats()
    {
        $totalCustomers = Customer::count();
        $lastThirtyDaysCustomers = Customer::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        $percentageIncrease = $totalCustomers > 0
            ? ($lastThirtyDaysCustomers / $totalCustomers) * 100
            : 0;

        return [
            'total_customers' => $totalCustomers,
            'new_customers_30days' => $lastThirtyDaysCustomers,
            'percentage_of_total' => round($percentageIncrease, 2)
        ];
    }
}
