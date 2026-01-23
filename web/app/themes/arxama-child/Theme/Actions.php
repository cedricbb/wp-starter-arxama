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
