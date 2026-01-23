# theme/functions.php

```php
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

```

# theme/Model/Admin.php

```php
<?php

declare(strict_types=1);

namespace App\Model;

use function Safe\preg_replace;

final class Admin
{
    private const ADMIN_COLUMNS = [
        'post' => [
            'cb' => [
                'thumbnail' => ['title' => 'Image', 'type' => 'thumbnail', 'style' => '<style>th#thumbnail { width:50px; }</style>'],
            ],
        ],
        'page' => [
            'cb' => [
                'thumbnail' => ['title' => 'Image', 'type' => 'thumbnail', 'style' => '<style>th#thumbnail { width:50px; }</style>'],
            ],
        ],
        'custom' => [
            'cb' => [
                'thumbnail' => ['title' => 'Image', 'type' => 'thumbnail', 'style' => '<style>th#thumbnail { width:50px; }</style>'],
            ],
        ],
    ];

    public static function init(): void
    {
        add_action('admin_init', [self::class, 'admin_init']);
    }

    public static function admin_init(): void
    {
        add_filter('manage_post_posts_columns', [self::class, 'createAdminColumnPosts']);
        add_action('manage_post_posts_custom_column', [self::class, 'renderPostsAdminColumns'], 10, 2);
        add_filter('manage_page_posts_columns', [self::class, 'createAdminColumnPages']);
        add_action('manage_page_posts_custom_column', [self::class, 'renderPagesAdminColumns'], 10, 2);
        add_filter('manage_custom_posts_columns', [self::class, 'createAdminColumnPosts']);
        add_action('manage_custom_posts_custom_column', [self::class, 'renderPostsAdminColumns'], 10, 2);
    }

    /**
     * @param  string[]  $columns
     * @return string[]
     */
    public static function createAdminColumnPosts(array $columns): array
    {
        $type = get_post_type();
        if (empty($type) || empty(self::ADMIN_COLUMNS[$type])) {
            return $columns;
        }
        $columns = self::insertPostTypeColumns($columns, self::ADMIN_COLUMNS[$type]);

        return $columns;
    }

    /**
     * @param  string[]  $columns
     * @return string[]
     */
    public static function createAdminColumnPages(array $columns): array
    {
        $columns = self::insertPostTypeColumns($columns, self::ADMIN_COLUMNS['page']);

        return $columns;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, string>  $new_columns
     * @return array<int, string>
     */
    public static function insertIntoColumnsAfterIndex(array $columns, string $index, array $new_columns): array
    {
        $split = array_search($index, array_keys($columns));
        $before = array_slice($columns, 0, $split + 1);
        $after = array_slice($columns, $split + 1);

        return array_merge($before, $new_columns, $after);
    }

    public static function renderPostsAdminColumns(string $column_key, int $post_id): void
    {
        $type = get_post_type();
        if (empty($type) || empty(self::ADMIN_COLUMNS[$type])) {
            return;
        }
        // On recherche les settings du champ
        $settings = [];
        foreach (self::ADMIN_COLUMNS[$type] as $index => $cols) {
            foreach ($cols as $idcol => $col) {
                if ($idcol !== $column_key) {
                    continue;
                }
                $settings = $col;
                break;
            }
        }
        if (empty($settings)) {
            return;
        }

        self::renderAdminColumnContent($column_key, $post_id, $settings);
    }

    public static function renderPagesAdminColumns(string $column_key, int $post_id): void
    {
        // On recherche les settings du champ
        $settings = [];
        foreach (self::ADMIN_COLUMNS['page'] as $cols) {
            foreach ($cols as $idcol => $col) {
                if ($idcol !== $column_key) {
                    continue;
                }
                $settings = $col;
                break;
            }
        }
        if (empty($settings)) {
            return;
        }

        self::renderAdminColumnContent($column_key, $post_id, $settings);
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<string, mixed>  $settings
     * @return array<int, string>
     */
    private static function insertPostTypeColumns(array $columns, array $settings): array
    {
        foreach ($settings as $index => $cols) {
            if (empty($cols)) {
                continue;
            }
            foreach ($cols as $idcol => $col) {
                // Le titre et éventuellement un string de style inline de type <style>th#thumbnail { width:50px; }</style>
                $col_title = ($col['title'] ?? $idcol).($col['style'] ?? '');
                $new_column[$idcol] = $col_title;
                $columns = self::insertIntoColumnsAfterIndex($columns, $index, $new_column);
                // On décale le prochain index après celui qu'on crée
                $index = $idcol;
            }
        }

        return $columns;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private static function renderAdminColumnContent(string $column_key, int $post_id, array $settings): void
    {
        $type = $settings['type'] ?? null;
        if (empty($type)) {
            return;
        }
        if ($type === 'acf') {
            echo get_field($column_key, $post_id, true);

            return;
        }
        if (str_starts_with($type, 'acf.')) {
            $acf = preg_replace('/acf\./', '', $type);
            if (empty($acf)) {
                return;
            }
            $value = get_field($acf, $post_id, true);
            if (empty($value)) {
                return;
            }
            echo $value;

            return;
        }
        if ($type === 'thumbnail') {
            $image = get_the_post_thumbnail_url($post_id, 'thumbnail');
            if (empty($image)) {
                echo '<pre>'.print_r($image, true).'</pre>';

                return;
            }
            echo "<img src='$image' height='50' width='50'/>";

            return;
        }

        echo '<pre style="font-size:9px; line-height=9px;">'.print_r(get_field($column_key, $post_id, true), true).'</pre>';
    }
}

```

# theme/Model/CustomPostTypes.php

```php
<?php

declare(strict_types=1);

namespace App\Model;

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

final class CustomPostTypes
{
    public static function init(): void
    {
        self::registerPostType('custom', 'custom', 'Custom', 'Customs', 4, 'dashicons-format-video', 'f', ['title', 'editor', 'revisions', 'author', 'thumbnail'], ['taxonomies' => ['category']]);
    }

    /**
     * @param  array<string>  $supports
     * @param  array<string, mixed>  $options
     */
    public static function registerPostType(?string $post_type_name = null, ?string $slug = null, ?string $label = null, ?string $labels = null, ?int $menu_position = null, string $menu_icon = 'dashicons-lightbulb', ?string $genre = null, array $supports = [], array $options = []): void
    {
        if (! $post_type_name || ! $slug || ! $label || ! $labels || ! $menu_position || ! $menu_icon || ! $genre) {
            return;
        }

        $labelstexts = [
            'name' => $labels,
            'singular_name' => $label,
            'menu_name' => $labels,
            'name_admin_bar' => $label,
            'add_new' => 'Ajouter',
            'add_new_item' => ($genre == 'f') ? 'Ajouter nouvelle '.$label : 'Ajouter nouveau '.$label,
            'new_item' => ($genre == 'f') ? 'Nouvelle '.$label : 'Nouveau '.$label,
            'edit_item' => 'Éditer '.$label,
            'view_item' => 'Voir '.$label,
            'all_items' => ($genre == 'f') ? 'Toutes les '.$labels : 'Tous les '.$labels,
        ];
        $post_type_args = [
            'labels' => $labelstexts,
            'show_in_rest' => true, // active gutenberg
            'public' => true,
            'revisions' => true,
            'show_in_menu' => true,
            'show_in_admin_bar' => true,
            'show_admin_column' => true,
            'menu_position' => $menu_position,
            'menu_icon' => $menu_icon,
            'has_archive' => $options['has_archive'] ?? true,
            'can_export' => true,
            'rewrite' => [
                'slug' => $slug,
            ],
            'taxonomies' => $options['taxonomies'] ?? [],
        ];
        // $supports ne peut pas être vide
        if (count($supports) > 0) {
            $post_type_args['supports'] = $supports;
        } else {
            $post_type_args['supports'] = ['title', 'editor'];
        }

        register_post_type($post_type_name, $post_type_args);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    public static function registerPostTypeTaxonomy(?string $name = null, ?string $post_type = null, ?string $plural_name = null, ?string $singular_name = null, ?string $slug = null, string $genre = 'm', bool $hierarchical = true, array $params = []): void
    {
        if (is_null($name) || is_null($plural_name) || is_null($singular_name) || is_null($slug) || is_null($post_type)) {
            return;
        }
        //  Textes
        $labels = [
            'name' => $plural_name,
            'singular_name' => $singular_name,

            'all_items' => ($genre == 'f') ? 'Toutes les '.$plural_name : 'Tous les '.$plural_name,
            'edit_item' => ($genre == 'f') ? 'Éditer la '.$singular_name : 'Éditer le '.$singular_name,
            'view_item' => ($genre == 'f') ? 'Voir la '.$singular_name : 'Voir le '.$singular_name,
            'update_item' => ($genre == 'f') ? 'Mettre à jour la '.$singular_name : 'Mettre à jour le '.$singular_name,
            'add_new_item' => ($genre == 'f') ? 'Ajouter une '.$singular_name : 'Ajouter un '.$singular_name,
            'new_item_name' => ($genre == 'f') ? 'Nouvelle '.$singular_name : 'Nouveau '.$singular_name,
            'search_items' => 'Rechercher parmi les '.$plural_name,
            'popular_items' => $plural_name.' les plus utilisées',
        ];
        //  Params de la taxonomie
        $args = [
            'label' => $plural_name,
            'labels' => $labels,
            'public' => $params['public'] ?? true,
            'show_in_rest' => $params['show_in_rest'] ?? true,
            'hierarchical' => $hierarchical,
            'show_admin_column' => $params['show_admin_column'] ?? true,
            'show_in_nav_menus' => $params['show_in_nav_menus'] ?? true,
            'rewrite' => [
                'slug' => $slug,
                'with_front' => true,
            ],
        ];

        register_taxonomy($name, $post_type, $args);
    }
}

```

# theme/Model/Widgets/Assets/scripts/WidgetName.js

```js
jQuery(document).ready(function () {
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/WidgetName.default', function ($scope) {
            //
        });
    });
});
```

# theme/Model/Widgets/Assets/styles/WidgetName.css

```css

```

# theme/Model/Widgets/Elementor/ElementorWidgetControls.php

```php
<?php

declare(strict_types=1);

namespace App\Model\Widgets\Elementor;

use Elementor\Controls_Manager;

final class ElementorWidgetControls
{
    /** @var array<string, mixed> */
    public const WIDGET_PARAMS = [
        'tabs' => [
            [
                'section' => 'section_tabs',
                'name' => 'tab_style',
                'config' => [
                    'label' => 'Tabs Style',
                    'type' => Controls_Manager::SELECT,
                    'options' => [
                        '' => 'Normal',
                        'dots-lines' => 'Dots & Lines',
                    ],
                    'prefix_class' => 'tab-style-',
                    'default' => '',
                ],
            ],
            [
                'section' => 'section_tabs',
                'name' => 'tabs',
                'config' => [
                    'fields' => [
                        'name' => 'tab_svg',
                        'label' => 'Image SVG',
                        'description' => 'Images au format SVG seulement',
                        'type' => Controls_Manager::MEDIA,
                        'media_types' => ['svg'],
                    ],
                ],
            ],
        ],
        'image' => [
            [
                'section' => 'section_image',
                'name' => 'image_border_style',
                'config' => [
                    'label' => "Style Bords d'Image",
                    'type' => Controls_Manager::SELECT,
                    'options' => [
                        '' => 'Normal',
                        'offset' => 'Bords Décalés',
                    ],
                    'prefix_class' => 'image-border-style-',
                    'default' => '',
                    'separator' => 'before',
                ],
            ],
            [
                'section' => 'section_image',
                'name' => 'image_border_zindex',
                'config' => [
                    'label' => 'Bord Décalés : Z-Index',
                    'type' => Controls_Manager::NUMBER,
                    'max' => 100,
                    'selectors' => [
                        '{{WRAPPER}}.image-border-style-offset .elementor-widget-container::after' => 'z-index:{{VALUE}};',
                    ],
                    'condition' => [
                        'image_border_style!' => '',
                    ],
                ],
            ],
            [
                'section' => 'section_image',
                'name' => 'image_border_border_radius',
                'responsive' => true,
                'config' => [
                    'label' => 'Bord Décalés : Rayon',
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', 'em', '%'],
                    'selectors' => [
                        '{{WRAPPER}}.image-border-style-offset .elementor-widget-container::after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}}.image-border-style-offset .elementor-widget-container img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                    'condition' => [
                        'image_border_style!' => '',
                    ],
                ],
            ],
            [
                'section' => 'section_image',
                'name' => 'image_border_translatex',
                'responsive' => true,
                'config' => [
                    'label' => 'Décalage X',
                    'type' => Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'min' => -100,
                            'max' => 100,
                        ],
                    ],
                    'devices' => ['desktop', 'tablet', 'mobile'],
                    'selectors' => [
                        '{{WRAPPER}}.image-border-style-offset .elementor-widget-container::after' => 'left: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'image_border_style!' => '',
                    ],
                ],
            ],
            [
                'section' => 'section_image',
                'name' => 'image_border_translatey',
                'responsive' => true,
                'config' => [
                    'label' => 'Décalage Y',
                    'type' => Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'min' => -100,
                            'max' => 100,
                        ],
                    ],
                    'devices' => ['desktop', 'tablet', 'mobile'],
                    'selectors' => [
                        '{{WRAPPER}}.image-border-style-offset .elementor-widget-container::after' => 'top: {{SIZE}}{{UNIT}};',
                    ],
                    'condition' => [
                        'image_border_style!' => '',
                    ],
                ],
            ],
            [
                'section' => 'section_image',
                'name' => 'image_border_bordercolor',
                'config' => [
                    'label' => 'Couleur',
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}}.image-border-style-offset .elementor-widget-container::after' => 'border-color: {{VALUE}}!important;',
                    ],
                    'condition' => [
                        'image_border_style!' => '',
                    ],
                ],
            ],
        ],
        'button' => [
            [
                'section' => 'section_button',
                'name' => 'button_wrapper_width',
                'responsive' => true,
                'config' => [
                    'label' => 'Largeur custom',
                    'type' => Controls_Manager::SLIDER,
                    'size_units' => ['px', '%'],
                    'range' => [
                        'px' => [
                            'min' => 0,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-button-wrapper' => 'width:auto; margin:auto;',
                        '{{WRAPPER}}.elementor-align-center .elementor-button-wrapper' => 'width:{{SIZE}}{{UNIT}}; margin:auto',
                        '{{WRAPPER}}.elementor-align-left .elementor-button-wrapper' => 'width:{{SIZE}}{{UNIT}}; margin:0 auto 0 0',
                        '{{WRAPPER}}.elementor-align-right .elementor-button-wrapper' => 'width:{{SIZE}}{{UNIT}}; margin:0 0 0 auto',
                    ],
                    'separator' => 'before',
                    'description' => 'Valeur non prise en compte pour les boutons alignés en justify',
                ],
            ],
        ],
    ];
}

```

# theme/Model/Widgets/Elementor/WidgetName/WidgetName.php

```php
<?php

declare( strict_types=1 );

namespace App\Model\Widgets\Elementor\WidgetName;

final class WidgetName {

}
```

# theme/Model/Widgets/ElementorWidgets.php

```php
<?php

declare(strict_types=1);

namespace App\Model\Widgets;

use App\Model\Widgets\Elementor\ElementorWidgetControls;
use Elementor\Plugin;

final class ElementorWidgets
{
    public static function init(): void
    {
        add_action('after_setup_theme', [self::class, 'after_setup_theme']);
    }

    public static function after_setup_theme(): void
    {
        // add_action('elementor/widgets/register', [self::class, 'includeWidgetFiles']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueueAssets']);
        // add_action('elementor/widgets/register', [self::class, 'addControlsToWidgets']);
    }

    public static function includeWidgetFiles(): void
    {
//        self::includeWidgetFile(
//            get_stylesheet_directory().'/Model/Widgets/Elementor/WidgetSliderHome/WidgetSliderHome.php',
//            'App\Model\Widgets\Elementor\WidgetSliderHome\WidgetSliderHome'
//        );
    }

    private static function includeWidgetFile(string $widgetPath = '', string $registerClass = ''): void
    {
        if (! is_file($widgetPath)) {
            return;
        }

        if (! class_exists($registerClass)) {
            return;
        }

        Plugin::instance()->widgets_manager->register(new $registerClass);
    }

    public static function addControlsToWidgets(): void
    {
        foreach (ElementorWidgetControls::WIDGET_PARAMS as $idWidgetParam => $params) {
            foreach ($params as $param) {
                self::addControlsToWidget(
                    $idWidgetParam,
                    $param['section'],
                    $param['name'],
                    $param['config'],
                    $param['responsive'] ?? false
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private static function addControlsToWidget(string $widget, string $section, string $name, array $params, bool $responsive = false): void
    {
        if (empty($widget) || empty($section) || empty($name) || empty($params)) {
            return;
        }
        add_action(
            'elementor/element/'.$widget.'/'.$section.'/before_section_end',
            function ($element, $args) use ($params, $name, $responsive) {
                if (empty($params['fields'])) {
                    if (! $responsive) {
                        $element->add_control($name, $params);
                    } else {
                        $element->add_responsive_control($name, $params);
                    }
                } else {
                    // https://github.com/elementor/elementor/issues/8832
                    // First get the repeater control
                    $repeater_control_data = Plugin::instance()->controls_manager->get_control_from_stack(
                        $element->get_unique_name(),
                        $name
                    );
                    if (is_wp_error($repeater_control_data)) {
                        return;
                    }
                    $repeater_control_data['fields'] = array_merge(
                        $repeater_control_data['fields'],
                        [$params['fields']['name'] => $params['fields']]
                    );

                    // update the control in the stack/widget
                    $element->update_control($name, $repeater_control_data);
                }
            },
            10,
            2
        );
    }

    public static function enqueueAssets(): void
    {
        //        wp_enqueue_script('WidgetName-script', get_stylesheet_directory_uri() . '/Model/Widgets/Assets/dist/js/WidgetName.min.js');
    }
}

```

# theme/Model/Widgets/Helpers/WidgetsHelpers.php

```php
<?php

declare( strict_types=1 );


namespace Helpers;


final class WidgetsHelpers {

}
```

# theme/style.css

```css
/*
Theme Name: Arxama Child
Template: hello-elementor
Version: 1.0.0
Text Domain: arxama-child
*/
```

# theme/Theme/Actions.php

```php
<?php

declare(strict_types=1);

namespace App\Theme;

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use PHPMailer\PHPMailer\PHPMailer;

use function Env\env;

final class Actions
{
    public static function init(): void
    {
        add_action('phpmailer_init', [self::class, 'phpMailerInit']);
        add_action('init', [self::class, 'setBedrockRoutes'], 10, 0);
    }

    public static function phpMailerInit(PHPMailer $phpmailer): void
    {
        $phpmailer->Mailer = 'smtp';
        $phpmailer->Host = env('SMTP_HOST');
        $phpmailer->SMTPAuth = env('SMTP_AUTH');
        $phpmailer->Port = env('SMTP_PORT');
        $phpmailer->Username = env('SMTP_USER');
        $phpmailer->Password = env('SMTP_PASS');
        $phpmailer->SMTPSecure = env('SMTP_SECURE');
        $phpmailer->From = env('SMTP_FROM');
        $phpmailer->FromName = env('SMTP_NAME');
    }

    public static function setBedrockRoutes(): void
    {
        // Les urls siteurl et home dans la base de données wp_options doivent être sans /wp
        add_rewrite_rule('wp-.*\.php$', 'wp/$0', 'top');
        add_rewrite_rule('wp-(content|admin|includes)/.*$', 'wp/$0', 'top');
    }
}

```

# theme/Theme/Assets.php

```php
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

```

# theme/Theme/Filters.php

```php
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

```

# theme/Theme/Loader.php

```php
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
        ?>
		<style type="text/css">
			<?php
            echo file_get_contents(get_stylesheet_directory().'/Assets/styles/loader/loader.css');
        ?>
		</style>
		<?php
    }

    public static function addLoaderScript(): void
    {
        wp_enqueue_script('loader_script', get_stylesheet_directory_uri().'/Assets/scripts/loader/loader.js', ['jquery'], false, true);
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

```

