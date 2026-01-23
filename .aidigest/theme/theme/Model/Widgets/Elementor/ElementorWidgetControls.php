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
