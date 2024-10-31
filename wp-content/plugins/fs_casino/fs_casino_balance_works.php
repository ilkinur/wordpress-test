<?php 

function fs_balance_topup_button() {
    if (is_user_logged_in()) {
        return '<button id="fs-topup-button">'.__('The balance increases', 'fs-casino').'</button>
                <input type="number" id="fs-topup-amount" placeholder="'.__('Enter the amount', 'fs-casino').'">
                <script>
                    document.getElementById("fs-topup-button").onclick = function() {
                        let amount = document.getElementById("fs-topup-amount").value;
                        if (amount > 0) {
                            window.location.href = "' . esc_url(add_query_arg('topup_amount', '', wc_get_checkout_url())) . '" + amount;
                        }
                    }
                </script>';
    }
}
add_shortcode('fs_balance_topup', 'fs_balance_topup_button');

function fs_add_balance_topup_to_cart() {
    if (!is_user_logged_in() || !isset($_GET['topup_amount'])) return;

    WC()->cart->empty_cart(); 
    
    $product_id = get_option('fs_product_id');
    $amount = (float) $_GET['topup_amount'];

    if ($amount > 0 && $product_id) {
        $user = wp_get_current_user();
        $product_name = "Balans artırılması - {$user->user_login}";

        WC()->cart->add_to_cart($product_id, 1, '', '', array(
            'product_name' => $product_name,
            'custom_price' => $amount,
        ));
    }
}
add_action('template_redirect', 'fs_add_balance_topup_to_cart');

function fs_adjust_product_price($cart_object) {
    foreach ($cart_object->get_cart() as $cart_item) {
        if (isset($cart_item['custom_price'])) {
            $cart_item['data']->set_price($cart_item['custom_price']);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'fs_adjust_product_price');

function fs_update_user_balance($order_id) {
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();

    if ($user_id) {
        $balance = (float) get_user_meta($user_id, 'balance', true);
        foreach ($order->get_items() as $item) {
            if ($item->get_name() == "FS Product") {
                $balance += $item->get_total(); 
            }
        }
        update_user_meta($user_id, 'balance', $balance);
    }
}
add_action('woocommerce_payment_complete', 'fs_update_user_balance');