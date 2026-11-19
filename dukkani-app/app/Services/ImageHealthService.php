<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Decides whether a stored product image can actually be loaded. The browser
 * beacon and the products:check-images command both use this so a temporary
 * network hiccup on a visitor's phone never permanently hides a healthy photo.
 */
class ImageHealthService
{
    public function loads(Product $product): bool
    {
        return $this->loadsPath((string) $product->image_path);
    }

    public function loadsPath(string $path): bool
    {
        $path = trim($path);

        if ($path === '') {
            return false;
        }

        if (! str_starts_with($path, 'http')) {
            // A zero-byte file is as dead as a missing one.
            if (str_starts_with($path, 'images/')) {
                $file = public_path($path);

                return is_file($file) && (int) filesize($file) > 0;
            }

            $disk = Storage::disk('public');

            return $disk->exists($path) && (int) $disk->size($path) > 0;
        }

        if (! $this->isPublicHttpUrl($path)) {
            return false;
        }

        try {
            // Browsers ask for modern formats; Cloudinary may only have a
            // cached JPEG for a deleted original. Ask like a browser so the
            // check does not report a stale cache as healthy.
            $response = Http::timeout(8)
                ->withHeaders(['Accept' => 'image/avif,image/webp,image/png,image/*,*/*;q=0.8'])
                ->head($path);
        } catch (Throwable) {
            return false;
        }

        // Some image hosts refuse HEAD but still serve the asset.
        if ($response->status() === 405) {
            return true;
        }

        if (! $response->successful()) {
            return false;
        }

        $type = (string) $response->header('Content-Type');

        return $type === '' || str_starts_with($type, 'image/');
    }

    /**
     * Do not let an owner-supplied image URL turn the health check into an
     * SSRF probe of the hosting network.
     */
    protected function isPublicHttpUrl(string $url): bool
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower(trim((string) parse_url($url, PHP_URL_HOST), '[]'));

        if ($host === '' || $host === 'localhost' || str_ends_with($host, '.localhost')) {
            return false;
        }

        $ip = filter_var($host, FILTER_VALIDATE_IP) ?: gethostbyname($host);

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        return true;
    }
}
