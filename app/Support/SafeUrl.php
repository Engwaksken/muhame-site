<?php

namespace App\Support;

/** Allows only http(s) URLs, site-relative paths and #anchors in admin-entered links. */
class SafeUrl
{
    public static function clean(mixed $url, bool $allowRelative = true): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        $isWebUrl = in_array($scheme, ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL);
        $isRelativePath = $allowRelative && str_starts_with($url, '/') && ! str_starts_with($url, '//');
        $isAnchor = $allowRelative && str_starts_with($url, '#');
        $isMail = str_starts_with($url, 'mailto:') && filter_var(substr($url, 7), FILTER_VALIDATE_EMAIL);

        return ($isWebUrl || $isRelativePath || $isAnchor || $isMail) ? $url : null;
    }

    public static function isExternal(string $url): bool
    {
        return str_starts_with($url, 'http') && parse_url($url, PHP_URL_HOST) !== parse_url(config('app.url'), PHP_URL_HOST);
    }
}
