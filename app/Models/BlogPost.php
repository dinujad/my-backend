<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_category_id',
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'featured_image_alt',
        'is_published',
        'published_at',
        'seo_title',
        'seo_description',
        'focus_keyword',
        'meta_keywords',
        'canonical_url',
        'og_image',
        'noindex',
        'tags',
        'faqs',
        'related_links',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'noindex' => 'boolean',
            'published_at' => 'datetime',
            'faqs' => 'array',
            'related_links' => 'array',
            'tags' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * SEO-friendly, unique slug: lowercase, hyphenated, ASCII, stop words trimmed,
     * max 75 chars, with -2, -3 … appended when already taken.
     */
    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $stopWords = ['a', 'an', 'the', 'and', 'or', 'of', 'to', 'in', 'on', 'for', 'with', 'at', 'by', 'is', 'are'];

        $words = array_values(array_filter(
            explode('-', Str::slug($source)),
            fn ($w) => $w !== ''
        ));
        // Drop stop words, but never strip a slug down to nothing.
        $filtered = array_values(array_filter($words, fn ($w) => ! in_array($w, $stopWords, true)));
        $base = implode('-', $filtered ?: $words);
        $base = trim(Str::limit($base, 75, ''), '-') ?: 'post';

        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** Plain-text word count of the HTML body. */
    public function getWordCountAttribute(): int
    {
        return str_word_count(strip_tags((string) $this->content));
    }

    /** Minutes to read at ~200 wpm, minimum 1. */
    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) ceil($this->word_count / 200));
    }

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if (empty($post->slug)) {
                $post->slug = static::uniqueSlug($post->title, $post->id);
            }
        });
    }
}
