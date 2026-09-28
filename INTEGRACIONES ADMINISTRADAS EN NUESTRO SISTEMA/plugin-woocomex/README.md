# Ingram Micro WooCommerce Connector

This plugin connects WooCommerce with the Ingram Micro Reseller API (Chile) to import products, sync pricing/availability and later create orders.

## Features

- Settings page to configure API credentials (OAuth client ID/secret, customer number, country code).
- Lightweight API client with token caching
- Starter product sync logic that imports search results as WooCommerce products

## Setup

1. Install the plugin by copying the `plugin-woocomex` folder into `wp-content/plugins`.
2. Activate from the WordPress admin.
3. Go to **Ingram Micro** menu and enter your API credentials.
4. Use the `Sync Products` button (added later) or manually trigger the admin action to pull items.

## Development

The API class (`includes/class-ingram-api.php`) wraps several endpoints; you can extend it with other methods from the documentation:

- `search_products()` → GET `/catalog`
- `price_and_availability()` → POST `/catalog/priceandavailability`
- `product_details($ipn)` → GET `/catalog/details/{ingramPartNumber}`

The product sync class shows a basic loop converting catalog items into WooCommerce products. Customize field mapping as needed and add periodic WP Cron jobs.

## Next Steps

- Implement order creation by calling `/orders` endpoints when a WooCommerce order is placed.
- Add background sync and webhook endpoints for real-time stock updates.
- Handle token renewal and error logging more robustly.

See the Ingram Micro API docs at https://developer.ingrammicro.com/reseller/api-documentation/Chile for full specifications.