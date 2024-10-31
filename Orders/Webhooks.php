<?php
/**
 * Class WebHook's base
 *
 * @package Ecomerciar\PedidosYa\Orders
 */

namespace Ecomerciar\PedidosYa\Orders;

use Ecomerciar\PedidosYa\Helper\Helper;

defined( 'ABSPATH' ) || exit;

class Webhooks {

				/**
				 * Receives the webhook and check if it's valid to proceed
				 *
				 * @return void
				 */
	public static function listener() {
					// Takes raw data from the request.
					$json = file_get_contents( 'php://input' );

					// Converts it into a PHP object.
					$data = json_decode( $json, true );
					Helper::log_info( 'Webhook recibido' );
		if ( Helper::get_option( 'debug' ) ) {
						Helper::log_debug( __FUNCTION__ . esc_html__( '- Webhook recibido de PedidosYa:', 'pedidosya' ) . wp_json_encode( $json ) );
		}
		if ( empty( $json ) || ! self::validate_input( $data ) ) {
						wp_die( esc_html__( 'WooCommerce PedidosYa Webhook no válido.', 'pedidosya' ), 'PedidosYa Webhook', array( 'response' => 500 ) );
		}
	}

				/**
				 * Validates the incoming webhook
				 *
				 * @param array $data
				 * @return bool
				 */
	private static function validate_input( array $data ) {
		$return = true;
		$data   = wp_unslash( $data );
		if ( empty( $data['id'] ) ) {
						$return = false;
		}
		if ( empty( $data['data'] ) ) {
						$return = false;
		}
		if ( empty( $data['data']['status'] ) ) {
				$return = false;
		}

					$peya_id  = filter_var( $data['id'], FILTER_SANITIZE_STRING );
					$order_id = Helper::find_order_by_itemmeta_value( $peya_id );
		if ( empty( $order_id ) || is_null( $order_id ) || ! is_int( $order_id ) ) {
			$return = false;
		} else {
			self::handle_webhook( $order_id, $data );
		}

					return $return;
	}

				/**
				 * Handles and processes the webhook
				 *
				 * @param int   $order_id
				 * @param array $data
				 * @return void
				 */
	private static function handle_webhook( int $order_id, array $data ) {

					$order = wc_get_order( $order_id );

					$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
								Helper::log_info( sprintf( __( 'El pedido #%s fue no tiene método de envío.', 'pedidosya' ), $order_id ) );
					return false;
		}

			$shipping_method = array_shift( $shipping_methods );
		if ( $shipping_method->get_method_id() === 'peya' ) {
			if ( ! empty( $shipping_method->get_meta( 'wc_peya_id' ) ) ) {

				$current_status = $shipping_method->get_meta( 'wc_peya_status' );
				$webhook_status = $data['data']['status'];
				if ( Helper::get_status_rank( $webhook_status ) > Helper::get_status_rank( $current_status ) ) {
							$shipping_method->update_meta_data( 'wc_peya_status', $webhook_status );
				}

						$order->add_order_note( 'PedidosYa - ' . $webhook_status . '.  - ' . Helper::get_status_descr( $webhook_status ) );
						$order->save();
						Helper::log_info( sprintf( __( 'El pedido #%1$s fue actualizada con el estado: %2$s', 'pedidosya' ), $order_id, $webhook_status ) );

						return true;
			} else {
				Helper::log_info( sprintf( __( 'El pedido #%s no tiene el ID de PedidosYa relacionado.', 'pedidosya' ), $order_id ) );
			}
		} else {
						Helper::log_info( sprintf( __( 'El pedido #%s fue no tiene PedidosYa como método de envío.', 'pedidosya' ), $order_id ) );
		}

											return false;

	}

}
