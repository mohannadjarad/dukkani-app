<?php

namespace App\Services;

use App\Exceptions\ImageServiceNotConfiguredException;
use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            $this->cloudinary()->uploadApi()->destroy($publicId);
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

    /** @return array{url: string, public_id: null} */
    protected function uploadLocally(UploadedFile $file, string $folder): array
    {
        $path = $file->store(trim($folder, '/'), 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Unable to store the uploaded image.');
        }

        $url = Storage::disk('public')->url($path);

        if (! preg_match('~^https?://~i', $url)) {
            $url = url($url);
        }

        return [
            'url' => $url,
            'public_id' => null,
        ];
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
