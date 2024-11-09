# WooCommerce Order Coupon Plugin

A WordPress plugin to generate and send unique, order-specific coupons directly from the WooCommerce order page. This plugin enables store managers to create and email customized discount codes upon successful order completion, providing an additional incentive for repeat purchases.

## Features
- Adds a "Send Coupon" option to WooCommerce order pages.
- Generates a unique coupon code for each order, displayed to the admin.
- Allows selection of coupon type, amount, and expiration date.
- Sends the generated coupon via email to the customer's billing address.
- Applies usage restrictions to each coupon to ensure secure and unique discount application.

## Installation

1. **Clone the Repository**:
   Clone or download the repository into your WordPress plugins directory:
   ```bash
   git clone https://github.com/roghithsam/woocommerce-order-coupon.git
   ```

2. **Upload via WordPress**:
   - Download the plugin as a `.zip` file.
   - Go to your WordPress dashboard: `Plugins > Add New > Upload Plugin`.
   - Choose the downloaded `.zip` file and click **Install Now**.
   - Activate the plugin.

3. **Manual Installation**:
   - Upload the plugin files to the `/wp-content/plugins/woocommerce-order-coupon` directory.
   - Activate the plugin through the **Plugins** menu in WordPress.

## Usage

1. **Accessing the Coupon Feature**:
   - Go to your WooCommerce orders and open the edit page for a specific order.
   - On the right sidebar, locate the **Order Coupon** meta box.

2. **Generating a Coupon**:
   - The plugin will display the customer’s email and a randomly generated coupon code.
   - Customize the discount type (e.g., percentage or fixed cart discount), amount, and expiration date.

3. **Sending the Coupon**:
   - Click the **Send Coupon** button.
   - The coupon code will be saved in the order meta and emailed to the customer.

## Requirements
- WordPress 5.0 or higher
- WooCommerce 4.0 or higher
- PHP 7.0 or higher

## Code Overview

- **Meta Box Creation**: Adds a custom meta box on the WooCommerce order page to input coupon details.
- **Coupon Generation**: Creates unique coupon codes using the order ID, with customization options for discount type and expiration date.
- **Email Notification**: Automatically sends the generated coupon to the customer’s billing email, with WooCommerce's native email class integration.

## Contributing
Pull requests are welcome. For significant changes, please open an issue to discuss what you would like to change.

## Support
For support or questions, please create an issue on the [GitHub repository](https://github.com/roghithsam/woocommerce-order-coupon/issues).
