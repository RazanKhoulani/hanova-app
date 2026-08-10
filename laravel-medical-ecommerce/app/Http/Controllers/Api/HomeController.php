<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Resources\ProductResource;
use App\Models\Concern;
use App\Models\Product;
use App\Services\OfferService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, OfferService $offerService)
    {
        $lang = $request->header('Accept-Language', 'ar');
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';
        $products = Product::with('concerns')->latest()->limit(20)->get();
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
            ])
            ->values();
        $offer = $offerService->getActiveForUser($request->user('sanctum'));

        return response()->json([
            'data' => [
                'products' => ProductResource::collection($products)->resolve($request),
                'categories' => $categories,
                'active_offer' => $offer ? (new OfferResource($offer))->resolve($request) : null,
            ],
        ]);
    }
}
