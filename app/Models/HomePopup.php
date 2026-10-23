<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePopup extends Model
{
    protected $fillable = [
        'enabled',
        'image_path',
        'close_seconds',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'close_seconds' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['enabled' => true, 'close_seconds' => 3]
        );
    }

    public function publicImageUrl(): ?string
    {
        $path = str_replace('\\', '/', trim((string) $this->image_path));
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $trim = ltrim($path, '/');
        if (str_starts_with($trim, 'images/')) {
            return '/' . $trim;
        }
        if (str_starts_with($trim, 'storage/')) {
            return asset($trim);
        }

        return asset('storage/' . $trim);
    }
}
