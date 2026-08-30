<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Central defaults keep the mobile API usable before an administrator
     * configures a conversion rate in the dashboard.
     */
    public static function pricingDefaults(): array
    {
        return [
            'display_currency' => 'syp_new',
            'syp_old_per_new' => '0',
            'syp_old_per_usd' => '0',
            'show_dual_syp' => '0',
        ];
    }

    public static function pricingValues(): array
    {
        return array_replace(
            self::pricingDefaults(),
            self::query()
                ->whereIn('key', array_keys(self::pricingDefaults()))
                ->pluck('value', 'key')
                ->all(),
        );
    }

    public static function reviewRewardDefaults(): array
    {
        return [
            'review_reward_percentage' => '10',
            'review_reward_expiry_days' => '90',
        ];
    }

    public static function reviewRewardValues(): array
    {
        return array_replace(
            self::reviewRewardDefaults(),
            self::query()
                ->whereIn('key', array_keys(self::reviewRewardDefaults()))
                ->pluck('value', 'key')
                ->all(),
        );
    }

    public static function siteContentDefaults(): array
    {
        return [
            'site_about_ar' => 'تجربة واحدة تجمع العيادة، الاستشارات، المواعيد، ومنتجات العناية المختارة لتكون رحلتك أوضح وأسهل.',
            'site_about_en' => 'One connected experience for clinic visits, consultations, appointments, and carefully selected skincare.',
            'site_goal_ar' => 'كل خطوة في التطبيق مرتبطة بالداشبورد وملفك، من الحجز حتى استلام الطلب.',
            'site_goal_en' => 'Every app step connects to the dashboard and your profile, from booking to order delivery.',
        ];
    }

    public static function siteContentValues(): array
    {
        return array_replace(
            self::siteContentDefaults(),
            self::query()
                ->whereIn('key', array_keys(self::siteContentDefaults()))
                ->pluck('value', 'key')
                ->all(),
        );
    }
}
