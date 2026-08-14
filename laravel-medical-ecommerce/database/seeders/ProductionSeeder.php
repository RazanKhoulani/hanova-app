<?php

namespace Database\Seeders;

use App\Models\DeliveryArea;
use App\Models\User;
use App\Support\SyrianPhoneNumber;
use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Models\Role;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        Role::firstOrCreate(['name' => 'user']);
        $deliveryRole = Role::firstOrCreate(['name' => 'delivery']);

        $admin = $this->upsertConfiguredUser(
            'HANOVA_ADMIN',
            'Hanova Admin',
            [$adminRole, $doctorRole],
        );

        if ($admin) {
            $admin->availability_schedule = [
                'duration_minutes' => [
                    'session' => 60,
                    'treatment' => 30,
                ],
                'clinic' => [
                    'default' => [
                        ['start' => '09:00', 'end' => '12:00'],
                        ['start' => '14:00', 'end' => '17:00'],
                    ],
                ],
                'online' => [
                    'default' => [
                        ['start' => '10:00', 'end' => '13:00'],
                        ['start' => '15:00', 'end' => '18:00'],
                    ],
                ],
            ];
            $admin->save();
        }

        $this->upsertConfiguredUser(
            'HANOVA_DELIVERY',
            'Hanova Delivery',
            [$deliveryRole],
        );

        foreach ([
            ['name_ar' => 'المزة', 'name_en' => 'Mezzeh', 'fee' => 3.00],
            ['name_ar' => 'المالكي', 'name_en' => 'Malki', 'fee' => 3.50],
            ['name_ar' => 'كفرسوسة', 'name_en' => 'Kafr Sousa', 'fee' => 4.00],
            ['name_ar' => 'مشروع دمر', 'name_en' => 'Dummar', 'fee' => 5.00],
        ] as $area) {
            DeliveryArea::updateOrCreate(
                ['name_en' => $area['name_en']],
                $area + ['is_active' => true],
            );
        }

        $this->call([
            ConcernSeeder::class,
            OfferSeeder::class,
            ProductSeeder::class,
            ProductConcernSeeder::class,
            FaqSeeder::class,
        ]);
    }

    /**
     * @param  array<int, Role>  $roles
     */
    private function upsertConfiguredUser(string $prefix, string $defaultName, array $roles): ?User
    {
        $name = trim((string) env("{$prefix}_NAME", $defaultName));
        $phone = SyrianPhoneNumber::normalize(env("{$prefix}_PHONE"));
        $password = (string) env("{$prefix}_PASSWORD");

        if ($phone === '' && $password === '') {
            return null;
        }

        if ($phone === '' || $password === '') {
            throw new RuntimeException("{$prefix}_PHONE and {$prefix}_PASSWORD must both be configured.");
        }

        $user = User::updateOrCreate(
            ['phone' => $phone],
            [
                'name' => $name,
                'password' => $password,
            ],
        );

        $user->phone_verified_at = now();
        $user->save();
        $user->syncRoles($roles);

        return $user;
    }
}
