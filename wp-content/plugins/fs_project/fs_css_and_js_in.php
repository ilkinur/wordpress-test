<?php 

function fs_project_enqueue_scripts() {
    wp_enqueue_script(
        'fs-project-script',                                 
        plugin_dir_url(__FILE__) . 'assets/fs_project.js', 
        array('jquery'),
        '1.0',
        true
    );

    wp_localize_script('fs-project-script', 'fs_project_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

add_action('admin_enqueue_scripts', 'fs_project_enqueue_scripts');

function fs_project_enqueue_styles() {
    wp_enqueue_style('fs-project-style', plugin_dir_url(__FILE__) . 'assets/style.css', array(),'1.0','all');
}

add_action('admin_enqueue_scripts', 'fs_project_enqueue_styles');