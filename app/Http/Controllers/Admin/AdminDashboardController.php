<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\SyncLog;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $totalSales = Order::where('payment_status', 'approved')->sum('total');
        $pendingScrapes = Product::where('scraper_status', 'pending')->orWhereNull('main_image')->count();
        $lowStockProducts = Product::where('stock', '<=', 5)->count();

        $recentOrders = Order::latest()->take(6)->get();
        $recentLogs = SyncLog::latest()->take(6)->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalOrders',
            'totalSales',
            'pendingScrapes',
            'lowStockProducts',
            'recentOrders',
            'recentLogs'
        ));
    }
}
