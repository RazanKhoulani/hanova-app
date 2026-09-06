<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppSettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings.currency', [
            'settings' => array_merge(
                AppSetting::reviewRewardValues(),
                AppSetting::siteContentValues(),
            ),
            'deliveryAreas' => DeliveryArea::query()->orderBy('name_ar')->get(),
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
            'delivery_areas' => ['sometimes', 'array'],
            'delivery_areas.*.fee' => ['required', 'numeric', 'min:0'],
            'delivery_areas.*.fee_usd' => ['nullable', 'numeric', 'min:0'],
            'delivery_areas.*.is_active' => ['nullable', 'boolean'],
        ]);

        $values = [
            'review_reward_percentage' => (string) $data['review_reward_percentage'],
            'review_reward_expiry_days' => (string) $data['review_reward_expiry_days'],
            'site_about_ar' => $data['site_about_ar'] ?? '',
            'site_about_en' => $data['site_about_en'] ?? '',
            'site_goal_ar' => $data['site_goal_ar'] ?? '',
            'site_goal_en' => $data['site_goal_en'] ?? '',
        ];

        DB::transaction(function () use ($values, $data): void {
            foreach ($values as $key => $value) {
                AppSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
            }

            foreach ($data['delivery_areas'] ?? [] as $id => $areaData) {
                DeliveryArea::query()->whereKey($id)->update([
                    'fee' => $areaData['fee'],
                    'fee_usd' => $areaData['fee_usd'] ?? null,
                    'is_active' => (bool) ($areaData['is_active'] ?? false),
                ]);
            }
        });

        return redirect()
            ->route('admin.settings.currency.edit')
            ->with('success', __('admin.currency_settings_saved'));
    }
}
