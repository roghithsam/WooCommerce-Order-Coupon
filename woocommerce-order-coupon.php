<?php
/**
 * Plugin Name: Woocommerce Order Coupon
 * Description: Generate and send unique coupons from the WooCommerce order page.
 * Version: 1.0.0
 * Author: Roghithsam
 * Text Domain: wc-order-coupon
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register meta box
add_action('add_meta_boxes', 'wcoc_send_coupon_meta_box', 30);
function wcoc_send_coupon_meta_box($post_type) {
    // Check if we're on the 'shop_order' post type or equivalent
    if ($post_type === wc_get_page_screen_id('shop-order') || $post_type === 'shop_order') {
        add_meta_box(
            'wcoc_order_coupon',
            __('Order Coupon', 'wc-order-coupon'),
            'wcoc_add_send_coupon_form',
            $post_type,
            'side',
            'default'
        );
    }
}


// Display meta box content
function wcoc_add_send_coupon_form($order) {
    $order_id = $order->get_id();
    $billing_email = $order->get_billing_email();
    $coupon_code = get_post_meta($order_id, '_generated_coupon_code', true);
    echo '<style>#wcoc-generated-coupon{padding:3px 5px;border:2px dashed #2d696c;width:max-content;}</style>';

    if ($coupon_code) {
        echo '<div class="send-coupon-wrapper" id="send-coupon-wrapper">
                <h3>' . __('Generated Coupon', 'wc-order-coupon') . '</h3>
                <p id="wcoc-generated-coupon"><strong>' . esc_html($coupon_code) . '</strong></p>
              </div>';
    } else {
        $random_code = wcoc_generate_random_code();
        ?>
        <div class="send-coupon-wrapper" id="send-coupon-wrapper">
            <h3><?php _e('Send Coupon', 'wc-order-coupon'); ?></h3>
            <form id="wcoc_send_coupon_form">
                <input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>" />
                <input type="hidden" name="billing_email" value="<?php echo esc_attr($billing_email); ?>" />
                <p>
                    <label for="coupon_code"><?php _e('Coupon Code', 'wc-order-coupon'); ?></label>
                    <input type="text" name="coupon_code" id="coupon_code" value="<?php echo esc_attr($random_code . $order_id); ?>" />
                </p>
                <p>
                    <label for="discount_type"><?php _e('Discount Type', 'wc-order-coupon'); ?></label><br>
                    <select name="discount_type" id="discount_type">
                        <option value="fixed_cart"><?php _e('Fixed Cart Discount', 'wc-order-coupon'); ?></option>
                        <option value="percent"><?php _e('Percentage Discount', 'wc-order-coupon'); ?></option>
                    </select>
                </p>
                <p>
                    <label for="coupon_amount"><?php _e('Coupon Amount', 'wc-order-coupon'); ?></label>
                    <input type="number" name="coupon_amount" id="coupon_amount" value="10" />
                </p>
                <p>
                    <label for="expiry_date"><?php _e('Expiry Date', 'wc-order-coupon'); ?></label><br>
                    <input type="date" name="expiry_date" id="expiry_date" value="<?php echo esc_attr(date('Y-m-d', strtotime('+30 days'))); ?>" />
                </p>
                <p>
                    <button type="button" class="button" id="wcoc_send_coupon_button"><?php _e('Send Coupon', 'wc-order-coupon'); ?></button>
                </p>
            </form>
        </div>
        <?php
    }
}

// Enqueue admin script
add_action('admin_enqueue_scripts', 'wcoc_enqueue_send_coupon_script');
function wcoc_enqueue_send_coupon_script($hook) {
	
  /*  if ('post.php' !== $hook || 'shop_order' !== get_post_type()) {
        return;
    }*/

   wp_enqueue_script('wcoc-send-coupon-script', plugin_dir_url(__FILE__) . 'js/wcoc-order-send-coupon.js', array('jquery'), null, true);


    // Localize script to pass AJAX URL and nonce
    wp_localize_script('wcoc-send-coupon-script', 'wcoc_send_coupon_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wcoc_send_coupon_nonce')
    ));
}


// Handle AJAX request to generate and send coupon
add_action('wp_ajax_wcoc_send_coupon', 'wcoc_send_coupon_callback');
function wcoc_send_coupon_callback() {
    if (!check_ajax_referer('wcoc_send_coupon_nonce', 'security', false)) {
        wp_send_json_error(['message' => __('Security check failed.', 'wc-order-coupon')]);
    }

    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
    $coupon_amount = isset($_POST['coupon_amount']) ? floatval($_POST['coupon_amount']) : 0;
    $discount_type = isset($_POST['discount_type']) ? sanitize_text_field($_POST['discount_type']) : '';
    $expiry_date = isset($_POST['expiry_date']) ? sanitize_text_field($_POST['expiry_date']) : '';
    $billing_email = isset($_POST['billing_email']) ? sanitize_email($_POST['billing_email']) : '';

    $order = wc_get_order($order_id);
    if (!$order) {
        wp_send_json_error(['message' => __('Order not found!', 'wc-order-coupon')]);
    }

    if (get_post_meta($order_id, '_generated_coupon_code', true)) {
        wp_send_json_error(['message' => __('Coupon has already been generated for this order!', 'wc-order-coupon')]);
    }

    // Create and configure the coupon
    $coupon_id = wp_insert_post([
        'post_title'  => $coupon_code,
        'post_content' => 'For Successfully purchased Order: #'.$order_id,
        'post_excerpt' => 'For Successfully purchased Order: #'.$order_id,
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
        'post_type'   => 'shop_coupon'
    ]);

    if ($coupon_id) {
        update_post_meta($coupon_id, 'discount_type', $discount_type);
        update_post_meta($coupon_id, 'coupon_amount', $coupon_amount);
        update_post_meta($coupon_id, 'individual_use', 'yes');
        update_post_meta($coupon_id, 'usage_limit', 1);
        update_post_meta($coupon_id, 'expiry_date', $expiry_date);
        update_post_meta($coupon_id, 'apply_before_tax', 'yes');
        update_post_meta($coupon_id, 'customer_email', $billing_email);
        update_post_meta($order_id, '_generated_coupon_code', $coupon_code);

        // Send email
        $mailer = WC()->mailer();
        $mails = $mailer->get_emails();
        foreach ($mails as $mail) {
            if ($mail->id === 'wc_email_coupon_code') {
                $mail->trigger($order_id, $coupon_code);
            }
        }
        wp_send_json_success(['message' => __('Coupon has been sent!', 'wc-order-coupon')]);
    } else {
        wp_send_json_error(['message' => __('Failed to create coupon.', 'wc-order-coupon')]);
    }
	wp_send_json_error(['message' => __('Failed.', 'wc-order-coupon')]);
}

function add_wc_email_coupon_code($email_classes) {
    require_once plugin_dir_path(__FILE__) . 'templates/emails/WC_Email_Coupon_Code.php';
    $email_classes['WC_Email_Coupon_Code'] = new WC_Email_Coupon_Code();
    return $email_classes;
}

add_filter('woocommerce_email_classes', 'add_wc_email_coupon_code');


//add_action('after_delete_post', 'wcoc_generate_coupon_delete', 10, 2); 
//add_action('woocommerce_shop_coupon_post_delete', 'wcoc_generate_coupon_delete', 10, 1);
function wcoc_generated_coupon_delete($post_id, $post) {
    // For a specific post type, 'shop_coupon'
    if ('shop_coupon' !== $post->post_type) {
        return;
    }
    $coupon_id = $post->post_title;
    $orders_with_coupon = get_posts([
        'post_type'      => 'shop_order_placehold',
        'meta_key'       => '_generated_coupon_code',
        'meta_value'     => $coupon_id,
        'posts_per_page' => -1
    ]);

    // Delete custom meta from each order
    foreach ($orders_with_coupon as $order) {
        delete_post_meta($order->ID, '_generated_coupon_code');
    }
}


// Generate random code
function wcoc_generate_random_code($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $char_length = strlen($characters);
    $random_code = '';
    for ($i = 0; $i < $length; $i++) {
        $random_code .= $characters[mt_rand(0, $char_length - 1)];
    }
    return $random_code;
}

?>
