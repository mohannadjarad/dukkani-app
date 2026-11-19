<?php

namespace App\Services;

use App\Exceptions\ImageServiceNotConfiguredException;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ImageService
{
    /**
     * Upload an image to Cloudinary.
     *
     * @return array{url: string, public_id: string|null}
     */
    public function upload(UploadedFile $file, string $folder): array
    {
        if (! $this->configured()) {
            if ($this->allowsLocalFallback()) {
                return $this->uploadLocally($file, $folder);
            }

            throw new ImageServiceNotConfiguredException('CLOUDINARY_URL is not configured.');
        }

        $result = $this->cloudinary()->uploadApi()->upload($file->getRealPath(), [
            'folder' => trim($folder, '/'),
            'resource_type' => 'image',
        ]);

        return [
            'url' => (string) $result['secure_url'],
            'public_id' => (string) $result['public_id'],
        ];
    }

    /**
     * Delete an uploaded Cloudinary asset. Failures are logged but never block
     * the image replacement or product deletion that triggered the cleanup.
     */
    public function delete(?string $publicId): void
    {
        if (! $publicId || ! $this->configured()) {
            return;
        }

        try {
            // Invalidate the CDN copy too; otherwise a deleted asset can keep
            // loading from cache for weeks and make the next bug invisible.
            $this->cloudinary()->uploadApi()->destroy($publicId, ['invalidate' => true]);
        } catch (Throwable $exception) {
            Log::warning('Cloudinary image deletion failed.', [
                'public_id' => $publicId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Add a delivery transformation to a Cloudinary URL. Legacy local paths and
     * external URLs are returned unchanged.
     */
    public function url(string $url, string $transform): string
    {
        $url = trim($url);
        $transform = trim($transform);

        if ($url === '' || $transform === '') {
            return $url;
        }

        if (! str_contains($url, 'res.cloudinary.com') || ! str_contains($url, '/image/upload/')) {
            return $url;
        }

        [$prefix, $path] = explode('/image/upload/', $url, 2);

        if (str_starts_with($path, $transform.'/')) {
            return $url;
        }

        return $prefix.'/image/upload/'.$transform.'/'.$path;
    }

    /**
     * Local uploads are stored as disk-relative paths, not absolute URLs. The
     * model builds the public URL from the current request host, so an upload
     * made through the dashboard works on localhost, a LAN IP, and Render alike.
     *
     * @return array{url: string, public_id: null}
     */
    protected function uploadLocally(UploadedFile $file, string $folder): array
    {
        $path = $file->store(trim($folder, '/'), 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Unable to store the uploaded image.');
        }

        return [
            'url' => $path,
            'public_id' => null,
        ];
    }

    /**
     * Return the public-disk path when an absolute URL points at this app's own
     * /storage prefix. Older uploads stored APP_URL-based links, which broke
     * when the same image was opened through a phone's LAN address.
     */
    public function localStoragePath(string $url): ?string
    {
        if (! preg_match('~^https?://~i', $url)) {
            return null;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);

        if (! str_starts_with($path, '/storage/')) {
            return null;
        }

        $host = strtolower(trim((string) parse_url($url, PHP_URL_HOST), '[]'));
        $appHost = strtolower(trim((string) parse_url((string) config('app.url'), PHP_URL_HOST), '[]'));
        $requestHost = strtolower(trim((string) request()->getHost(), '[]'));
        $localHosts = ['localhost', '127.0.0.1', '::1'];

        if ($host !== '' && $host !== $appHost && $host !== $requestHost && ! in_array($host, $localHosts, true)) {
            return null;
        }

        $relative = ltrim(substr($path, strlen('/storage/')), '/');

        return $relative === '' ? null : $relative;
    }

    protected function configured(): bool
    {
        return filled(config('services.cloudinary.url'));
    }

    protected function allowsLocalFallback(): bool
    {
        // The demo service runs with APP_ENV=production but has no Cloudinary
        // account; it keeps uploads on the container disk like before.
        return in_array(app()->environment(), ['local', 'testing'], true)
            || (bool) config('app.demo_mode');
    }

    protected function cloudinary(): Cloudinary
    {
        return new Cloudinary((string) config('services.cloudinary.url'));
    }
}
