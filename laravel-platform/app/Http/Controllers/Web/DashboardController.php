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
    /**
     * Get the authenticated user's store or redirect to create one
     */
    private function getStore()
    {
        $store = Auth::user()->store;
        if (!$store) {
            abort(redirect()->route('create-store'));
        }
        return $store;
    }

    /**
     * Dashboard Home - Overview
     */
    public function index(Request $request)
    {
        $store = $this->getStore();

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

        $recentOrders = Order::where('store_id', $store->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $lowStock = Product::where('store_id', $store->id)
            ->where('track_inventory', true)
            ->where('stock_quantity', '<=', 5)
            ->where('status', 'active')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('store', 'stats', 'recentOrders', 'lowStock'));
    }

    /**
     * Products Management
     */
    public function products(Request $request)
    {
        $store = $this->getStore();

        $query = Product::where('store_id', $store->id)->with('category', 'images');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('dashboard.products.index', compact('products', 'store'));
    }

    /**
     * Product Create Page
     */
    public function createProduct(Request $request)
    {
        $store = $this->getStore();
        $categories = \App\Models\Category::where('store_id', $store->id)
            ->orderBy('name')
            ->get(['id', 'name', 'name_ar']);
        return view('dashboard.products.create', compact('store', 'categories'));
    }

    /**
     * Product Edit Page
     */
    public function editProduct(Request $request, string $id)
    {
        $store = $this->getStore();
        $product = Product::where('store_id', $store->id)
            ->where('id', $id)
            ->with(['category', 'images', 'variants', 'options.values'])
            ->firstOrFail();
        $categories = \App\Models\Category::where('store_id', $store->id)
            ->orderBy('name')
            ->get(['id', 'name', 'name_ar']);
        return view('dashboard.products.edit', compact('store', 'categories', 'product'));
    }

    /**
     * Categories Management
     */
    public function categories()
    {
        $store = $this->getStore();
        return view('dashboard.categories.index', compact('store'));
    }

    /**
     * Orders Management
     */
    public function orders(Request $request)
    {
        $store = $this->getStore();

        $query = Order::where('store_id', $store->id)->with('items');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

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

    /**
     * Customers Management
     */
    public function customers()
    {
        $store = $this->getStore();
        return view('dashboard.customers.index', compact('store'));
    }

    /**
     * Shipping Management
     */
    public function shipping()
    {
        $store = $this->getStore();
        return view('dashboard.shipping.index', compact('store'));
    }

    /**
     * Coupons Management
     */
    public function coupons()
    {
        $store = $this->getStore();
        return view('dashboard.coupons.index', compact('store'));
    }

    /**
     * Reviews Management
     */
    public function reviews()
    {
        $store = $this->getStore();
        return view('dashboard.reviews.index', compact('store'));
    }

    /**
     * Abandoned Carts
     */
    public function abandonedCarts()
    {
        $store = $this->getStore();
        return view('dashboard.abandoned-carts.index', compact('store'));
    }

    /**
     * Themes Management
     */
    public function themes()
    {
        $store = $this->getStore();
        return view('dashboard.themes.index', compact('store'));
    }

    /**
     * Domains Management
     */
    public function domains()
    {
        $store = $this->getStore();
        return view('dashboard.domains.index', compact('store'));
    }

    /**
     * Pages Management
     */
    public function pages()
    {
        $store = $this->getStore();
        return view('dashboard.pages.index', compact('store'));
    }

    /**
     * Analytics
     */
    public function analytics()
    {
        $store = $this->getStore();
        return view('dashboard.analytics.index', compact('store'));
    }

    /**
     * Notifications Settings
     */
    public function notifications()
    {
        $store = $this->getStore();
        return view('dashboard.notifications.index', compact('store'));
    }

    /**
     * Pixels Management
     */
    public function pixels()
    {
        $store = $this->getStore();
        return view('dashboard.pixels.index', compact('store'));
    }

    /**
     * Facebook Ads
     */
    public function facebookAds()
    {
        $store = $this->getStore();
        return view('dashboard.facebook-ads.index', compact('store'));
    }

    /**
     * Subscription & Billing
     */
    public function subscription()
    {
        $store = $this->getStore();
        return view('dashboard.subscription.index', compact('store'));
    }

    /**
     * Staff Management
     */
    public function staff()
    {
        $store = $this->getStore();
        return view('dashboard.staff.index', compact('store'));
    }

    /**
     * Media Library
     */
    public function media()
    {
        $store = $this->getStore();
        return view('dashboard.media.index', compact('store'));
    }

    /**
     * Store Settings
     */
    public function settings()
    {
        $store = $this->getStore();
        return view('dashboard.settings.index', compact('store'));
    }
}
