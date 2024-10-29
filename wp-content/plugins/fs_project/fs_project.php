<?php 
/*
 * Plugin Name:       FS Project
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handle the basics FS Project with this plugin.
 * Version:           1.0
 * Requires PHP:      7.2
 * Author:            Ilkinur Keremli
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       fs-project
 * Domain Path:       /languages
 */



    defined( 'ABSPATH' ) or exit;
    
    register_activation_hook( __FILE__, 'fs_project_create_log_table' );

    function fs_project_create_log_table() {

        global $wpdb;
        $table_name = $wpdb->prefix .'fs_logs';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            login_id bigint(20) NOT NULL,
            post_title text NOT NULL,
            post_link text NOT NULL,
            share_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH ."wp-admin/includes/upgrade.php";

        dbDelta( $sql );

    }


    function fs_project_load_textdomain() {
        load_plugin_textdomain('fs-project', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    add_action('init', 'fs_project_load_textdomain');


    
    add_action( "admin_menu","fs_project_add_menu");

    function fs_project_add_menu() {
        add_menu_page('FS Logs', 'FS logs', 'manage_options', 'fs_project', 'fs_project_logs_page','dashicons-book');
        add_submenu_page('fs_project', 'Settings', 'Settings', 'manage_options','fs_project_settings','fs_project_settings_page');
    }

    function fs_project_logs_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'fs_logs';
        $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY share_date DESC");
        
        echo '<br/><br/>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-id">ID</th>
                    <th scope="col" class="manage-column column-login-id">Login ID</th>
                    <th scope="col" class="manage-column column-post-id">Post ID</th>
                    <th scope="col" class="manage-column column-title">'.__('Title', 'fs-project').'</th>
                    <th scope="col" class="manage-column column-link">Link</th>
                    <th scope="col" class="manage-column column-date">'.__('Date', 'fs-project').'</th>
                    <th scope="col" class="manage-column column-actions">'.__('Action', 'fs-project').'</th>
                </tr>
            </thead>
        <tbody>';

        foreach ($logs as $log) {
            echo "<tr id='log-{$log->id}'>";
            echo "<td>{$log->id}</td>";
            echo "<td>{$log->login_id}</td>";
            echo "<td>{$log->post_id}</td>";
            echo "<td>{$log->post_title}</td>";
            echo "<td><a href='{$log->post_link}' target='_blank'>Link</a></td>";
            echo "<td>{$log->share_date}</td>";
            echo "<td><button class='button delete-log' data-id='{$log->id}'>".__('Delete', 'fs-project')."</button></td>";
            echo "</tr>";
        }
        
        echo '</tbody></table>';
    }

    function fs_project_settings_page() {
        $categories = get_categories(); 
        $selected_categories = get_option('fs_project_categories', []);
     
        echo '<form method="post" action="options.php">';
        settings_fields('fs_project_options');
        do_settings_sections('fs_project');
        
        echo '<h2>' . __('Select Categories', 'fs-project') . '</h2>';
        echo '<table class="form-table">';
        echo '<tr valign="top">';
        echo '<th scope="row"><label for="fs_project_categories">' . __('Categories', 'fs-project') . '</label></th>';
        echo '<td>';
        echo '<select name="fs_project_categories[]" id="fs_project_categories" multiple class="regular-text">';
        foreach ($categories as $category) {
            $selected = in_array($category->term_id, $selected_categories) ? 'selected' : '';
            echo "<option value='{$category->term_id}' $selected>{$category->name}</option>";
        }
        echo '</select>';
        echo '<p class="description">' . __('Hold down the Ctrl (Windows) / Command (Mac) key to select multiple categories.', 'fs-project') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        
        submit_button(__('Save Categories', 'fs-project'), 'primary');
        echo '</form>';
    }

    add_action('admin_init', 'fs_project_register_settings');

    function fs_project_register_settings() {
        register_setting('fs_project_options', 'fs_project_categories');
    }

    add_action('save_post', 'fs_project_check_post', 10, 2);

    function fs_project_check_post($post_id, $post) {
        if (wp_is_post_revision($post_id) || 'publish' !== $post->post_status) return;

        
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'fs_logs', [
            'post_id'       => $post_id,
            'login_id'      => get_current_user_id(),
            'post_title'    => get_the_title($post_id),
            'post_link'     => get_permalink($post_id),
            'share_date'    => current_time('mysql')
        ]);

    }


    function fs_project_delete_log() {
        global $wpdb;
        $log_id = intval($_POST['log_id']);
        $wpdb->delete($wpdb->prefix . 'fs_logs', ['id' => $log_id]);
        wp_send_json_success();
    }

    add_action('wp_ajax_fs_project_delete_log', 'fs_project_delete_log');

    require_once plugin_dir_path(__FILE__) . 'fs_css_and_js_in.php';

    register_deactivation_hook(__FILE__, 'fs_project_deactivate');

    function fs_project_deactivate() {
    }

    require_once plugin_dir_path(__FILE__) . 'fs_shortcode.php';

    require_once plugin_dir_path(__FILE__) . 'fs_project_rest_api.php';