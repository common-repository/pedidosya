<?php
/**
 * Class Cancel Action Class
 *
 * @package Ecomerciar\PedidosYa\Orders\Actions
 */

namespace Ecomerciar\PedidosYa\Orders\Actions;

use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Orders\Actions\BaseAction;
use Ecomerciar\PedidosYa\Orders\Actions\Confirm;

class Cancel extends BaseAction {

	/**
	 * Get action Name
	 *
	 * @return string
	 */
	public static function getName() {
		return __( 'Cancelar Repartidor PedidosYa', 'pedidosya' );
	}

	/**
	 * Get ID
	 *
	 * @return string
	 */
	public static function getID() {
		return 'cancel';
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
			$sdk = new PeyaSdk();

			$cancel_reason = ( isset( $_POST['action_reason'] ) ) ? wp_unslash( $_POST['action_reason'] ) : '';
			$cancel_reason = ( empty( $cancel_reason ) ) ? __( 'Cancelado por el Vendedor', 'pedidosya' ) : $cancel_reason;

			$response = $sdk->cancel_order( $PedidosYa_id, $cancel_reason );
			if ( ! empty( $response ) ) {
				if ( strtoupper( $response['status'] ) === 'CANCELLED' ) {
					$shipping_method->update_meta_data( 'wc_peya_status', $response['status'] );
					$shipping_method->save();
					$order->add_order_note( __( 'El envío fue cancelado.', 'pedidosya' ) );
					$return = true;
				} else {
					$order->add_order_note( sprintf( __( 'No fue posible cancelar el envío. Mensaje: %s Contacte al administrador.', 'pedidosya' ), ( isset( $response['message'] ) ) ? $response['message'] : '' ) );
				}
			} else {
				$order->add_order_note( __( 'No fue posible cancelar el envío. Contacte al administrador.', 'pedidosya' ) );
			}
		} else {
			$order->add_order_note( __( 'No se realiza la cancelación del envío, no existe un ID de PedidosYa relacionado a la orden.', 'pedidosya' ) );
		}
		return $return;
	}

}
