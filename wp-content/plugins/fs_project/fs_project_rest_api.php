<?php

function fs_project_get_logs() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fs_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY share_date DESC");

    wp_send_json($logs);
}
add_action('wp_ajax_fs_get_logs', 'fs_project_get_logs');
add_action('wp_ajax_nopriv_fs_get_logs', 'fs_project_get_logs');

function fs_project_delete_user_log() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fs_logs';
    $log_id = intval($_POST['log_id']);

    if ($wpdb->delete($table_name, ['id' => $log_id])) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Could not delete log');
    }
}
add_action('wp_ajax_fs_delete_user_log', 'fs_project_delete_user_log');
add_action('wp_ajax_nopriv_fs_delete_user_log', 'fs_project_delete_user_log');