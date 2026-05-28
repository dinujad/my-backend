<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Normalize product image paths from DB (legacy formats, old APP_URLs) to public web paths.
 */
final class ProductMediaPath
{
    /**
     * Public URL path, e.g. /storage/products/abc.jpg or /images/logo.png
     */
    public static function normalize(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path);
            $path = $parsed['path'] ?? '';
            if ($path === '') {
                return '';
            }
        }

        $path = '/'.ltrim($path, '/');

        while (str_contains($path, '/storage/storage/')) {
            $path = str_replace('/storage/storage/', '/storage/', $path);
        }

        if (str_starts_with($path, '/storage/') || str_starts_with($path, '/images/')) {
            return $path;
        }

        $trim = ltrim($path, '/');
        if (str_starts_with($trim, 'storage/')) {
            return '/'.$trim;
        }

        if (str_starts_with($trim, 'images/')) {
            return '/'.$trim;
        }

        if (str_starts_with($trim, 'public/storage/')) {
            return '/'.substr($trim, strlen('public/'));
        }

        return '/storage/'.$trim;
    }

    /** Path relative to the public disk root, e.g. products/abc.jpg */
    public static function toDiskPath(string $path): string
    {
        $norm = self::normalize($path);
        if ($norm === '' || str_starts_with($norm, '/images/')) {
            return ltrim($norm, '/');
        }

        return preg_replace('#^/storage/#', '', $norm) ?? ltrim($norm, '/');
    }

    public static function existsOnDisk(string $path): bool
    {
        $norm = self::normalize($path);
        if ($norm === '' || str_starts_with($norm, '/images/')) {
            return is_file(public_path(ltrim($norm, '/')));
        }

        return Storage::disk(self::uploadDisk())->exists(self::toDiskPath($path));
    }

    /** Value stored in DB: storage/products/… (no leading slash) */
    public static function toDatabaseValue(string $path): string
    {
        $norm = self::normalize($path);
        if ($norm === '') {
            return '';
        }

        return ltrim($norm, '/');
    }

    /**
     * Full public URL for API / storefront.
     *
     * - B2 / S3 disk: returns the cloud URL directly (no APP_URL prefix needed).
     * - Local public disk: APP_URL + /storage/…
     */
    public static function publicUrl(?string $path): string
    {
        $norm = self::normalize((string) $path);
        if ($norm === '') {
            return '';
        }

        if (str_starts_with($norm, '/images/')) {
            $frontend = rtrim((string) config('app.frontend_url', ''), '/');
            if ($frontend !== '') {
                return $frontend.$norm;
            }
        }

        $disk = config('filesystems.default', 'public');

        if (in_array($disk, ['b2', 's3', 'r2'], true)) {
            $diskPath = self::toDiskPath($norm);
            try {
                $url = Storage::disk($disk)->url($diskPath);
                if ($url && str_starts_with($url, 'http')) {
                    return $url;
                }
            } catch (\Throwable) {
                // fall through to APP_URL
            }
        }

        $base = rtrim((string) config('app.url', ''), '/');

        return $base !== '' ? $base.$norm : $norm;
    }

    /**
     * The disk name used for uploads.
     * - 'public' locally (files in storage/app/public)
     * - 'b2' / 's3' / 'r2' in production (cloud object storage)
     */
    public static function uploadDisk(): string
    {
        return (string) config('filesystems.default', 'public');
    }

    /**
     * Upload a file to the configured disk and return the relative path stored in DB.
     * For local disk: 'products/abc.jpg'
     * For cloud:      'products/abc.jpg' (the disk URL is resolved via publicUrl())
     */
    public static function storeUpload(\Illuminate\Http\UploadedFile $file, string $folder): string
    {
        $disk = self::uploadDisk();
        $path = $file->storePublicly($folder, ['disk' => $disk]);

        if (! $path) {
            throw new \RuntimeException("Failed to store upload in {$folder} on disk {$disk}.");
        }

        return $path;
    }

    /** Delete a file from the configured upload disk. */
    public static function deleteUpload(string $storedPath): void
    {
        if ($storedPath === '') {
            return;
        }
        $disk     = self::uploadDisk();
        $diskPath = self::toDiskPath($storedPath);
        Storage::disk($disk)->delete($diskPath);
    }

    /** Check if a file exists on the configured upload disk. */
    public static function existsUpload(string $storedPath): bool
    {
        if ($storedPath === '' || str_starts_with(self::normalize($storedPath), '/images/')) {
            return false;
        }
        return Storage::disk(self::uploadDisk())->exists(self::toDiskPath($storedPath));
    }
}
