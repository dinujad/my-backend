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

        return Storage::disk('public')->exists(self::toDiskPath($path));
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
}
