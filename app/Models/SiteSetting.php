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
])]
class SiteSetting extends Model
{
    public const DEFAULT_PRIMARY = '#B45309';

    public const DEFAULT_ACCENT = '#1C1917';

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
