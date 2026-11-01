<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['site_name', 'tagline', 'homepage_title', 'homepage_description', 'contact_email', 'logo_path', 'navigation_links', 'social_links'])]
class SiteSetting extends Model
{
    protected function casts(): array
    {
        return [
            'navigation_links' => 'array',
            'social_links' => 'array',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], static::defaults());
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'site_name' => config('app.name', 'Muhame'),
            'tagline' => 'Stories and updates from our team.',
            'homepage_title' => 'The latest stories',
            'homepage_description' => 'Ideas, updates, and perspectives from our team.',
            'contact_email' => null,
            'logo_path' => null,
            'navigation_links' => [],
            'social_links' => [],
        ];
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

    /** @param array<mixed> $links
     * @return array<string, string>
     */
    private function safeLinks(array $links, bool $allowRelative): array
    {
        $safe = [];

        foreach ($links as $label => $url) {
            if (! is_string($label) || ! is_string($url) || trim($label) === '') {
                continue;
            }

            $url = trim($url);
            $scheme = parse_url($url, PHP_URL_SCHEME);
            $isWebUrl = in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL);
            $isRelativePath = $allowRelative && str_starts_with($url, '/') && ! str_starts_with($url, '//');
            $isAnchor = $allowRelative && str_starts_with($url, '#');

            if ($isWebUrl || $isRelativePath || $isAnchor) {
                $safe[trim($label)] = $url;
            }
        }

        return $safe;
    }
}
