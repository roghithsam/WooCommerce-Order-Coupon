jQuery(document).ready(function($) {
        $('#wcoc_send_coupon_button').on('click', function() {
            const button = $(this);
            button.prop('disabled', true);
            var data = {
                action: 'wcoc_send_coupon',
                security: wcoc_send_coupon_ajax.nonce,
                order_id: $('input[name="order_id"]').val(),
                coupon_code: $('input[name="coupon_code"]').val(),
                coupon_amount: $('input[name="coupon_amount"]').val(),
                discount_type: $('select[name="discount_type"]').val(),
                expiry_date: $('input[name="expiry_date"]').val(),
                billing_email: $('input[name="billing_email"]').val()
            };
            $.ajax({
                url: wcoc_send_coupon_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#send-coupon-wrapper').html('<h3>Generated Coupon</h3><p id="wcoc-generated-coupon"><strong>' + data.coupon_code + '</strong></p>');
                        alert(response.data.message);
                    } else {
                        alert(response.data.message);
                    }
                    button.prop('disabled', false);
                },
                error: function() {
                    button.prop('disabled', false);
                    alert('An error occurred.');
                }
            });
        });
    });
