<?php
/**
 * Class Orders Call Action
 *
 * @package Ecomerciar\PedidosYa\Orders\Actions
 */

namespace Ecomerciar\PedidosYa\Orders\Actions;

use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Orders\Actions\BaseAction;
use Ecomerciar\PedidosYa\Orders\Actions\Confirm;

class Call extends BaseAction {

	/**
	 * Get action Name
	 *
	 * @return string
	 */
	public static function getName() {
		return __( 'Solicitar Repartidor PedidosYa', 'pedidosya' );
	}

	/**
	 * Get ID
	 *
	 * @return string
	 */
	public static function getID() {
		return 'call';
	}

	/**
	 * Executes Action
	 *
	 * @param \WC_Order $order
	 * @param string    $deliveryTime
	 * @return bool
	 */
	public static function execute( \WC_Order $order, string $deliveryTime = '' ) {
		$return           = false;
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = array_shift( $shipping_methods );
		$sdk              = new PeyaSdk();
		$response         = $sdk->process_order( $order, $deliveryTime );
		if ( ! $response ) {
			$order->add_order_note( __( 'No fue posible notificar el pedido a PedidosYa. Contacte al administrador.', 'pedidosya' ) );
		} else {
			if ( isset( $response['id'] ) && isset( $response['status'] ) ) {
				if ( strtoupper( $response['status'] ) === 'PREORDER' ) {
					$PedidosYa_id   = $response['id'];
					$tracking_url   = $response['shareLocationUrl'];
					$online_support = isset( $response['onlineSupportUrl'] ) ? $response['onlineSupportUrl'] : '';
					$shipping_method->update_meta_data( 'wc_peya_tracking_url', $tracking_url );
					$shipping_method->update_meta_data( 'wc_peya_id', $PedidosYa_id );
					$shipping_method->update_meta_data( 'wc_peya_confirmation_cd', '' );
					$shipping_method->update_meta_data( 'wc_peya_status', $response['status'] );
					update_post_meta( $order->get_id(), 'wc_peya_proof_of_delivery', '' );
					update_post_meta( $order->get_id(), '_peya_online_support', $online_support );
					$shipping_method->save();
					$order->add_order_note( sprintf( __( 'El pedido fue notificado a PedidosYa ( %1$s - %2$s).', 'pedidosya' ), $PedidosYa_id, $tracking_url ) );
					Helper::delete_schedules_for_order( $order->get_id() );
					Confirm::run( $order );
					$return = true;
				} else {
					$order->add_order_note( sprintf( __( 'No se realiza el envío a PedidosYa. Status %1$s, %2$s %3$s', 'pedidosya' ), $response['status'], ( Helper::get_error_descr( $response['code'] ) ) ? Helper::get_error_descr( $response['code'] ) : $response['message'], ( isset( $response['code'] ) ) ? $response['code'] : '' ) );
					$shipping_method->update_meta_data( 'wc_peya_tracking_url', '' );
					$shipping_method->update_meta_data( 'wc_peya_id', '' );
					$shipping_method->update_meta_data( 'wc_peya_confirmation_cd', '' );
					$shipping_method->update_meta_data( 'wc_peya_status', '' );
					update_post_meta( $order->get_id(), 'wc_peya_proof_of_delivery', '' );
					update_post_meta( $order->get_id(), '_peya_online_support', '' );
					$shipping_method->save();
				}
			} else {
				$order->add_order_note( sprintf( __( 'No se realiza el envío a PedidosYa. Status %1$s, %2$s %3$s', 'pedidosya' ), $response['status'], ( Helper::get_error_descr( $response['code'] ) ) ? Helper::get_error_descr( $response['code'] ) : $response['message'], ( isset( $response['code'] ) ) ? $response['code'] : '' ) );
				$shipping_method->update_meta_data( 'wc_peya_tracking_url', '' );
				$shipping_method->update_meta_data( 'wc_peya_id', '' );
				$shipping_method->update_meta_data( 'wc_peya_confirmation_cd', '' );
				$shipping_method->update_meta_data( 'wc_peya_status', '' );
				update_post_meta( $order->get_id(), 'wc_peya_proof_of_delivery', '' );
				update_post_meta( $order->get_id(), '_peya_online_support', '' );
				$shipping_method->save();
			}
		}
		return $return;
	}

}
