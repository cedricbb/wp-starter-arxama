<?php

declare(strict_types=1);

namespace App\Theme;

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

final class Assets
{
    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'addScripts'], 30);
    }

    public static function addScripts(): void
    {
        self::addStyles();
        $theme_uri = get_stylesheet_directory_uri();

        wp_enqueue_script('arxama-child-script', $theme_uri . '/Assets/dist/js/scripts.min.js', ['jquery'], null, true);

        wp_localize_script('arxama-child-script', 'directory_uri', ['stylesheet_directory_uri' => $theme_uri]);
        wp_localize_script('arxama-child-script', 'ajaxurl', [admin_url('admin-ajax.php')]);
    }

    public static function addStyles(): void
    {
        $theme_uri = get_stylesheet_directory_uri();
        wp_enqueue_style('arxama-child-style', $theme_uri . '/Assets/dist/css/styles.min.css', [], wp_get_theme()->get('Version'), 'all');
    }
}
