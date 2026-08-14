<?php

use App\Support\SyrianPhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $seen = [];

        DB::table('users')->orderBy('id')->select(['id', 'phone'])->each(function ($user) use (&$seen) {
            $normalized = SyrianPhoneNumber::normalize($user->phone);

            if (! SyrianPhoneNumber::isValid($normalized)) {
                throw new \RuntimeException("Cannot normalize phone for user {$user->id}.");
            }

            if (isset($seen[$normalized]) && $seen[$normalized] !== $user->id) {
                throw new \RuntimeException("Duplicate normalized phone detected for users {$seen[$normalized]} and {$user->id}.");
            }

            $seen[$normalized] = $user->id;
            DB::table('users')->where('id', $user->id)->update(['phone' => $normalized]);
        });

        DB::table('patients')->orderBy('id')->select(['id', 'phone'])->each(function ($patient) {
            $normalized = SyrianPhoneNumber::normalize($patient->phone);

            if (SyrianPhoneNumber::isValid($normalized)) {
                DB::table('patients')->where('id', $patient->id)->update(['phone' => $normalized]);
            }
        });
    }

    public function down(): void
    {
        // The canonical format is intentionally irreversible.
    }
};
