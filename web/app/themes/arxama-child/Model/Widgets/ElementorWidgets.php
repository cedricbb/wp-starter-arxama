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

        // Utiliser le hook correct pour ajouter des contrôles aux widgets existants
        // 'elementor/element/before_section_end' est un hook dynamique, mais pour enregistrer des contrôles globaux ou modifier des widgets,
        // il vaut mieux s'assurer qu'Elementor est chargé.
        // Cependant, addControlsToWidgets utilise des hooks dynamiques, donc on peut l'appeler ici ou sur 'elementor/init'

        add_action('elementor/init', [self::class, 'addControlsToWidgets']);
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

        // Vérifier si Elementor est actif et si le gestionnaire de widgets est disponible
        if (did_action('elementor/loaded')) {
             Plugin::instance()->widgets_manager->register(new $registerClass);
        }
    }

    public static function addControlsToWidgets(): void
    {
        // Vérification de sécurité pour s'assurer qu'Elementor est bien chargé
        if (!did_action('elementor/loaded')) {
            return;
        }

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

        // Le hook doit être ajouté, mais l'exécution de la closure dépendra de l'instance de Plugin
        add_action(
            'elementor/element/'.$widget.'/'.$section.'/before_section_end',
            function ($element, $args) use ($params, $name, $responsive) {
                // Double vérification à l'intérieur du hook
                if (!did_action('elementor/loaded')) {
                    return;
                }

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
