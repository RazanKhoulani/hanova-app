<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BundledProductImagePublisher
{
    /**
     * Publish the product images shipped with the application to the public disk.
     *
     * Uploaded images are never overwritten. This is important on Railway, where
     * the public disk is backed by a persistent volume at runtime.
     */
    public function publishMissing(): int
    {
        try {
            $source = database_path('seeders/assets/products');
            if (! File::isDirectory($source)) {
                return 0;
            }

            $disk = Storage::disk('public');
            $published = 0;

            foreach (File::files($source) as $image) {
                $path = 'products/'.$image->getFilename();
                if ($disk->exists($path)) {
                    continue;
                }

                $disk->put($path, File::get($image->getPathname()));
                $published++;
            }

            if ($published > 0) {
                Log::info('Published bundled product images to public storage.', [
                    'count' => $published,
                ]);
            }

            return $published;
        } catch (Throwable $exception) {
            Log::warning('Unable to publish bundled product images.', [
                'error' => $exception->getMessage(),
            ]);

            return 0;
        }
    }
}
