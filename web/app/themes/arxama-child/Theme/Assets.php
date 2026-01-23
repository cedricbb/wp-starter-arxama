<?php

declare(strict_types=1);

namespace App\Theme;

use function Env\env;

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
        wp_enqueue_script('nom-du-theme_script', env('THEME_PATH_URI').'/Assets/dist/js/scripts.min.js', ['jquery']);
        wp_localize_script('nom-du-theme_script', 'directory_uri', ['stylesheet_directory_uri' => env('THEME_PATH_URI')]);
        wp_localize_script('nom-du-theme_script', 'ajaxurl', [admin_url('admin-ajax.php')]);
    }

    public static function addStyles(): void
    {
        wp_enqueue_style('nom-du-theme_theme', env('THEME_PATH_URI').'/Assets/dist/css/styles.min.css', [], wp_get_theme()->get('Version'), 'all');
    }
}
