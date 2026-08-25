<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Concern;
use App\Models\Product;
use App\Services\ProductService;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'query' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'concern' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'catalog_type' => 'nullable|in:product,bundle,session,nutrition',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $productsQuery = Product::with('concerns');

        if (!empty($filters['query'])) {
            $search = trim($filters['query']);
            $productsQuery->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('description_ar', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $productsQuery->where('category', $filters['category']);
        }

        if (!empty($filters['brand'])) {
            $productsQuery->where('brand', $filters['brand']);
        }

        if (!empty($filters['catalog_type'])) {
            $productsQuery->where('catalog_type', $filters['catalog_type']);
        }

        if (!empty($filters['concern'])) {
            $concern = $filters['concern'];
            $productsQuery->whereHas('concerns', function ($query) use ($concern) {
                $query->where('slug', $concern);

                if (is_numeric($concern)) {
                    $query->orWhere('concerns.id', (int) $concern);
                }
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        $products = $productsQuery->latest()->paginate($perPage);

        return ProductResource::collection($products)->additional([
            'message' => 'Products retrieved successfully'
        ]);
    }

    public function categories()
    {
        $lang = request()->query('lang', request()->header('Accept-Language', 'ar'));
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';

        $categories = Concern::query()
            ->where('is_active', true)
            ->orderBy($lang === 'en' ? 'name_en' : 'name_ar')
            ->get()
            ->map(fn (Concern $concern) => [
                'id' => $concern->id,
                'name' => $lang === 'en' ? $concern->name_en : $concern->name_ar,
                'slug' => $concern->slug,
                'image' => $concern->image,
                'type' => 'concern',
            ]);

        return response()->json(['data' => $categories]);
    }

    public function catalogFilters()
    {
        $brands = Product::query()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->values();

        return response()->json([
            'data' => [
                'brands' => $brands,
                'catalog_types' => [
                    ['key' => 'product', 'label_ar' => 'منتجات العناية', 'label_en' => 'Care products'],
                    ['key' => 'bundle', 'label_ar' => 'البكجات', 'label_en' => 'Bundles'],
                    ['key' => 'session', 'label_ar' => 'الجلسات', 'label_en' => 'Sessions'],
                    ['key' => 'nutrition', 'label_ar' => 'التغذية', 'label_en' => 'Nutrition'],
                ],
            ],
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:100',
            'catalog_type' => 'nullable|in:product,bundle,session,nutrition',
            'bundle_product_ids' => 'nullable|array',
            'bundle_product_ids.*' => 'integer|exists:products,id',
            'concern_ids' => 'nullable|array',
            'concern_ids.*' => 'exists:concerns,id',
            'image' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors()->toArray());
        }

        $product = $this->productService->createProduct($validator->validated());

        return $this->successResponse($product, 'Product created successfully', 201);
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = $this->productService->getProductById($id);
        return new ProductResource($product);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:100',
            'catalog_type' => 'nullable|in:product,bundle,session,nutrition',
            'bundle_product_ids' => 'nullable|array',
            'bundle_product_ids.*' => 'integer|exists:products,id',
            'concern_ids' => 'nullable|array',
            'concern_ids.*' => 'exists:concerns,id',
            'image' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors()->toArray());
        }

        $product = $this->productService->getProductById($id);
        $updatedProduct = $this->productService->updateProduct($product, $validator->validated());

        return $this->successResponse($updatedProduct, 'Product updated successfully');
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        $product = $this->productService->getProductById($id);
        $this->productService->deleteProduct($product);

        return $this->successResponse(null, 'Product deleted successfully', 204);
    }
}
