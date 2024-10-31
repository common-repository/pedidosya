<?php
/**
 * Class Orders Base Action
 *
 * @package Ecomerciar\PedidosYa\Orders\Actions
 */

namespace Ecomerciar\PedidosYa\Orders\Actions;

abstract class BaseAction {

	abstract static function getName();
	abstract static function getID();
	abstract static function execute( \WC_Order $order);

	/**
	 * Run Action
	 *
	 * @param \WC_Order $order
	 * @param string    $deliveryTime
	 *
	 * @return bool
	 */
	public static function run( \WC_Order $order, string $deliveryTime = '' ) {
		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			$order->add_order_note( sprintf( __( 'PedidosYa: No es posible %s. PedidosYa no se ha definido como método de envío.', 'pedidosya' ), static::getName() ) );
			return false;
		}

		$shipping_method = array_shift( $shipping_methods );
		// Verify It's PedidosYa Shipping Methods
		if ( 'peya' != $shipping_method->get_method_id() ) {
			$order->add_order_note( sprintf( __( 'PedidosYa: No es posible %s. PedidosYa no se ha definido como método de envío.', 'pedidosya' ), static::getName() ) );
			return false;
		}

		return static::execute( $order, $deliveryTime );
	}

	/**
	 * Register Action into Order Actions
	 *
	 * @param array $actions
	 * @return array $actions
	 */
	public static function register_action( $actions ) {
		global $theorder;
		$shipping_methods = $theorder->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			return $actions;
		}
		$shipping_method = array_shift( $shipping_methods );
		if ( $shipping_method->get_method_id() === 'peya' ) {
			$actions[ 'wc_peya_order_action_' . static::getID() ] = static::getName();
		}
		return $actions;
	}

	/**
	 * Run action from order_id - AJAX
	 *
	 * @param string $order_id
	 * @return string
	 */
	public static function runFromId( $order_id ) {
		return self::run( wc_get_order( $order_id ) );
	}

	public static function ajax_callback_wp() {

		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'pedidosya' ) || ! isset( $_POST['nonce'] ) || ! isset( $_POST['order_id'] ) ) {
			wp_send_json_error();
		}

		$order_id = filter_var( wp_unslash( $_POST['order_id'] ), FILTER_SANITIZE_NUMBER_INT );
		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_send_json_error();
		}

		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			wp_send_json_error();
		}

		$shipping_method = array_shift( $shipping_methods );

		if ( 'peya' !== $shipping_method->get_method_id() ) {
			wp_send_json_error();
		}

		$ret = static::run( $order );
		if ( $ret ) {
			wp_send_json_success( $ret );
		} else {
			wp_send_json_error();
		}

	}

}
