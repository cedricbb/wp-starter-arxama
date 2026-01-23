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
