<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Walks every stored product image and makes sure it still loads, then keeps
 * the products.image_broken_at flag honest in both directions. The storefront
 * already hides dead images behind the neutral placeholder the moment a browser
 * reports one; this command is the belt-and-braces pass for links that break
 * while nobody is shopping, and the way to clear a false alarm.
 */
class CheckProductImagesCommand extends Command
{
    protected $signature = 'products:check-images {--store= : Only check one store slug}';

    protected $description = 'Flag product images that no longer load and clear the ones that recovered';

    public function handle(): int
    {
        $query = Product::query()->whereNotNull('image_path');

        if ($slug = $this->option('store')) {
            $query->whereHas('store', fn ($store) => $store->where('slug', $slug));
        }

        $checked = 0;
        $broken = 0;
        $recovered = 0;

        $query->chunkById(100, function ($products) use (&$checked, &$broken, &$recovered) {
            foreach ($products as $product) {
                $checked++;

                if ($this->loads($product)) {
                    $recovered += (int) $product->markImageHealthy();

                    continue;
                }

                $broken += (int) $product->markImageBroken();
            }
        });

        $this->components->info("Checked {$checked} product images: {$broken} newly broken, {$recovered} back online.");

        return self::SUCCESS;
    }

    protected function loads(Product $product): bool
    {
        $path = (string) $product->image_path;

        if (! str_starts_with($path, 'http')) {
            return str_starts_with($path, 'images/')
                ? is_file(public_path($path))
                : Storage::disk('public')->exists($path);
        }

        try {
            $response = Http::timeout(8)->head($path);
        } catch (Throwable) {
            return false;
        }

        // 405 means the host refuses HEAD but the asset is there.
        if ($response->status() === 405) {
            return true;
        }

        if (! $response->successful()) {
            return false;
        }

        $type = (string) $response->header('Content-Type');

        return $type === '' || str_starts_with($type, 'image/');
    }
}