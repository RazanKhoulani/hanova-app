<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concern;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:100',
            'stock' => 'nullable|in:available,low,out,untracked',
            'catalog_type' => 'nullable|in:product,bundle,session,nutrition',
        ]);

        $productsQuery = Product::query()
            ->with('concerns')
            ->withCount('visibleReviews')
            ->withAvg('visibleReviews as visible_reviews_avg_rating', 'rating');

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $productsQuery->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if (($filters['catalog_type'] ?? null) !== null) {
            $productsQuery->where('catalog_type', $filters['catalog_type']);
        }

        match ($filters['stock'] ?? null) {
            'available' => $productsQuery->where('track_inventory', true)->whereColumn('stock_quantity', '>', 'low_stock_threshold'),
            'low' => $productsQuery->where('track_inventory', true)->where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'low_stock_threshold'),
            'out' => $productsQuery->where('track_inventory', true)->where('stock_quantity', '<=', 0),
            'untracked' => $productsQuery->where('track_inventory', false),
            default => null,
        };

        $products = $productsQuery->latest()->paginate(10)->withQueryString();
        $trackedProducts = Product::query()->where('track_inventory', true);
        $inventorySummary = [
            'tracked' => (clone $trackedProducts)->count(),
            'available' => (clone $trackedProducts)
                ->whereColumn('stock_quantity', '>', 'low_stock_threshold')
                ->count(),
            'low' => (clone $trackedProducts)
                ->where('stock_quantity', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->count(),
            'out' => (clone $trackedProducts)->where('stock_quantity', '<=', 0)->count(),
        ];

        return view('admin.products.index', compact('products', 'inventorySummary', 'filters'));
    }

    public function create()
    {
        $concerns = Concern::where('is_active', true)->orderBy('name_ar')->get();
        $bundleProducts = Product::orderBy('name_ar')->get(['id', 'name_ar', 'name_en']);
        return view('admin.products.create', compact('concerns', 'bundleProducts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'usage_ar' => 'nullable|string', 'usage_en' => 'nullable|string',
            'suitable_for_ar' => 'nullable|string', 'suitable_for_en' => 'nullable|string',
            'active_ingredients_ar' => 'nullable|string', 'active_ingredients_en' => 'nullable|string',
            'warnings_ar' => 'nullable|string', 'warnings_en' => 'nullable|string',
            'price_syp' => 'required|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'cost_syp' => 'required|numeric|min:0',
            'cost_usd' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:100',
            'catalog_type' => 'nullable|in:product,bundle,session,nutrition',
            'bundle_product_ids' => 'nullable|array',
            'bundle_product_ids.*' => 'integer|exists:products,id',
            'track_inventory' => 'nullable|boolean',
            'stock_quantity' => 'nullable|integer|min:0|max:1000000',
            'low_stock_threshold' => 'nullable|integer|min:0|max:1000000',
            'concern_ids' => 'nullable|array',
            'concern_ids.*' => 'exists:concerns,id',
            'image' => 'nullable|image|max:10240',
        ]);

        $validated['track_inventory'] = $request->boolean('track_inventory');
        $validated['price'] = $validated['price_syp'];
        $validated['cost'] = $validated['cost_syp'];
        $validated['stock_quantity'] = (int) ($validated['stock_quantity'] ?? 0);
        $validated['low_stock_threshold'] = (int) ($validated['low_stock_threshold'] ?? 5);

        $this->productService->createProduct($validated);

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.product_created'));
    }

    public function show($id)
    {
        // View not typically needed in dashboard unless for distinct preview, but we'll stick to basic CRUD
        $product = $this->productService->getProductById($id);
        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = $this->productService->getProductById($id);
        $concerns = Concern::where('is_active', true)->orderBy('name_ar')->get();
        $bundleProducts = Product::where('id', '!=', $product->id)
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_en']);
        return view('admin.products.edit', compact('product', 'concerns', 'bundleProducts'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'usage_ar' => 'nullable|string', 'usage_en' => 'nullable|string',
            'suitable_for_ar' => 'nullable|string', 'suitable_for_en' => 'nullable|string',
            'active_ingredients_ar' => 'nullable|string', 'active_ingredients_en' => 'nullable|string',
            'warnings_ar' => 'nullable|string', 'warnings_en' => 'nullable|string',
            'price_syp' => 'required|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'cost_syp' => 'required|numeric|min:0',
            'cost_usd' => 'nullable|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:100',
            'catalog_type' => 'nullable|in:product,bundle,session,nutrition',
            'bundle_product_ids' => 'nullable|array',
            'bundle_product_ids.*' => 'integer|exists:products,id',
            'track_inventory' => 'nullable|boolean',
            'stock_quantity' => 'nullable|integer|min:0|max:1000000',
            'low_stock_threshold' => 'nullable|integer|min:0|max:1000000',
            'concern_ids' => 'nullable|array',
            'concern_ids.*' => 'exists:concerns,id',
            'image' => 'nullable|image|max:10240',
        ]);

        $product = $this->productService->getProductById($id);
        $validated['price'] = $validated['price_syp'];
        $validated['cost'] = $validated['cost_syp'];
        $validated['concern_ids'] = $request->input('concern_ids', []);
        $validated['bundle_product_ids'] = $request->input('bundle_product_ids', []);
        $validated['track_inventory'] = $request->boolean('track_inventory');
        $validated['stock_quantity'] = (int) ($validated['stock_quantity'] ?? 0);
        $validated['low_stock_threshold'] = (int) ($validated['low_stock_threshold'] ?? 5);
        $this->productService->updateProduct($product, $validated);

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.product_updated'));
    }

    public function destroy($id)
    {
        $product = $this->productService->getProductById($id);
        $this->productService->deleteProduct($product);

        return redirect()->route('admin.products.index')
            ->with('success', __('admin.product_deleted'));
    }

    public function updateStock(Request $request, $id)
    {
        $data = $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $product = $this->productService->getProductById($id);
        $this->productService->updateProduct($product, $data);

        return back()->with('success', __('admin.stock_updated'));
    }
}
