<?php 

function fs_custom_shortcode($atts) {
    $atts = shortcode_atts([
        'style' => 'light' 
    ], $atts, 'fs-shortcode');

    $class = $atts['style'] === 'dark' ? 'fs-dark' : 'fs-light';

    global $wpdb;
    $table_name = $wpdb->prefix . 'fs_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY share_date DESC");
    

    ob_start();
    ?>
    <div class="fs-project-logs <?php echo esc_attr($class); ?>">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login ID</th>
                    <th>Post ID</th>
                    <th>Title</th>
                    <th>Link</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="fs-project-logs-body">
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('fs-shortcode', 'fs_custom_shortcode');


function fs_register_shortcode_block() {
    wp_register_script(
        'fs-shortcode-block',
        plugins_url('build/index.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
        '1.0'
    );

    wp_register_style(
        'fs-shortcode-block-editor',
        plugins_url('assets/fs-shortcode-block.css', __FILE__),
        array('wp-edit-blocks'),
        '1.0'
    );

    register_block_type('fs/shortcode-block', array(
        'editor_script' => 'fs-shortcode-block',
        'editor_style'  => 'fs-shortcode-block-editor'
    ));
}
add_action('enqueue_block_editor_assets', 'fs_register_shortcode_block');

function fs_project_enqueue_front_scripts() {
    if (is_singular()) {
        wp_enqueue_style('fs-logs-style', plugins_url('assets/fs-logs.css', __FILE__));
        wp_enqueue_script('fs-logs-script', plugins_url('assets/fs-logs.js', __FILE__), array('jquery'), '1.0', true);

        wp_localize_script('fs-logs-script', 'fsLogsData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'fs_project_enqueue_front_scripts');