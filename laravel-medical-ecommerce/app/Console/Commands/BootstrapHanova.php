<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\Product;
use App\Models\User;
use App\Services\BundledProductImagePublisher;
use Database\Seeders\ProductionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BootstrapHanova extends Command
{
    protected $signature = 'hanova:bootstrap';

    protected $description = 'Initialize a fresh Hanova database without replacing existing clinic data';

    public function handle(BundledProductImagePublisher $images): int
    {
        DB::transaction(function () {
            $marker = AppSetting::firstOrCreate(['key' => 'deployment_initialized'], ['value' => '0']);
            $marker = AppSetting::whereKey($marker->id)->lockForUpdate()->firstOrFail();

            if ($marker->value === '1') {
                return;
            }

            // Restored or populated databases must not receive sample data.
            if (! User::exists() && ! Product::exists()) {
                $this->call('db:seed', ['--class' => ProductionSeeder::class, '--force' => true]);
            }

            $marker->update(['value' => '1']);
        });

        $images->publishMissing();
        $this->info('Hanova initialization completed. Existing clinic data preserved.');

        return self::SUCCESS;
    }
}
