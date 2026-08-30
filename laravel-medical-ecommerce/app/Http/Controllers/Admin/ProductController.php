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

    public function index()
    {
        $products = $this->productService->getAllProducts(10);
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

        return view('admin.products.index', compact('products', 'inventorySummary'));
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
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
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
            'price' => 'sometimes|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
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
