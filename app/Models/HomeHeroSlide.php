<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeHeroSlide extends Model
{
    protected $fillable = [
        'eyebrow',
        'title_line1',
        'title_line2',
        'highlight_text',
        'description',
        'cta_label',
        'cta_url',
        'image',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActiveOrdered($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        $path = ltrim(str_replace('\\', '/', $this->image), '/');
        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        return '/storage/'.$path;
    }

    public function deleteImageFile(): void
    {
        if (! $this->image || str_starts_with($this->image, 'http')) {
            return;
        }

        $diskPath = preg_replace('#^storage/#', '', ltrim($this->image, '/')) ?? $this->image;
        if (Storage::disk('public')->exists($diskPath)) {
            Storage::disk('public')->delete($diskPath);
        }
    }
}
