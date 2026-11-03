<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

final class FaIcon
{
    public static function make(string $icon): HtmlString
    {
        return new HtmlString('<i class="fas '.e($icon).'" aria-hidden="true"></i>');
    }
}
