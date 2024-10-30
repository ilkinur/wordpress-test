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


 }

 register_deactivation_hook(__FILE__, 'fs_casino_deactivate');

 function fs_casino_deactivate() {
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