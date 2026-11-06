<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'parent_id', 'title', 'slug', 'excerpt', 'hero_image', 'body', 'blocks', 'status', 'is_homepage',
    'show_in_navigation', 'navigation_order', 'seo_title', 'seo_description', 'noindex', 'header_link_label', 'header_link_url',
])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    /**
     * Top-level paths already used by the app. Pages live at /{slug},
     * so these can never be used as a page slug.
     */
    public const RESERVED_SLUGS = [
        'admin', 'api', 'build', 'categories', 'contact', 'css', 'events', 'filament', 'fonts', 'images',
        'insights', 'js', 'livewire', 'login', 'logout', 'newsletter', 'posts', 'register', 'search',
        'storage', 'tags', 'up', 'vendor',
    ];

    public const SLUG_PATTERN = '[a-z0-9]+(?:-[a-z0-9]+)*';

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            if (blank($page->slug) && filled($page->title)) {
                $page->slug = Str::slug($page->title);
            }
        });

        // Only one page can be the homepage at a time.
        static::saved(function (Page $page): void {
            if ($page->is_homepage) {
                static::query()->whereKeyNot($page->getKey())->where('is_homepage', true)->update(['is_homepage' => false]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'is_homepage' => 'boolean',
            'show_in_navigation' => 'boolean',
            'navigation_order' => 'integer',
            'noindex' => 'boolean',
        ];
    }

    /** Route constraint for /{slug}: a valid slug that is not a reserved path. */
    public static function routePattern(): string
    {
        return '(?!(?:'.implode('|', self::RESERVED_SLUGS).')$)'.self::SLUG_PATTERN;
    }

    public static function homepage(): ?self
    {
        return static::query()->published()->where('is_homepage', true)->first();
    }

    public function url(): string
    {
        return $this->is_homepage ? route('home') : route('pages.show', $this->slug);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /** Top-level menu pages, each with its published sub-pages for the dropdown. */
    public function scopeInNavigation(Builder $query): Builder
    {
        return $query->published()
            ->whereNull('parent_id')
            ->where('show_in_navigation', true)
            ->with(['children' => fn ($children) => $children->published()->orderBy('navigation_order')->orderBy('title')])
            ->orderBy('navigation_order')
            ->orderBy('title');
    }
}
