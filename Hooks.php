<?php
defined('ABSPATH') || exit;

// --- Shipping Calculator - WooCommerce Locale Templates
add_filter('woocommerce_locate_template', ['WCPeya', 'add_wc_locate_template'] , 1, 3 );
add_action('woocommerce_calculated_shipping', ['\Ecomerciar\PedidosYa\ShippingCalculator\ShippingCalculator', 'save_fields'], 1 , 3 );

// --- Shipment Method
add_filter('woocommerce_shipping_methods', ['WCPeya', 'add_shipping_method']);
add_action( 'woocommerce_after_shipping_rate', ['\Ecomerciar\PedidosYa\ShippingMethod\WC_Peya', 'after_shipping_rate']);

// --- Order section
add_action('add_meta_boxes', ['\Ecomerciar\PedidosYa\Orders\Metabox', 'create']);
add_action('woocommerce_order_status_completed', ['\Ecomerciar\PedidosYa\Orders\Processor', 'process_order_completed'], 10, 4 );

// --- Order Actions
add_action('woocommerce_order_actions', ['\Ecomerciar\PedidosYa\Orders\Actions\Call', 'register_action']);
add_action('woocommerce_order_actions', ['\Ecomerciar\PedidosYa\Orders\Actions\Confirm', 'register_action']);
add_action('woocommerce_order_actions', ['\Ecomerciar\PedidosYa\Orders\Actions\Cancel', 'register_action']);
add_action('woocommerce_order_actions', ['\Ecomerciar\PedidosYa\Orders\Actions\ProofOfDelivery', 'register_action']);
add_action('woocommerce_order_action_wc_peya_order_action_call', ['\Ecomerciar\PedidosYa\Orders\Actions\Call', 'run']);
add_action('woocommerce_order_action_wc_peya_order_action_confirm', ['\Ecomerciar\PedidosYa\Orders\Actions\Confirm', 'run']);
add_action('woocommerce_order_action_wc_peya_order_action_cancel', ['\Ecomerciar\PedidosYa\Orders\Actions\Cancel', 'run']);
add_action('woocommerce_order_action_wc_peya_order_action_proof_of_delivery', ['\Ecomerciar\PedidosYa\Orders\Actions\ProofOfDelivery', 'run']);


// --- Order Ajax Actions
add_action( 'wp_ajax_peya_action_proof_of_delivery', ['\Ecomerciar\PedidosYa\Orders\Actions\ProofOfDelivery', 'ajax_callback_wp']);
add_action( 'wp_ajax_nopriv_peya_action_proof_of_delivery', ['\Ecomerciar\PedidosYa\Orders\Actions\ProofOfDelivery', 'ajax_callback_wp'] );
add_action( 'wp_ajax_peya_action_call', ['\Ecomerciar\PedidosYa\Orders\Actions\Call', 'ajax_callback_wp']);
add_action( 'wp_ajax_nopriv_peya_action_call', ['\Ecomerciar\PedidosYa\Orders\Actions\Call', 'ajax_callback_wp'] );
add_action( 'wp_ajax_peya_action_cancel', ['\Ecomerciar\PedidosYa\Orders\Actions\Cancel', 'ajax_callback_wp']);
add_action( 'wp_ajax_nopriv_peya_action_cancel', ['\Ecomerciar\PedidosYa\Orders\Actions\Cancel', 'ajax_callback_wp'] );
add_action( 'wp_ajax_peya_action_confirm', ['\Ecomerciar\PedidosYa\Orders\Actions\Confirm', 'ajax_callback_wp']);
add_action( 'wp_ajax_nopriv_peya_action_confirm', ['\Ecomerciar\PedidosYa\Orders\Actions\Confirm', 'ajax_callback_wp'] );
add_action( 'wp_ajax_peya_action_cancel_schedule', ['\Ecomerciar\PedidosYa\Orders\Actions\CancelSchedule', 'ajax_callback_wp']);
add_action( 'wp_ajax_nopriv_peya_action_cancel_schedule', ['\Ecomerciar\PedidosYa\Orders\Actions\CancelSchedule', 'ajax_callback_wp'] );
add_action('admin_head', ['WCPeya', 'ajax_settings_js']);


// --- Admin Order List
add_filter( 'manage_edit-shop_order_columns', ['\Ecomerciar\PedidosYa\Orders\OrderList', 'add_peya_status'] );
add_filter( 'manage_edit-shop_order_columns', ['\Ecomerciar\PedidosYa\Orders\OrderList', 'add_peya_actions'] );
add_action( 'manage_shop_order_posts_custom_column', ['\Ecomerciar\PedidosYa\Orders\OrderList', 'fill_peya_status']  );
add_action( 'manage_shop_order_posts_custom_column', ['\Ecomerciar\PedidosYa\Orders\OrderList', 'fill_peya_actions']  );

// --- Tracking
add_filter('woocommerce_my_account_my_orders_columns', ['\Ecomerciar\PedidosYa\Orders\OrderList', 'add_tracking_column']);
add_action('woocommerce_my_account_my_orders_column_wc-peya-tracking', ['\Ecomerciar\PedidosYa\Orders\OrderList', 'fill_tracking_column']);

// --- Webhook
add_action('woocommerce_api_wc-peya-orders-status', ['\Ecomerciar\PedidosYa\Orders\Webhooks', 'listener']);

// --- Settings
add_filter('plugin_action_links_' . plugin_basename(WCPeya::MAIN_FILE) , ['WCPeya', 'create_settings_link']);
add_filter('woocommerce_get_sections_shipping', ['\Ecomerciar\PedidosYa\Settings\Main', 'add_tab_settings']);
add_filter('woocommerce_get_settings_shipping', ['\Ecomerciar\PedidosYa\Settings\Main', 'get_tab_settings'], 10, 2);
add_action( 'woocommerce_update_options_peya_shipping_options', ['\Ecomerciar\PedidosYa\Settings\Main', 'update_settings']);
add_action( 'woocommerce_admin_field_peya-schedule', ['\Ecomerciar\PedidosYa\Settings\ScheduleType', 'output_fields']);
// Process/save the settings
add_action( 'woocommerce_settings_save_shipping', ['\Ecomerciar\PedidosYa\Settings\Main', 'save'] );


// --- Onboarding Page
add_action('admin_menu',  ['\Ecomerciar\PedidosYa\Onboarding\Main', 'register_onboarding_page']);
add_action('admin_enqueue_scripts', ['\Ecomerciar\PedidosYa\Onboarding\Main', 'add_assets_files']);

// --- Cron
add_filter( 'cron_schedules', ['\Ecomerciar\PedidosYa\Orders\Cron','add_schedule'] );
add_action( 'wc_peya_cron_update_order', ['\Ecomerciar\PedidosYa\Orders\Cron','run_cron']  );
add_action( 'wc_peya_cron_update_settings', ['\Ecomerciar\PedidosYa\Settings\Cron','run_cron']  );


// --- Checkout Google API
add_action( 'woocommerce_after_order_notes' , ['\Ecomerciar\PedidosYa\Checkout\Main', 'add_geolocalization']);
add_filter('woocommerce_checkout_fields', ['\Ecomerciar\PedidosYa\Checkout\Main', 'override_checkout_fields']);
add_action('woocommerce_checkout_update_order_meta', ['\Ecomerciar\PedidosYa\Checkout\Main', 'checkout_update_order_meta']);
add_filter('woocommerce_admin_billing_fields', ['\Ecomerciar\PedidosYa\Checkout\Main', 'admin_billing_fields']);
add_filter('woocommerce_admin_shipping_fields', ['\Ecomerciar\PedidosYa\Checkout\Main', 'admin_shipping_fields']);
?>
