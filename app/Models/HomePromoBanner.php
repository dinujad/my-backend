<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomePromoBanner extends Model
{
    protected $fillable = [
        'title',
        'bold_text',
        'post_text',
        'second_line',
        'has_discount',
        'discount_number',
        'action_text',
        'href',
        'image_alt',
        'image',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'has_discount' => 'boolean',
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
