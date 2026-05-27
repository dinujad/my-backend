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

    /** Browser path on this app, e.g. /storage/uploads/file.png */
    public function getWebPathAttribute(): string
    {
        return ProductMediaPath::normalize('storage/'.ltrim((string) $this->path, '/'));
    }

    /** Full URL for copy/paste (uses current request host in admin, APP_URL in API). */
    public function getUrlAttribute(): string
    {
        $webPath = $this->web_path;
        if ($webPath === '') {
            return '';
        }

        if (! app()->runningInConsole() && request()->hasHeader('Host')) {
            return url($webPath);
        }

        return ProductMediaPath::publicUrl('storage/'.ltrim((string) $this->path, '/'));
    }

    public function existsOnDisk(): bool
    {
        return filled($this->path) && Storage::disk('public')->exists($this->path);
    }
}
