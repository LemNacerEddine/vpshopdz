<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('create-store');
        }

        // Get stats
        $stats = [
            'products_count' => Product::where('store_id', $store->id)->count(),
            'active_products' => Product::where('store_id', $store->id)->where('status', 'active')->count(),
            'customers_count' => Customer::where('store_id', $store->id)->count(),
            'orders_count' => Order::where('store_id', $store->id)->count(),
            'pending_orders' => Order::where('store_id', $store->id)->where('status', 'pending')->count(),
            'total_revenue' => Order::where('store_id', $store->id)->where('status', 'delivered')->sum('total'),
            'today_orders' => Order::where('store_id', $store->id)->whereDate('created_at', today())->count(),
            'today_revenue' => Order::where('store_id', $store->id)->whereDate('created_at', today())->where('status', 'delivered')->sum('total'),
        ];

        // Recent orders
        $recentOrders = Order::where('store_id', $store->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Low stock products
        $lowStock = Product::where('store_id', $store->id)
            ->where('track_inventory', true)
            ->where('stock_quantity', '<=', 5)
            ->where('status', 'active')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('store', 'stats', 'recentOrders', 'lowStock'));
    }

    public function products(Request $request)
    {
        $store = Auth::user()->store;
        
        $query = Product::where('store_id', $store->id)->with('category', 'images');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('dashboard.products.index', compact('products', 'store'));
    }

    public function orders(Request $request)
    {
        $store = Auth::user()->store;
        
        $query = Order::where('store_id', $store->id)->with('items');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        // Order stats
        $stats = [
            'total' => Order::where('store_id', $store->id)->count(),
            'pending' => Order::where('store_id', $store->id)->where('status', 'pending')->count(),
            'confirmed' => Order::where('store_id', $store->id)->where('status', 'confirmed')->count(),
            'shipped' => Order::where('store_id', $store->id)->where('status', 'shipped')->count(),
            'delivered' => Order::where('store_id', $store->id)->where('status', 'delivered')->count(),
            'cancelled' => Order::where('store_id', $store->id)->where('status', 'cancelled')->count(),
        ];

        return view('dashboard.orders.index', compact('orders', 'store', 'stats'));
    }

    public function settings()
    {
        $store = Auth::user()->store;
        return view('dashboard.settings', compact('store'));
    }
}
