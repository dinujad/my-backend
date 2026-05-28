<?php

namespace App\Models;

use App\Support\ProductMediaPath;
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
