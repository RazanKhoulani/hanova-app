<?php

namespace App\Http\Controllers;

use App\Models\Concern;
use App\Models\Product;
use App\Services\OfferService;
use Illuminate\Contracts\View\View;

class SiteController extends Controller
{
    public function __invoke(OfferService $offerService): View
    {
        $locale = app()->getLocale() === 'en' ? 'en' : 'ar';
        $products = Product::query()
            ->with('concerns')
            ->latest()
            ->limit(8)
            ->get();
        $concerns = Concern::query()
            ->where('is_active', true)
            ->orderBy($locale === 'en' ? 'name_en' : 'name_ar')
            ->limit(12)
            ->get();

        return view('welcome', [
            'locale' => $locale,
            'products' => $products,
            'concerns' => $concerns,
            'activeOffer' => $offerService->getActiveForUser(null),
            'productCount' => Product::count(),
            'concernCount' => Concern::where('is_active', true)->count(),
        ]);
    }
}
