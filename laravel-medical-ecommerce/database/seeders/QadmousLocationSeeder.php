<?php

namespace Database\Seeders;

use App\Models\QadmousLocation;
use Illuminate\Database\Seeder;

class QadmousLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['دمشق', 'Damascus', 'مركز مدينة دمشق', 'Damascus City Center'],
            ['ريف دمشق', 'Rif Dimashq', 'جرمانا', 'Jaramana'],
            ['ريف دمشق', 'Rif Dimashq', 'صحنايا', 'Sahnaya'],
            ['ريف دمشق', 'Rif Dimashq', 'قدسيا', 'Qudsaya'],
            ['حلب', 'Aleppo', 'مركز مدينة حلب', 'Aleppo City Center'],
            ['حمص', 'Homs', 'مركز مدينة حمص', 'Homs City Center'],
            ['حماة', 'Hama', 'مركز مدينة حماة', 'Hama City Center'],
            ['اللاذقية', 'Latakia', 'مركز مدينة اللاذقية', 'Latakia City Center'],
            ['طرطوس', 'Tartous', 'مركز مدينة طرطوس', 'Tartous City Center'],
            ['إدلب', 'Idlib', 'مركز مدينة إدلب', 'Idlib City Center'],
            ['درعا', 'Daraa', 'مركز مدينة درعا', 'Daraa City Center'],
            ['السويداء', 'As-Suwayda', 'مركز مدينة السويداء', 'As-Suwayda City Center'],
            ['القنيطرة', 'Quneitra', 'مركز مدينة القنيطرة', 'Quneitra City Center'],
            ['دير الزور', 'Deir ez-Zor', 'مركز مدينة دير الزور', 'Deir ez-Zor City Center'],
            ['الرقة', 'Raqqa', 'مركز مدينة الرقة', 'Raqqa City Center'],
            ['الحسكة', 'Al-Hasakah', 'مركز مدينة الحسكة', 'Al-Hasakah City Center'],
        ];

        foreach ($locations as $index => [$governorateAr, $governorateEn, $branchAr, $branchEn]) {
            QadmousLocation::query()->firstOrCreate(
                [
                    'governorate_ar' => $governorateAr,
                    'branch_ar' => $branchAr,
                ],
                [
                    'governorate_en' => $governorateEn,
                    'branch_en' => $branchEn,
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ],
            );
        }
    }
}
