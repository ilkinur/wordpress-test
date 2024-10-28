<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

global $wpdb;
$table_name = $wpdb->prefix . 'fs_logs';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

delete_option('fs_project_categories');