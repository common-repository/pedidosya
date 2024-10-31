<?php
/**
 * Class Cron Processor
 *
 * @package Ecomerciar\PedidosYa\Orders
 */

namespace Ecomerciar\PedidosYa\Orders;

use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Orders\Actions\Call;

defined( 'ABSPATH' ) || exit;

/**
 * Cron Processor's Main Class
 */
class Cron {



	/**
	 * Run Cron Action
	 */
	public static function run_cron() {
		$now         = get_date_from_gmt( 'now', 'Y-m-d H:i:s' );
		$nowDatePart = date( 'Y-m-d', strtotime( $now ) );
		$nowDateTime = date( 'H:i:s', strtotime( $now ) );
		$nowDate     = $nowDatePart . 'T' . $nowDateTime . 'Z';

		Helper::log( __( '*****************************************************', 'pedidosya' ) );
		Helper::log( __( 'Ejecución Cron Ordenes PedidosYa Express', 'pedidosya' ) );
		Helper::log( $now );
		Helper::log( $nowDate );

		$orders = Helper::find_order_scheduled( $nowDate );
		Helper::log( $orders );
		foreach ( $orders as $order_value ) {

			// Order ID is empty.
			if ( empty( $order_value['option_value'] ) ) {
				continue;}

			$order = wc_get_order( $order_value['option_value'] );

			// Order ID is empty.
			if ( empty( $order ) ) {
				continue;}

			$shipping_methods = $order->get_shipping_methods();

			// There is no Shipping Method.
			if ( empty( $shipping_methods ) ) {
				continue;}

			$shipping_method = array_shift( $shipping_methods );

			// Verify It's PedidosYa Shipping Method.
			if ( 'peya' !== $shipping_method->get_method_id() ) {
				continue;}

			// If Status is not EXPRESS_SCHEDULED.
			if ( 'EXPRESS_SCHEDULED' !== $shipping_method->get_meta( 'wc_peya_status' ) ) {
				Helper::delete_schedules_for_order( $order_value['option_value'] );
				continue;
			}

			$action = new Call();
			$action->run( $order );
		}

		Helper::log( __( 'CRON EOF', 'pedidosya' ) );
		Helper::log( __( '*****************************************************', 'pedidosya' ) );

	}

	/**
	 * Add New Schedule Time
	 *
	 * @param array $schedules
	 * @return array
	 */
	public static function add_schedule( $schedules ) {
		/*every minute*/
		$schedules['wc_peya_schedule'] = array(
			'interval' => 1 * 60 * 60,
			'display'  => __( 'A cada hora', 'pedidosya' ),
		);
		return $schedules;
	}
}
