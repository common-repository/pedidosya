<?php
/**
 * Class WooCommerce "My Orders" List's Main
 *
 * @package Ecomerciar\PedidosYa\Orders
 */

namespace Ecomerciar\PedidosYa\Orders;

use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Sdk\PeyaSdk;

defined( 'ABSPATH' ) || exit;

class OrderList {

	/**
	 * Adds new Column for PedidosYa Tracking Info
	 *
	 * @return bool
	 */
	public static function add_tracking_column( $columns ) {
		$columns['wc-peya-tracking'] = __( 'Seguimiento PedidosYa', 'pedidosya' );
		return $columns;
	}

	/**
	 * Adds new Column Info for Enviamelo Tracking Info
	 */
	public static function fill_tracking_column( $order ) {
		$shipping_methods = $order->get_shipping_methods();
		$shipping_method  = array_shift( $shipping_methods );
		if ( isset( $shipping_method ) ) {
			if ( $shipping_method->get_method_id() === 'peya' ) {
				if ( ! empty( $shipping_method->get_meta( 'wc_peya_tracking_url' ) ) ) {
					echo "<a href='" . esc_attr( $shipping_method->get_meta( 'wc_peya_tracking_url' ) ) . "' target='_blank'>" . $shipping_method->get_meta( 'wc_peya_id' ) . '</a>';
				} else {
					echo '<span>' . esc_html__( 'PedidosYa aún no ha sido llamado.', 'pedidosya' ) . '</span>';
				}
			}
		}
	}


	public static function add_peya_status( $columns ) {
		$columns['wc-peya-status'] = __( 'Estado PedidosYa', 'pedidosya' );
		return $columns;
	}

	public static function add_peya_actions( $columns ) {
		$columns['wc-peya-actions'] = __( 'Acciones PedidosYa', 'pedidosya' );
		return $columns;
	}

	public static function fill_peya_status( $column ) {
		global $post;
		if ( 'wc-peya-status' === $column ) {
			$order            = wc_get_order( get_the_ID() );
			$shipping_methods = $order->get_shipping_methods();
			$shipping_method  = array_shift( $shipping_methods );
			if ( isset( $shipping_method ) ) {
				if ( $shipping_method->get_method_id() === 'peya' ) {
					if ( ! empty( $shipping_method->get_meta( 'wc_peya_status' ) ) ) {
						echo '<span>' . esc_html( Helper::get_status_descr( $shipping_method->get_meta( 'wc_peya_status' ) ) ) . '</span>';
					} else {
						echo '<span>' . esc_html__( 'El repartidor de PedidosYa aún no ha sido llamado.', 'pedidosya' ) . '</span>';
					}
				}
			}
		}
	}

	public static function fill_peya_actions( $column ) {
		global $post;
		if ( 'wc-peya-actions' === $column ) {
			$order            = wc_get_order( get_the_ID() );
			$shipping_methods = $order->get_shipping_methods();
			$shipping_method  = array_shift( $shipping_methods );
			if ( isset( $shipping_method ) ) {
				if ( $shipping_method->get_method_id() === 'peya' ) {
					switch ( $shipping_method->get_meta( 'wc_peya_status' ) ) {
						case '':
							echo "<a class='button button-primary peya-action' data-action='peya_action_call' data-id='" . esc_attr( get_the_ID() ) . "'>" . esc_html__( 'Solicitar Repartidor', 'pedidosya' ) . '</a>';
							break;
						case 'EXPRESS_SCHEDULED':
							echo "<a class='button button-primary peya-action' data-action='peya_action_cancel_schedule' data-id='" . esc_attr( get_the_ID() ) . "'>" . esc_html__( 'Cancelar Programación', 'pedidosya' ) . '</a>';
							break;
						case 'PREORDER':
							echo "<a class='button button-primary peya-action' data-action='peya_action_confirm' data-id='" . esc_attr( get_the_ID() ) . "'>" . esc_html__( 'Confirmar Envío', 'pedidosya' ) . '</a>';
							echo "<a class='button button-primary peya-action' data-action='peya_action_cancel' data-id='" . esc_attr( get_the_ID() ) . "'>" . esc_html__( 'Cancelar Envío', 'pedidosya' ) . '</a>';
							break;
						case 'CONFIRMED':
							echo "<a class='button button-primary peya-action' data-action='peya_action_cancel' data-id='" . esc_attr( get_the_ID() ) . "'>" . esc_html__( 'Cancelar Envío', 'pedidosya' ) . '</a>';
							break;
						case 'COMPLETED':
								echo "<a class='button button-primary peya-action' data-action='peya_action_call' data-id='" . esc_attr( get_the_ID() ) . "'>" . esc_html__( 'Volver a Solicitar Repartidor', 'pedidosya' ) . '</a>';
							break;
						case 'CANCELLED':
							echo "<a class='button button-primary peya-action' data-action='peya_action_call' data-id='" . esc_attr( get_the_ID() ) . "'>" . esc_html__( 'Volver a Solicitar Repartidor', 'pedidosya' ) . '</a>';
							break;
						default:
							break;
					}
				}
			}
		}
	}

}
