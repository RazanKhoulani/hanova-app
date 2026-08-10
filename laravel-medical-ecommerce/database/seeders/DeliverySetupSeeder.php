<?php

namespace Database\Seeders;

use App\Models\DeliveryArea;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DeliverySetupSeeder extends Seeder
{
    public function run(): void
    {
        $deliveryRole = Role::firstOrCreate(['name' => 'delivery']);

        $deliveryUser = User::firstOrCreate([
            'phone' => '+963900000001',
        ], [
            'name' => 'Delivery Boy',
            'password' => bcrypt('password123'),
        ]);

        if (empty($deliveryUser->phone_verified_at)) {
            $deliveryUser->phone_verified_at = now();
            $deliveryUser->save();
        }

        $deliveryUser->assignRole($deliveryRole);

        foreach ([
            ['name_ar' => 'المزة', 'name_en' => 'Mezzeh', 'fee' => 3.00],
            ['name_ar' => 'المالكي', 'name_en' => 'Malki', 'fee' => 3.50],
            ['name_ar' => 'كفرسوسة', 'name_en' => 'Kafr Sousa', 'fee' => 4.00],
            ['name_ar' => 'مشروع دمر', 'name_en' => 'Dummar', 'fee' => 5.00],
        ] as $area) {
            DeliveryArea::updateOrCreate(
                ['name_en' => $area['name_en']],
                $area + ['is_active' => true]
            );
        }
    }
}
