<?php

namespace App\Models;

use App\Support\SafeUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'site_name', 'tagline', 'homepage_title', 'homepage_description', 'contact_email', 'logo_path',
    'navigation_links', 'social_links', 'primary_color', 'accent_color', 'footer_text', 'footer_links',
    'newsletter_heading', 'newsletter_text',
])]
class SiteSetting extends Model
{
    public const DEFAULT_PRIMARY = '#B45309';

    public const DEFAULT_ACCENT = '#1C1917';

    public const HEX_PATTERN = '/^#[0-9A-Fa-f]{6}$/';

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
