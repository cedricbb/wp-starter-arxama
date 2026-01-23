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
