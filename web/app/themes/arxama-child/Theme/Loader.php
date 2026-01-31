<?php

declare(strict_types=1);

namespace App\Theme;

use function Safe\file_get_contents;

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

final class Loader
{
    public static function init(): void
    {
        add_action('wp_footer', [self::class, 'addLoaderScript']);
        //  styles du loader
        add_filter('body_class', [self::class, 'addLoaderBodyClass']);
        add_action('wp_head', [self::class, 'addLoaderStyles'], 0);
    }

    public static function addLoaderStyles(): void
    {
        $loader_css_path = get_stylesheet_directory() . '/Assets/styles/loader/loader.css';
        if (file_exists($loader_css_path)) {
            echo '<style type="text/css">';
            echo file_get_contents($loader_css_path);
            echo '</style>';
        }
    }

    public static function addLoaderScript(): void
    {
        $loader_js_path = get_stylesheet_directory() . '/Assets/scripts/loader/loader.js';
        if (file_exists($loader_js_path)) {
            wp_enqueue_script('loader_script', get_stylesheet_directory_uri() . '/Assets/scripts/loader/loader.js', ['jquery'], false, true);
        }
    }

    /**
     * @param  array<string>  $classes
     * @return array<string>
     */
    public static function addLoaderBodyClass(array $classes): array
    {
        $classes[] = 'js_loading_page';

        return $classes;
    }
}
