<?php
/**
 * Class Orders Cancel Schedule Action
 *
 * @package Ecomerciar\PedidosYa\Orders\Actions
 */

namespace Ecomerciar\PedidosYa\Orders\Actions;

use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Orders\Actions\BaseAction;
use Ecomerciar\PedidosYa\Orders\Actions\Confirm;

class CancelSchedule extends BaseAction {

	/**
	 * Get action Name
	 *
	 * @return string
	 */
	public static function getName() {
		return __( 'Cancelar Programación PedidosYa', 'pedidosya' );
	}

	/**
	 * Get ID
	 *
	 * @return string
	 */
	public static function getID() {
		return 'cancel_schedule';
	}

	/**
	 * Executes Action
	 *
	 * @param \WC_Order $order
	 * @param string    $deliveryTime
	 * @return bool
	 */
	public static function execute( \WC_Order $order ) {
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = array_shift( $shipping_methods );

		if ( 'EXPRESS_SCHEDULED' !== $shipping_method->get_meta( 'wc_peya_status' ) ) {
			return false;
		};

		$shipping_method->update_meta_data( 'wc_peya_status', '' );
		$shipping_method->update_meta_data( 'wc_peya_schedule_time', '' );
		$shipping_method->save();

		$order->add_order_note( __( 'La programación del envío por PedidosYa fue cancelada.', 'pedidosya' ) );
		Helper::delete_schedules_for_order( $order->get_id() );
		return true;
	}


}
