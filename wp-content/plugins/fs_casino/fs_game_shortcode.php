<?php 

function fs_game_shortcode() {
    if (is_user_logged_in()) {
        return '<div id="fs-game-root"></div>';
    } else {
        wp_redirect(wp_login_url(get_permalink()));
        exit;
    }
    
}
add_shortcode('fs-game', 'fs_game_shortcode');

function fs_game_rest_api() {
    register_rest_route('fs-game/v1', '/play', array(
        'methods' => 'POST',
        'callback' => 'fs_game_play',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));

    register_rest_route('fs-game/v1', '/user_me', array(
        'methods' => 'GET',
        'callback' => 'fs_game_user_info',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
    ));
}
add_action('rest_api_init', 'fs_game_rest_api');

function fs_game_user_info(){
    $user_id = get_current_user_id();

    $balance = get_user_meta($user_id, 'balance', true);

    return new WP_REST_Response(array('balance' => $balance), 200);
    
}

function fs_game_play($request) {
    $user_id = get_current_user_id();
    $balance = get_user_meta($user_id, 'balance', true);
    $bet_amount = $request->get_param('bet_amount');
    
    if ($balance < $bet_amount) {
        return new WP_REST_Response(array('message' => __('Insufficient balance.', 'fs-casino')), 400);
    }

    $win_chance = 0.4;
    $is_win = mt_rand(0, 100) / 100 < $win_chance;

    if ($is_win) {
        $balance += $bet_amount;
        $message = __('Congratulations! You won.','fs-casino');
    } else {
        $balance -= $bet_amount;
        $message = __('You lose.','fs-casino');
    }

    update_user_meta($user_id, 'balance', $balance);

    return new WP_REST_Response(array('message' => $message, 'balance' => $balance), 200);
}


function enqueue_fs_game_script() {
    wp_enqueue_script(
        'fs-game',
        plugin_dir_url(__FILE__) . '/assets/js/dist/fs-game.bundle.js',
        array('wp-element'),
        null,
        true
    );
    wp_localize_script('fs-game', 'wpApiSettings', array(
        'nonce' => wp_create_nonce('wp_rest')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_fs_game_script');