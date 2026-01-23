<?php

use App\Model\Widgets\ElementorWidgets;
use App\Theme\Actions;
use App\Theme\Assets;
use App\Theme\Filters;
use App\Theme\Loader;

class WpTheme
{
    public function __construct()
    {
        // Loader
        Loader::init();
        // Init styles
        Assets::init();
        // WP filters
        Filters::init();
        // WP actions
        Actions::init();
        // Widgets Elementor
        ElementorWidgets::init();
    }
}
new WpTheme;
