<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;

class AppSettingsController extends Controller
{
    public function __invoke()
    {
        $settings = AppSetting::pricingValues();
        $siteContent = AppSetting::siteContentValues();

        return response()->json([
            'data' => [
                'base_currency' => 'SYP_OLD',
                'syp_old_per_new' => (float) $settings['syp_old_per_new'],
                'syp_old_per_usd' => (float) $settings['syp_old_per_usd'],
                'show_dual_syp' => $settings['show_dual_syp'] === '1',
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
