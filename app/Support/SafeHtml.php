<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/** Sanitizes admin-authored rich text before it is rendered on the public site. */
class SafeHtml
{
    public static function clean(?string $html): string
    {
        $sanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig)->allowSafeElements()->allowRelativeLinks(),
        );

        return $sanitizer->sanitize($html ?? '');
    }
}
