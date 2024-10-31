<?php
/**
 * Class Orders Confirm Action
 *
 * @package Ecomerciar\PedidosYa\Orders\Actions
 */

namespace Ecomerciar\PedidosYa\Orders\Actions;

use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Orders\Actions\BaseAction;


class Confirm extends BaseAction {

	/**
	 * Get action Name
	 *
	 * @return string
	 */
	public static function getName() {
		return __( 'Confirmar Repartidor PedidosYa', 'pedidosya' );
	}

	/**
	 * Get ID
	 *
	 * @return string
	 */
	public static function getID() {
		return 'confirm';
	}

	/**
	 * Executes Action
	 *
	 * @param \WC_Order $order
	 * @param string    $deliveryTime
	 * @return bool
	 */
	public static function execute( \WC_Order $order ) {
		$return           = false;
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = array_shift( $shipping_methods );
		$PedidosYa_id     = $shipping_method->get_meta( 'wc_peya_id' );
		if ( ! empty( $PedidosYa_id ) ) {
			$sdk      = new PeyaSdk();
			$response = $sdk->confirm_order( $PedidosYa_id );

			if ( ! empty( $response ) ) {
				if ( strtoupper( $response['status'] ) === 'CONFIRMED' ) {
					$confirmation_cd = ( isset( $response['confirmationCode'] ) ) ? $response['confirmationCode'] : '';
					$online_support  = isset( $response['onlineSupportUrl'] ) ? $response['onlineSupportUrl'] : '';
					update_post_meta( $order->get_id(), '_peya_online_support', $online_support );
					$shipping_method->update_meta_data( 'wc_peya_confirmation_cd', $confirmation_cd );
					$shipping_method->update_meta_data( 'wc_peya_status', $response['status'] );
					$shipping_method->save();
					$order->add_order_note( __( 'El envío fue confirmado.', 'pedidosya' ) );
					$return = true;
				} else {
					$order->add_order_note( sprintf( __( 'No fue posible confirmar el envío. Mensaje: %s Contacte al administrador.', 'pedidosya' ), ( isset( $response['message'] ) ) ? $response['message'] : '' ) );
				}
			} else {
				$order->add_order_note( __( 'No fue posible confirmar el envío. Contacte al administrador.', 'pedidosya' ) );
			}
		} else {
				$order->add_order_note( __( 'No se realiza la confirmación, no existe un ID de PedidosYa relacionado a la orden.', 'pedidosya' ) );
		}
		return $return;
	}

}
