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
