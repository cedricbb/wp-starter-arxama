<?php

declare(strict_types=1);

namespace App\Theme;

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

final class Filters
{
    public static function init(): void
    {
        add_filter('upload_mimes', [self::class, 'addCustomMimeTypes']);
    }

    /**
     * @param  array<string, string>  $mimes
     * @return array<string, string>
     */
    public static function addCustomMimeTypes(array $mimes): array
    {
        /* XML files must start with <?xml version="1.0" encoding="utf-8"?> */
        $mimes['svg'] = 'image/svg xml';

        return $mimes;
    }
}
