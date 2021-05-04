<?php
/*
   Plugin Name: WP Dev
   description: WordPress local development aid.
   Version: 0.1.0
   Author: Trevor Thompson
*/

namespace Tsquare\WPDev;

defined( 'ABSPATH' ) || die();


if ('127.0.0.1' !== gethostbyname($_SERVER['SERVER_NAME'])) {
    die('Danger! Remove WP Dev Plugin!');
}

if (isset($_GET['phpinfo'])) {
    phpinfo();
    die();
}


/**
 * Disable WordPress heartbeat for uninterrupted debugging.
 */
add_action('init', static function() {
    wp_deregister_script('heartbeat');
});

/**
 * Bypass authentication.
 */
if (function_exists('add_filter')) {
    add_filter('authenticate', static function($user) {
        if ($GLOBALS['pagenow'] !== 'wp-login.php') {
            return;
        }

        if (!$user) {
            $user = get_user_by('id', 1);
        }

        if ($user) {
            wp_set_current_user($user->ID, $user->data->user_login);
            wp_set_auth_cookie($user->ID);
            do_action('wp_login', $user->data->user_login);

            wp_safe_redirect(admin_url());
            exit;
        }
    }, 10, 1);
}

add_filter('phpmailer_init', static function($mailer) {
    $host = '127.0.0.1';
    $port = 2525;

    $connection = fsockopen('127.0.0.1', 2525);

    if (!is_resource($connection)) {
        return;
    }

    fclose($connection);

    $mailer->isSMTP();
    $mailer->SMTPAuth = true;
    $mailer->Host = $host;
    $mailer->Port = $port;
    $mailer->Username = get_site_url();
    $mailer->Password = '';
});
