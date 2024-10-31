<?php 

/*
 * Plugin Name:       FS Casino
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics FS Casino with this plugin.
 * Version:           1.0
 * Requires PHP:      7.2
 * Author:            Ilkinur Keremli
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       fs-casino
 * Domain Path:       /languages
 */

 
 defined( 'ABSPATH' ) or exit;
    
 register_activation_hook( __FILE__, 'fs_casino_create' );

 function fs_casino_create() {
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
        include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugin_slug = 'woocommerce';
        $api = plugins_api('plugin_information', array('slug' => $plugin_slug, 'fields' => array('sections' => false)));

        if (!is_wp_error($api)) {
            $upgrader = new Plugin_Upgrader();
            $upgrader->install($api->download_link);

            activate_plugin('woocommerce/woocommerce.php');
        }
    }

    fs_create_balance_product();

 }

 function fs_create_balance_product() {
    if (get_option('fs_product_id')) return;

    $product = new WC_Product_Simple();
    $product->set_name('FS Product');
    $product->set_price(0);
    $product->set_regular_price(0);
    $product->set_catalog_visibility('hidden');
    $product->save();

    update_option('fs_product_id', $product->get_id());
}

 register_deactivation_hook(__FILE__, 'fs_casino_deactivate');

 function fs_casino_deactivate() {
    $timestamp = wp_next_scheduled('action_scheduler_run_queue');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'action_scheduler_run_queue');
    }
 }

 function fs_casino_load_textdomain() {
    load_plugin_textdomain('fs-casino', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

add_action('init', 'fs_casino_load_textdomain');
function fs_casino_login_link() {
    if (!is_user_logged_in()) {
        $login_url = wp_login_url();
        return '<a href="' . esc_url($login_url) . '">'.__('Login', 'fs-casino').'</a>';
    } else {
        return '';
    }
}
add_shortcode('fs-casino-login', 'fs_casino_login_link');

function fs_casino_register_link() {
    if (!is_user_logged_in()) {
        $register_url = wp_registration_url();
        return '<a href="' . esc_url($register_url) . '">'.__('Register', 'fs-casino').'</a>';
    } else {
        return '';
    }
}
add_shortcode('fs-casino-register', 'fs_casino_register_link');


function custom_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles) && in_array('subscriber', $user->roles)) {
        return home_url('/casino'); 
    }
    return $redirect_to; 
}
add_filter('login_redirect', 'custom_login_redirect', 10, 3);


function add_initial_balance_to_new_user($user_id) {
    $initial_balance = 10;
    update_user_meta($user_id, 'balance', $initial_balance); 
}
add_action('user_register', 'add_initial_balance_to_new_user');


require_once plugin_dir_path( __FILE__ ) .'fs_game_shortcode.php';

require_once plugin_dir_path( __FILE__ ) .'fs_casino_cron_job.php';

require_once plugin_dir_path( __FILE__ ) .'fs_casino_balance_works.php';