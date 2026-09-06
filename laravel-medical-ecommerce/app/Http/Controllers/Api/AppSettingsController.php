<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;

class AppSettingsController extends Controller
{
    public function __invoke()
    {
        $siteContent = AppSetting::siteContentValues();

        return response()->json([
            'data' => [
                'currencies' => ['SYP', 'USD'],
                'pricing_mode' => 'independent_dual_prices',
                'about' => [
                    'ar' => $siteContent['site_about_ar'],
                    'en' => $siteContent['site_about_en'],
                ],
                'goal' => [
                    'ar' => $siteContent['site_goal_ar'],
                    'en' => $siteContent['site_goal_en'],
                ],
            ],
        ]);
    }
}
