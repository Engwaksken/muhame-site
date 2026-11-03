<?php

namespace App\Models;

use App\Support\Media;
use App\Support\SafeUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'site_name', 'tagline', 'homepage_title', 'homepage_description', 'contact_email', 'logo_path',
    'navigation_links', 'social_links', 'primary_color', 'accent_color', 'footer_text', 'footer_links',
    'newsletter_heading', 'newsletter_text', 'favicon_path', 'body_font', 'heading_font', 'base_font_size',
    'contact_phone', 'contact_address', 'office_hours', 'map_location', 'map_embed_url',
    'header_display', 'primary_text_color',
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
            'social_links' => 'array',
            'footer_links' => 'array',
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
            'social_links' => [],
            'footer_links' => [],
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
