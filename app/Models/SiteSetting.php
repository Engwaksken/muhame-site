<?php

namespace App\Models;

use App\Support\Media;
use App\Support\SafeUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable([
    'site_name', 'tagline', 'homepage_title', 'homepage_description', 'contact_email', 'logo_path',
    'navigation_links', 'main_menu', 'social_links', 'primary_color', 'accent_color', 'footer_text', 'footer_links', 'footer_pages',
    'footer_columns', 'explore_pages', 'explore_links',
    'newsletter_heading', 'newsletter_text', 'favicon_path', 'body_font', 'heading_font', 'base_font_size',
    'contact_phone', 'contact_address', 'office_hours', 'map_location', 'map_embed_url',
    'header_display', 'primary_text_color',
    'top_header_button_label', 'top_header_button_url',
])]
class SiteSetting extends Model
{
    public const DEFAULT_PRIMARY = '#1baa58';

    public const DEFAULT_ACCENT = '#4caa75';

    public const HEX_PATTERN = '/^#[0-9A-Fa-f]{6}$/';

    public const DEFAULT_FONT = 'instrument-sans';

    /**
     * Fonts editors can pick, served by Bunny Fonts (a privacy-friendly Google Fonts mirror).
     * key => [label, CSS family name, fallback stack]
     */
    public const FONTS = [
        'instrument-sans' => ['Instrument Sans', 'Instrument Sans', 'sans'],
        'inter' => ['Inter', 'Inter', 'sans'],
        'dm-sans' => ['DM Sans', 'DM Sans', 'sans'],
        'figtree' => ['Figtree', 'Figtree', 'sans'],
        'ibm-plex-sans' => ['IBM Plex Sans', 'IBM Plex Sans', 'sans'],
        'lato' => ['Lato', 'Lato', 'sans'],
        'montserrat' => ['Montserrat', 'Montserrat', 'sans'],
        'nunito-sans' => ['Nunito Sans', 'Nunito Sans', 'sans'],
        'open-sans' => ['Open Sans', 'Open Sans', 'sans'],
        'poppins' => ['Poppins', 'Poppins', 'sans'],
        'roboto' => ['Roboto', 'Roboto', 'sans'],
        'source-sans-3' => ['Source Sans 3', 'Source Sans 3', 'sans'],
        'work-sans' => ['Work Sans', 'Work Sans', 'sans'],
        'lora' => ['Lora (serif)', 'Lora', 'serif'],
        'merriweather' => ['Merriweather (serif)', 'Merriweather', 'serif'],
        'playfair-display' => ['Playfair Display (serif)', 'Playfair Display', 'serif'],
        'source-serif-4' => ['Source Serif 4 (serif)', 'Source Serif 4', 'serif'],
    ];

    /** Base text size options => CSS root font size. */
    public const FONT_SIZES = ['small' => '15px', 'normal' => '16px', 'large' => '17px', 'x-large' => '18px'];

    private const FALLBACK_STACKS = [
        'sans' => "ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif",
        'serif' => "ui-serif, Georgia, Cambria, 'Times New Roman', serif",
    ];

    protected function casts(): array
    {
        return [
            'navigation_links' => 'array',
            'main_menu' => 'array',
            'social_links' => 'array',
            'footer_links' => 'array',
            'footer_pages' => 'array',
            'footer_columns' => 'array',
            'explore_pages' => 'array',
            'explore_links' => 'array',
        ];
    }

    public static function current(): self
    {
        return once(fn () => static::query()->firstOrCreate(['id' => 1], static::defaults()));
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'site_name' => config('app.name', 'Muhame'),
            'tagline' => 'Stories and updates from our team.',
            'homepage_title' => 'The latest insights',
            'homepage_description' => 'Ideas, updates, and perspectives from our team.',
            'contact_email' => null,
            'logo_path' => null,
            'navigation_links' => [],
            'main_menu' => null,
            'social_links' => [],
            'footer_links' => [],
            'footer_pages' => [],
            'footer_columns' => null,
            'explore_pages' => [],
            'explore_links' => ['insights', 'events', 'contact'],
        ];
    }

    public function primaryColor(): string
    {
        return $this->validHex($this->primary_color) ?? self::DEFAULT_PRIMARY;
    }

    public function accentColor(): string
    {
        return $this->validHex($this->accent_color) ?? self::DEFAULT_ACCENT;
    }

    public const HEADER_DISPLAY = ['logo_and_name' => 'Logo and site name', 'logo' => 'Logo only', 'name' => 'Site name only'];

    public const PRIMARY_TEXT = ['white' => 'White', 'dark' => 'Dark', 'auto' => 'Automatic (best contrast)'];

    /** Text colour on primary-coloured buttons and badges. White unless set otherwise. */
    public function primaryTextColor(): string
    {
        return match ($this->primary_text_color) {
            'dark' => '#111111',
            'auto' => self::readableTextOn($this->primaryColor()),
            default => '#FFFFFF',
        };
    }

    /** Whether the header shows the logo (only possible when one is uploaded). */
    public function headerShowsLogo(): bool
    {
        return filled($this->logo_path) && $this->header_display !== 'name';
    }

    /** Whether the header shows the site name as visible text. Always true without a logo. */
    public function headerShowsName(): bool
    {
        if (! $this->headerShowsLogo()) {
            return true;
        }

        // Default with a logo: logo only, unless "logo and name" is chosen.
        return $this->header_display === 'logo_and_name';
    }

    /** Black or white, whichever is more readable on top of the given colour. */
    public static function readableTextOn(string $hex): string
    {
        [$r, $g, $b] = array_map(
            fn (string $c) => (($v = hexdec($c) / 255) <= 0.03928) ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4,
            str_split(ltrim($hex, '#'), 2),
        );
        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        // Compare contrast against white (1.0) and black (0.0).
        return (1.05 / ($luminance + 0.05)) >= (($luminance + 0.05) / 0.05) ? '#FFFFFF' : '#111111';
    }

    public function bodyFontKey(): string
    {
        return array_key_exists((string) $this->body_font, self::FONTS) ? $this->body_font : self::DEFAULT_FONT;
    }

    public function headingFontKey(): string
    {
        return array_key_exists((string) $this->heading_font, self::FONTS) ? $this->heading_font : $this->bodyFontKey();
    }

    /** CSS font-family value, e.g. "'Inter', ui-sans-serif, …". */
    public static function fontStack(string $key): string
    {
        [, $family, $fallback] = self::FONTS[$key] ?? self::FONTS[self::DEFAULT_FONT];

        return "'{$family}', ".self::FALLBACK_STACKS[$fallback];
    }

    /** One Bunny Fonts stylesheet URL for the body and heading fonts. */
    public function fontStylesheetUrl(): string
    {
        $families = collect([$this->bodyFontKey(), $this->headingFontKey()])
            ->unique()
            ->map(fn (string $key) => $key.':400,500,600,700')
            ->implode('|');

        return 'https://fonts.bunny.net/css?family='.$families.'&display=swap';
    }

    public function baseFontSize(): string
    {
        return self::FONT_SIZES[$this->base_font_size] ?? self::FONT_SIZES['normal'];
    }

    public function faviconUrl(): string
    {
        return Media::url($this->favicon_path) ?? asset('favicon.ico');
    }

    /** "tel:" link for the phone number (digits and a leading + only), or null. */
    public function phoneLink(): ?string
    {
        $digits = preg_replace('/(?!^\+)[^0-9]/', '', trim((string) $this->contact_phone));

        return strlen(ltrim($digits, '+')) >= 5 ? 'tel:'.$digits : null;
    }

    /**
     * The map shown on the contact page: the pasted embed code if there is one,
     * otherwise a Google Maps embed of the map location. No API key needed.
     */
    public function mapEmbedUrl(): ?string
    {
        if ($embed = self::extractMapEmbedUrl($this->map_embed_url)) {
            return $embed;
        }

        return filled($this->map_location)
            ? 'https://www.google.com/maps?q='.rawurlencode(trim($this->map_location)).'&output=embed'
            : null;
    }

    /**
     * Pulls the map URL out of pasted embed code (<iframe src="…">) or a plain URL.
     * Only Google Maps and OpenStreetMap embed URLs are accepted; anything else returns null.
     */
    public static function extractMapEmbedUrl(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $input, $match)) {
            $input = $match[1];
        }

        $url = html_entity_decode(trim($input), ENT_QUOTES | ENT_HTML5);
        $parts = parse_url($url);

        if (($parts['scheme'] ?? null) !== 'https' || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $isGoogle = in_array($host, ['www.google.com', 'google.com', 'maps.google.com'], true)
            && (str_starts_with($path, '/maps/embed') || ($path === '/maps' && ($query['output'] ?? null) === 'embed'));
        $isOpenStreetMap = $host === 'www.openstreetmap.org' && $path === '/export/embed.html';

        return ($isGoogle || $isOpenStreetMap) ? $url : null;
    }

    public function mapDirectionsUrl(): ?string
    {
        return filled($this->map_location)
            ? 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode(trim($this->map_location))
            : null;
    }

    /** Font Awesome brand icon for a social network name, e.g. "LinkedIn" => "fab fa-linkedin-in". */
    public static function socialIcon(string $network): string
    {
        $key = strtolower(preg_replace('/[^a-z]/i', '', $network));

        return match (true) {
            str_contains($key, 'facebook') => 'fab fa-facebook-f',
            $key === 'x' || str_contains($key, 'twitter') => 'fab fa-x-twitter',
            str_contains($key, 'linkedin') => 'fab fa-linkedin-in',
            str_contains($key, 'instagram') => 'fab fa-instagram',
            str_contains($key, 'youtube') => 'fab fa-youtube',
            str_contains($key, 'tiktok') => 'fab fa-tiktok',
            str_contains($key, 'whatsapp') => 'fab fa-whatsapp',
            default => 'fas fa-globe',
        };
    }

    public function newsletterHeading(): string
    {
        return $this->newsletter_heading ?: 'Stay in the loop';
    }

    public function newsletterText(): string
    {
        return $this->newsletter_text ?: 'Get our latest insights and upcoming events in your inbox.';
    }

    /**
     * The top menu, built from the admin's menu builder when one is set.
     * Falls back to pages marked "Show in site menu" plus extra links.
     *
     * @return array<int, array{label: string, url: string, description: ?string, children: array<int, array{label: string, url: string, description: ?string}>}>
     */
    public function publicMainMenu(): array
    {
        $configured = $this->resolveMenuItems($this->main_menu ?? []);

        if ($configured !== []) {
            return $configured;
        }

        $items = Page::query()->inNavigation()->get()
            ->map(fn (Page $page) => [
                'label' => $page->title,
                'url' => $page->url(),
                'description' => $page->excerpt,
                'children' => $page->children->map(fn (Page $child) => [
                    'label' => $child->title,
                    'url' => $child->url(),
                    'description' => $child->excerpt,
                ])->all(),
            ])
            ->all();

        foreach ($this->publicNavigationLinks() as $label => $url) {
            $items[] = ['label' => $label, 'url' => $url, 'description' => null, 'children' => []];
        }

        return $items;
    }

    /**
     * Footer link columns configured in Site settings. When empty, the layout
     * falls back to the original Explore / Pages & links columns.
     *
     * @return array<int, array{title: string, items: array<int, array{label: string, url: string}>}>
     */
    public function publicFooterColumns(): array
    {
        $columns = $this->footer_columns ?? [];

        if (! is_array($columns) || $columns === []) {
            return [];
        }

        $pages = $this->publishedPagesById();
        $resolved = [];

        foreach ($columns as $column) {
            if (! is_array($column)) {
                continue;
            }

            $title = trim((string) ($column['title'] ?? ''));
            $items = [];

            foreach (($column['items'] ?? []) as $item) {
                if (! is_array($item)) {
                    continue;
                }

                if ($entry = $this->resolveMenuItem($item, $pages)) {
                    $items[] = ['label' => $entry['label'], 'url' => $entry['url']];
                }
            }

            if ($title !== '' || $items !== []) {
                $resolved[] = ['title' => $title, 'items' => $items];
            }
        }

        return $resolved;
    }

    /**
     * @param  array<mixed>  $items
     * @return array<int, array{label: string, url: string, description: ?string, children: array<int, array{label: string, url: string, description: ?string}>}>
     */
    private function resolveMenuItems(array $items): array
    {
        if ($items === []) {
            return [];
        }

        $pages = $this->publishedPagesById();
        $menu = [];

        foreach ($items as $item) {
            if (! is_array($item) || ! ($entry = $this->resolveMenuItem($item, $pages))) {
                continue;
            }

            $children = [];

            foreach (($item['children'] ?? []) as $child) {
                if (is_array($child) && ($childEntry = $this->resolveMenuItem($child, $pages))) {
                    $children[] = [
                        'label' => $childEntry['label'],
                        'url' => $childEntry['url'],
                        'description' => $childEntry['description'],
                    ];
                }
            }

            $entry['children'] = $children;
            $menu[] = $entry;
        }

        return $menu;
    }

    /**
     * A menu item is either a published page or a safe custom link.
     *
     * @param  Collection<int, Page>  $pages
     * @return array{label: string, url: string, description: ?string}|null
     */
    private function resolveMenuItem(array $item, Collection $pages): ?array
    {
        if (($item['type'] ?? 'page') === 'link') {
            $label = trim((string) ($item['label'] ?? ''));
            $url = SafeUrl::clean($item['url'] ?? null);

            return $label !== '' && $url ? ['label' => $label, 'url' => $url, 'description' => null] : null;
        }

        $page = $pages->get((int) ($item['page_id'] ?? 0));

        if (! $page) {
            return null;
        }

        $label = trim((string) ($item['label'] ?? ''));

        return [
            'label' => $label !== '' ? $label : $page->title,
            'url' => $page->url(),
            'description' => $page->excerpt,
        ];
    }

    /** @return Collection<int, Page> */
    private function publishedPagesById(): Collection
    {
        return Page::query()->published()->get()->keyBy('id');
    }

    /** @return array<string, string> */
    public function publicNavigationLinks(): array
    {
        return $this->safeLinks($this->navigation_links ?? [], allowRelative: true);
    }

    /** @return array<string, string> */
    public function publicSocialLinks(): array
    {
        return $this->safeLinks($this->social_links ?? [], allowRelative: false);
    }

    /** @return array<string, string> */
    public function publicFooterLinks(): array
    {
        return $this->safeLinks($this->footer_links ?? [], allowRelative: true);
    }

    /** Published pages selected for the footer, preserving the admin's chosen order. */
    public function publicFooterPages(): Collection
    {
        return $this->publishedSelectedPages($this->footer_pages ?? []);
    }

    /** Published pages selected for the footer's Explore group, in admin-selected order. */
    public function publicExplorePages(): Collection
    {
        return $this->publishedSelectedPages($this->explore_pages ?? []);
    }

    /** @return array<string, string> */
    public function publicExploreLinks(): array
    {
        $available = [
            'insights' => route('posts.index'),
            'events' => route('events.index'),
            'contact' => route('contact'),
        ];
        $selected = $this->explore_links ?? array_keys($available);

        return collect($selected)->filter(fn ($key) => is_string($key) && isset($available[$key]))
            ->mapWithKeys(fn (string $key) => [match ($key) {
                'insights' => 'Blogs',
                'events' => 'Events',
                'contact' => 'Contact',
            } => $available[$key]])
            ->all();
    }

    /** @param array<mixed> $selectedPageIds */
    private function publishedSelectedPages(array $selectedPageIds): Collection
    {
        $pageIds = array_values(array_filter(array_map('intval', $selectedPageIds)));

        if ($pageIds === []) {
            return collect();
        }

        return Page::query()->published()->whereIn('id', $pageIds)->get()
            ->sortBy(fn (Page $page) => array_search($page->getKey(), $pageIds, true))
            ->values();
    }

    private function validHex(?string $value): ?string
    {
        return is_string($value) && preg_match(self::HEX_PATTERN, $value) ? strtoupper($value) : null;
    }

    /** @param array<mixed> $links
     * @return array<string, string>
     */
    private function safeLinks(array $links, bool $allowRelative): array
    {
        $safe = [];

        foreach ($links as $label => $url) {
            if (! is_string($label) || trim($label) === '') {
                continue;
            }

            if ($clean = SafeUrl::clean($url, $allowRelative)) {
                $safe[trim($label)] = $clean;
            }
        }

        return $safe;
    }
}
