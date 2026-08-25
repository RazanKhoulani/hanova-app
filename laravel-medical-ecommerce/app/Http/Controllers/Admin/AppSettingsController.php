<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.currency', [
            'settings' => array_merge(
                AppSetting::pricingValues(),
                AppSetting::siteContentValues(),
            ),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'syp_old_per_new' => ['required', 'numeric', 'gt:0'],
            'syp_old_per_usd' => ['required', 'numeric', 'gt:0'],
            'show_dual_syp' => ['nullable', 'boolean'],
            'site_about_ar' => ['nullable', 'string', 'max:1000'],
            'site_about_en' => ['nullable', 'string', 'max:1000'],
            'site_goal_ar' => ['nullable', 'string', 'max:1000'],
            'site_goal_en' => ['nullable', 'string', 'max:1000'],
        ]);

        $values = [
            'syp_old_per_new' => (string) $data['syp_old_per_new'],
            'syp_old_per_usd' => (string) $data['syp_old_per_usd'],
            'show_dual_syp' => $request->boolean('show_dual_syp') ? '1' : '0',
            'site_about_ar' => $data['site_about_ar'] ?? '',
            'site_about_en' => $data['site_about_en'] ?? '',
            'site_goal_ar' => $data['site_goal_ar'] ?? '',
            'site_goal_en' => $data['site_goal_en'] ?? '',
        ];

        foreach ($values as $key => $value) {
            AppSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return redirect()
            ->route('admin.settings.currency.edit')
            ->with('success', __('admin.currency_settings_saved'));
    }
}
