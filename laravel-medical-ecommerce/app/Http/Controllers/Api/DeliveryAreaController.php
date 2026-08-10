<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;

class DeliveryAreaController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->query('lang', $request->header('Accept-Language', 'ar'));
        $lang = str_starts_with((string) $lang, 'en') ? 'en' : 'ar';

        $areas = DeliveryArea::query()
            ->where('is_active', true)
            ->orderBy($lang === 'en' ? 'name_en' : 'name_ar')
            ->get()
            ->map(fn (DeliveryArea $area) => [
                'id' => $area->id,
                'name' => $lang === 'en' ? $area->name_en : $area->name_ar,
                'name_ar' => $area->name_ar,
                'name_en' => $area->name_en,
                'fee' => (float) $area->fee,
            ]);

        return response()->json(['data' => $areas]);
    }
}
