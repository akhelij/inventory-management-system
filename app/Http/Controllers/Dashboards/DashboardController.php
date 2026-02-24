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
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today()->format('Y-m-d');

        return view('dashboard', [
            'products' => Product::count(),
            'orders' => Order::where('user_id', auth()->id())->count(),
            'purchases' => Purchase::where('user_id', auth()->id())->count(),
            'todayPurchases' => Purchase::where('date', $today)->count(),
            'todayProducts' => Product::where('created_at', $today)->count(),
            'todayQuotations' => Quotation::where('created_at', $today)->count(),
            'todayOrders' => Order::where('created_at', $today)->count(),
            'categories' => Category::count(),
            'quotations' => Quotation::where('user_id', auth()->id())->count(),
        ] + $this->getAnalytics() + $this->getCustomerStats());
    }

    public function getAnalytics(): array
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);
        $previousStartDate = $startDate->copy()->subDays(30);

        $currentPeriodOrders = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $previousPeriodOrders = Order::query()
            ->whereBetween('created_at', [$previousStartDate, $startDate])
            ->get();

        $currentTotal = $currentPeriodOrders->count();
        $currentRevenue = $currentPeriodOrders->sum('total');
        $completedOrders = $currentPeriodOrders->where('pay', '>', 0)->count();

        $previousTotal = $previousPeriodOrders->count();
        $previousRevenue = $previousPeriodOrders->sum('total');

        $salesGrowth = $previousTotal > 0
            ? (($currentTotal - $previousTotal) / $previousTotal) * 100
            : 100;

        $conversionRate = $currentTotal > 0
            ? ($completedOrders / $currentTotal) * 100
            : 0;

        $revenueGrowth = $previousRevenue > 0
            ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100
            : 100;

        return [
            'sales_growth_percentage' => round($salesGrowth, 2),
            'conversion_rate' => round($conversionRate, 2),
            'revenue_amount' => $currentRevenue,
            'revenue_growth_percentage' => round($revenueGrowth, 2),
        ];
    }

    public function getCustomerStats(): array
    {
        $totalCustomers = Customer::count();
        $newCustomers = Customer::where('created_at', '>=', Carbon::now()->subDays(30))->count();

        $percentageIncrease = $totalCustomers > 0
            ? ($newCustomers / $totalCustomers) * 100
            : 0;

        return [
            'total_customers' => $totalCustomers,
            'new_customers_30days' => $newCustomers,
            'percentage_of_total' => round($percentageIncrease, 2),
        ];
    }
}
