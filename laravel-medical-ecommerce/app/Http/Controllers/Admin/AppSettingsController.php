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
                AppSetting::reviewRewardValues(),
                AppSetting::siteContentValues(),
            ),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'review_reward_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'review_reward_expiry_days' => ['required', 'integer', 'min:1', 'max:365'],
            'site_about_ar' => ['nullable', 'string', 'max:1000'],
            'site_about_en' => ['nullable', 'string', 'max:1000'],
            'site_goal_ar' => ['nullable', 'string', 'max:1000'],
            'site_goal_en' => ['nullable', 'string', 'max:1000'],
        ]);

        $values = [
            'review_reward_percentage' => (string) $data['review_reward_percentage'],
            'review_reward_expiry_days' => (string) $data['review_reward_expiry_days'],
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
