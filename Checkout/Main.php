<?php
/**
 * Class Main
 *
 * @package Ecomerciar\PedidosYa\Checkout
 */

namespace Ecomerciar\PedidosYa\Checkout;

use Ecomerciar\PedidosYa\Helper\Helper;
defined( 'ABSPATH' ) || exit;

/**
 * Google API Key - Geo Localization Main Class
 */
class Main {

	/**
	 * Add Google API Key - Geo Localization
	 *
	 * @return void
	 */
	public static function add_geolocalization() {
		if ( ! empty( Helper::get_option( 'google-api-key' ) ) ) {

			$store_address     = get_option( 'woocommerce_store_address' );
			$store_address_2   = get_option( 'woocommerce_store_address_2' );
			$store_city        = get_option( 'woocommerce_store_city' );
			$store_postcode    = get_option( 'woocommerce_store_postcode' );
			$store_raw_country = get_option( 'woocommerce_default_country' );

			$initLocB = $store_address . ' ' . $store_address_2 . ', ' . $store_city . ' ' . Helper::get_option( 'country' );
			$initLocS = $store_address . ' ' . $store_address_2 . ', ' . $store_city . ' ' . Helper::get_option( 'country' );

			?>
		<script type="text/javascript">
		var peya_geo_settings = {
			init_billing_address : "<?php echo esc_html( $initLocB ); ?>",
			init_shipping_address : "<?php echo esc_html( $initLocS ); ?>",
			billing_only: <?php echo ( 'billing_only' === get_option( 'woocommerce_ship_to_destination' ) ) ? 'true' : 'false'; ?>,
		}
		</script>
			<?php
			wp_enqueue_script( 'wc-peya-checkout-geo', Helper::get_assets_folder_url() . '/js/checkout-geo.js', array( 'jquery' ), '1.1.19', true );
			wp_enqueue_script( 'wc-peya-google-api', 'https://maps.googleapis.com/maps/api/js?key=' . Helper::get_option( 'google-api-key' ) . '&callback=peya_geo.init&libraries=places&v=weekly', array( 'wc-peya-checkout-geo' ), false, true );
			wp_enqueue_style( 'wc-peya-checkout', Helper::get_assets_folder_url() . '/css/checkout.css' );
		}
	}


	/**
	 * Override checkout fields
	 *
	 * @param array $fields
	 * @return array
	 */
	public static function override_checkout_fields( $fields ) {
		if ( ! empty( Helper::get_option( 'google-api-key' ) ) ) {
			$fields['billing']['billing_wc_lat']   = array(
				'label'       => __( 'Latitud', 'pedidosya' ),
				'placeholder' => __( 'Latitud', 'pedidosya' ),
				'required'    => false,
				'class'       => array(
					'form-row-wide',
				),
				'clear'       => true,
				'priority'    => 99,
			);
			$fields['billing']['billing_wc_lng']   = array(
				'label'       => __( 'Longitud', 'pedidosya' ),
				'placeholder' => __( 'Longitud', 'pedidosya' ),
				'required'    => false,
				'class'       => array(
					'form-row-wide',
				),
				'clear'       => true,
				'priority'    => 99,
			);
			$fields['shipping']['shipping_wc_lat'] = array(
				'label'       => __( 'Latitud', 'pedidosya' ),
				'placeholder' => __( 'Latitud', 'pedidosya' ),
				'required'    => false,
				'class'       => array(
					'form-row-wide',
				),
				'clear'       => true,
				'priority'    => 99,
			);
			$fields['shipping']['shipping_wc_lng'] = array(
				'label'       => __( 'Longitud', 'pedidosya' ),
				'placeholder' => __( 'Longitud', 'pedidosya' ),
				'required'    => false,
				'class'       => array(
					'form-row-wide',
				),
				'clear'       => true,
				'priority'    => 99,
			);
		}
		return $fields;
	}

	/**
	 * Save Custom Fields
	 *
	 * @param int  $order_id
	 * @param bool $posted
	 * @return void
	 */
	public static function checkout_update_order_meta( $order_id, $posted = null ) {
		$nonce_value    = wc_get_var( $_REQUEST['woocommerce-process-checkout-nonce'], wc_get_var( $_REQUEST['_wpnonce'], '' ) ); // phpcs:ignore
		if (
			empty( $nonce_value ) ||
			! wp_verify_nonce(
				$nonce_value,
				'woocommerce-process_checkout'
			)
		) {
			return;
		}

		if ( ! empty( Helper::get_option( 'google-api-key' ) ) ) {
			$customFields = array( 'billing_wc_lat', 'billing_wc_lng', 'shipping_wc_lat', 'shipping_wc_lng' );
			foreach ( $customFields as $field ) {
				if ( ! empty( $_POST[ $field ] ) ) {
					update_post_meta( $order_id, '_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
				}
			}
		}
	}

	/**
	 * Show Admin Billing Fields
	 *
	 * @param array $fields
	 * @return array
	 */
	public static function admin_billing_fields( $fields ) {
		if ( ! empty( Helper::get_option( 'google-api-key' ) ) ) {
			if ( is_admin() ) {
				$fields['wc_lat'] = array(
					'label' => __( 'Latitud', 'pedidosya' ),
					'show'  => true,
					'style' => '',
				);
				$fields['wc_lng'] = array(
					'label' => __( 'Longitud', 'pedidosya' ),
					'show'  => true,
					'style' => '',
				);
			}
		}
		return $fields;
	}

	/**
	 * Show Admin Billing Fields
	 *
	 * @param array $fields
	 * @return array
	 */
	public static function admin_shipping_fields( $fields ) {
		if ( ! empty( Helper::get_option( 'google-api-key' ) ) ) {
			if ( is_admin() ) {
				$fields['wc_lat'] = array(
					'label' => __( 'Latitud', 'pedidosya' ),
					'show'  => true,
					'style' => '',
				);
				$fields['wc_lng'] = array(
					'label' => __( 'Longitud', 'pedidosya' ),
					'show'  => true,
					'style' => '',
				);
			}
		}
		return $fields;
	}

}
