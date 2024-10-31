<?php
/**
 * Class Order Processor's Main
 *
 * @package Ecomerciar\PedidosYa\Orders
 */

namespace Ecomerciar\PedidosYa\Orders;

use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Orders\Actions\Call;
use Ecomerciar\PedidosYa\Orders\Actions\Schedule;
defined( 'ABSPATH' ) || exit;

class Processor {
	/**
	 * Handles the WooCommerce order status
	 *
	 * @param int $order_id
	 * @return void
	 */
	public static function process_order_completed( int $order_id ) {
		$order            = wc_get_order( $order_id );
		$shipping_methods = $order->get_shipping_methods();
		if ( ! ( empty( $shipping_methods ) ) ) {
			$shipping_method = array_shift( $shipping_methods );
			// Verify It's PedidosYa Shipping Methods.
			if ( 'peya' === $shipping_method->get_method_id() &&
			// If Status is Empty.
			empty( $shipping_method->get_meta( 'wc_peya_status' ) ) ) {
				// If PedidosYa Express is ON.
				if ( Helper::is_express_available( $shipping_method['method_id'], $shipping_method['instance_id'] ) === 'yes' ) {
					// check if now is available to pickup.
					$deliveryTimeToday = Helper::get_today_express_available( $shipping_method['method_id'], $shipping_method['instance_id'] );
					// IS Fleet Time???
					if ( $deliveryTimeToday ) {
						// action call.
						$action = new Call();
						$action->run( $order, $deliveryTimeToday );
					} else {
						// Today when branch is open.
						$deliveryTimeToday = Helper::get_today_next_express_available( $shipping_method['method_id'], $shipping_method['instance_id'] );
						if ( $deliveryTimeToday ) {
							$action = new Schedule();
							$action->run( $order, $deliveryTimeToday );
						} else {
							// action program for next days.
							$deliveryTimeTomorrow = Helper::get_tomorrow_express_available( $shipping_method['method_id'], $shipping_method['instance_id'] );
							$action               = new Schedule();
							$action->run( $order, $deliveryTimeTomorrow );
						}
					}
				}
			}
		}	
	}

}
