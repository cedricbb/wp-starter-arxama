<?php
/*
Plugin Name: Arxama Guard
*/

if (defined('WP_ENV') && WP_ENV === 'production') {
    add_filter('auto_update_plugin', '__return_false');
    add_filter('auto_update_theme', '__return_false');
}
