<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'category_id', 'title', 'slug', 'body', 'status', 'published_at', 'featured_image', 'seo_title', 'seo_description', 'noindex'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if ($post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }

            if ($post->status !== 'published') {
                $post->published_at = null;
            }

            if (blank($post->slug) && filled($post->title)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'noindex' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
