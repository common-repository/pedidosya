<?php
/**
 * Class Proof of delivery
 *
 * @package Ecomerciar\PedidosYa\Orders\Actions
 */

namespace Ecomerciar\PedidosYa\Orders\Actions;

use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Orders\Actions\BaseAction;
use Ecomerciar\PedidosYa\Orders\Actions\Confirm;

class ProofOfDelivery extends BaseAction {

	/**
	 * Get action Name
	 *
	 * @return string
	 */
	public static function getName() {
		return __( 'Obtener Prueba de Envío', 'pedidosya' );
	}

	/**
	 * Get ID
	 *
	 * @return string
	 */
	public static function getID() {
		return 'proof_of_delivery';
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
		$PedidosYa_id     = $shipping_method->get_meta( 'wc_peya_id' );

		if ( ! empty( $PedidosYa_id ) ) {
			$sdk = new PeyaSdk();

			$response = $sdk->get_proof_of_delivery( $PedidosYa_id );
			if ( ! empty( $response ) ) {
				if ( isset( $response['proofOfDelivery'] ) ) {
					return array( 'image' => $response['proofOfDelivery'] );
				} else {
					$order->add_order_note( sprintf( __( 'No fue posible obtener la prueba de envío. Mensaje: %s Contacte al administrador.', 'pedidosya' ), ( isset( $response['message'] ) ) ? $response['message'] : '' ) );
				}
			} else {
				$order->add_order_note( __( 'No fue posible obtener la prueba de envío. Contacte al administrador.', 'pedidosya' ) );
			}
		} else {
			$order->add_order_note( __( 'No fue posible obtener la prueba de envío, no existe un ID de PedidosYa relacionado a la orden.', 'pedidosya' ) );
		}
		return false;
	}


}
