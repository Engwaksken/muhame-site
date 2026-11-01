<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Media
{
    /** Public URL for an upload stored on the "public" disk (accepts a path or Filament's [uuid => path] array). */
    public static function url(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = reset($path);
        }

        if (! is_string($path) || $path === '' || str_contains($path, '..')) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
