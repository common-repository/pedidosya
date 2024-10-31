<?php
/**
 * Class  Orders Schedule Action
 *
 * @package Ecomerciar\PedidosYa\Orders\Actions
 */

namespace Ecomerciar\PedidosYa\Orders\Actions;

use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Orders\Actions\BaseAction;
use Ecomerciar\PedidosYa\Orders\Actions\Confirm;

class Schedule extends BaseAction {

	/**
	 * Get action Name
	 *
	 * @return string
	 */
	public static function getName() {
		return __( 'Programar Repartidor PedidosYa', 'pedidosya' );
	}

	/**
	 * Get ID
	 *
	 * @return string
	 */
	public static function getID() {
		return 'schedule';
	}

	/**
	 * Executes Action
	 *
	 * @param \WC_Order $order
	 * @param string    $deliveryTime
	 * @return bool
	 */
	public static function execute( \WC_Order $order, string $deliveryTime = '' ) {
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = array_shift( $shipping_methods );
		$shipping_method->update_meta_data( 'wc_peya_status', 'EXPRESS_SCHEDULED' );
		$shipping_method->update_meta_data( 'wc_peya_schedule_time', $deliveryTime );
		$shipping_method->save();

		$order->add_order_note( sprintf( __( 'El pedido fue programado, el repartidor de PedidosYa será llamado a partir de la fecha %s.', 'pedidosya' ), $deliveryTime ) );

		add_option( 'wc-peya-order-schedule-' . $deliveryTime, $order->get_id(), '', 'yes' );
		return true;
	}


}
