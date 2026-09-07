<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

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
            'home_consultation_title_ar' => 'استشارة البشرة',
            'home_consultation_title_en' => 'Skin Consultation',
            'home_consultation_description_ar' => 'احجزي موعداً في العيادة أو أونلاين ضمن الأوقات المتاحة فعلياً.',
            'home_consultation_description_en' => 'Book an in-clinic or online appointment from the available times.',
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
