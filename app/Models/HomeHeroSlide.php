<?php

namespace App\Models;

use App\Support\ProductMediaPath;
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

        $url = ProductMediaPath::publicUrl($this->image);

        return $url !== '' ? $url : null;
    }

    public function deleteImageFile(): void
    {
        if (! $this->image) {
            return;
        }

        ProductMediaPath::deleteUpload($this->image);
    }
}
