<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'body', 'status', 'show_in_navigation', 'navigation_order', 'seo_title', 'seo_description', 'noindex'])]
class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    /**
     * Top-level paths already used by the app. Pages live at /{slug},
     * so these can never be used as a page slug.
     */
    public const RESERVED_SLUGS = [
        'admin', 'api', 'build', 'categories', 'contact', 'css', 'filament', 'fonts', 'images', 'js',
        'livewire', 'login', 'logout', 'posts', 'register', 'search', 'storage', 'tags', 'up', 'vendor',
    ];

    public const SLUG_PATTERN = '[a-z0-9]+(?:-[a-z0-9]+)*';

    protected static function booted(): void
    {
        static::saving(function (Page $page): void {
            if (blank($page->slug) && filled($page->title)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    protected function casts(): array
    {
        return [
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

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeInNavigation(Builder $query): Builder
    {
        return $query->published()->where('show_in_navigation', true)->orderBy('navigation_order')->orderBy('title');
    }
}
