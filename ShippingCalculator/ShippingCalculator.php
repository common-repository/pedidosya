<?php
/**
 * Class Our main payment Shipping Calculator Method class
 *
 * @package Ecomerciar\PedidosYa\Settings
 */

namespace Ecomerciar\PedidosYa\ShippingCalculator;

defined( 'ABSPATH' ) || exit;

class ShippingCalculator {

	/**
	 * Save shipping address field on shipping calculator
	 */
	public static function save_fields() {
		$calc_shipping_address = isset( $_POST['calc_shipping_address'] ) ? wp_unslash( $_POST['calc_shipping_address'] ) : '';

		if ( $calc_shipping_address ) {
			WC()->customer->set_shipping_address( $calc_shipping_address );
		}
	}

}
