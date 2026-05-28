<?php

namespace App\Models;

use App\Support\ProductMediaPath;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'path',
        'filename',
        'alt_text',
        'mime_type',
        'size',
        'width',
        'height',
        'folder',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Full public URL — works for local disk (APP_URL/storage/...) and cloud (R2/S3/B2).
     */
    public function getUrlAttribute(): string
    {
        if (! filled($this->path)) {
            return '';
        }

        return ProductMediaPath::publicUrl('storage/'.ltrim((string) $this->path, '/'));
    }

    /**
     * web_path kept for backwards compat; always returns the public URL.
     */
    public function getWebPathAttribute(): string
    {
        return $this->url;
    }

    /**
     * Check whether the file actually exists on the configured upload disk (local or cloud).
     */
    public function existsOnDisk(): bool
    {
        if (! filled($this->path)) {
            return false;
        }

        $disk = ProductMediaPath::uploadDisk();

        return Storage::disk($disk)->exists((string) $this->path);
    }
}
