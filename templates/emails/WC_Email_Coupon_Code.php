<?php

if (!class_exists('WC_Email_Coupon_Code')) {
    class WC_Email_Coupon_Code extends WC_Email {
        public function __construct() {
            $this->id = 'wc_email_coupon_code';
			$this->customer_email = true;
            $this->title = __('Coupon Code', 'woocommerce');
            $this->additional_content = __('Email sent to customers when a coupon is generated.', 'woocommerce');
            $this->heading = __('Coupon Code for Next Purchase', 'woocommerce');
            $this->subject = __('Your Coupon Code', 'woocommerce');

            add_action('send_coupon_code_email_notification', array($this, 'trigger'), 10, 2);

            parent::__construct();

            $this->template_html  = 'customer-coupon-code.php';
            $this->template_plain = 'plain/customer-coupon-code.php';
           // $this->template_base  = WC()->template_path();
			$this->template_base  = plugin_dir_path( __FILE__ ) . 'templates/';
        }

        public function trigger($order_id, $coupon_code = false) {
			
			//$coupon_code = get_post_meta($order_id, '_generated_coupon_code', true);
            $this->object = wc_get_order($order_id);
            $this->recipient = $this->object->get_billing_email();
            $this->coupon_code = $coupon_code;

            if (!$this->is_enabled() || !$this->get_recipient()) {
                return;
            }

            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        }
		
		public function get_content_html() {
			ob_start();
			wc_get_template(
				$this->template_html, 
				array(
					'order' => $this->object,
					'coupon_code' => $this->coupon_code,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text' => false,
					'email' => $this,
					'additional_content' => $this->get_additional_content(),
				),
				'woocommerce-order-coupon',
				plugin_dir_path( __FILE__ )
			);
			return ob_get_clean();
		}


        public function get_content_plain() {
            ob_start();
            wc_get_template($this->template_plain, array(
                'order'       => $this->object,
                'coupon_code' => $this->coupon_code,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'plain_text'  => true,
                'email'       => $this,
				'additional_content' => $this->get_additional_content(),
            ),
			'woocommerce-order-coupon',
			plugin_dir_path( __FILE__ )
		);
            return ob_get_clean();
        }
    }
}
?>
