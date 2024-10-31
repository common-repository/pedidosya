<?php
/**
 * Class WooCommerce Order Metabox's base
 *
 * @package Ecomerciar\PedidosYa\Orders
 */

namespace Ecomerciar\PedidosYa\Orders;

use Ecomerciar\PedidosYa\Helper\Helper;
defined( 'ABSPATH' ) || exit;

class Metabox {
	/**
	 * Creates Metabos
	 *
	 * @return void
	 */
	public static function create() {
		$order_types = wc_get_order_types( 'order-meta-boxes' );
		foreach ( $order_types as $order_type ) {
			add_meta_box(
				'peya_metabox', // Unique ID.
				'PedidosYa', // Box title.
				array( __CLASS__, 'content' ), // Content callback, must be of type callable.
				$order_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Prints Metabox Contents
	 *
	 * @param WC_Post $post
	 * @param Metabox $metabox
	 */
	public static function content( $post, $metabox ) {
		$order = wc_get_order( $post->ID );
		if ( empty( $order ) ) {
			return false;
		}
		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			echo esc_html__( 'El pedido no tiene PedidosYa como método de envío.', 'pedidosya' );
			return true;
		}

		$shipping_method = array_shift( $shipping_methods );
		if ( $shipping_method->get_method_id() === 'peya' ) {
			if ( ! empty( $shipping_method->get_meta( 'wc_peya_status' ) ) ) {
				if ( ! empty( $shipping_method->get_meta( 'wc_peya_status' ) ) ) {
					echo '<p><strong>' . esc_html__( 'Estado: ', 'pedidosya' ) . '</strong>' . esc_html( Helper::get_status_descr( $shipping_method->get_meta( 'wc_peya_status' ) ) ) . '</p>';
				} else {
					echo '<p><strong>' . esc_html__( 'Estado: ', 'pedidosya' ) . '</strong>' . esc_html( Helper::get_status_descr( '' ) ) . '</p>';
				}

				if ( ! empty( $shipping_method->get_meta( 'wc_peya_id' ) ) ) {
					echo '<p><strong>' . esc_html__( 'ID PedidosYa: ', 'pedidosya' ) . '</strong>' . esc_html( $shipping_method->get_meta( 'wc_peya_id' ) ) . '</p>';
				} else {
					echo '<p><strong>' . esc_html__( 'ID PedidosYa: ', 'pedidosya' ) . '</strong>' . esc_html__( 'Pendiente', 'pedidosya' ) . '</p>';
				}

				if ( ! empty( $shipping_method->get_meta( 'wc_peya_confirmation_cd' ) ) ) {
					echo '<p><strong>' . esc_html__( 'ID Confirmación: ', 'pedidosya' ) . '</strong>' . esc_html( $shipping_method->get_meta( 'wc_peya_confirmation_cd' ) ) . '</p>';
				} else {
					echo '<p><strong>' . esc_html__( 'ID Confirmación: ', 'pedidosya' ) . '</strong>' . esc_html__( 'Pendiente', 'pedidosya' ) . '</p>';
				}

				$trackingUrl = $shipping_method->get_meta( 'wc_peya_tracking_url' );
				if ( ! empty( $trackingUrl ) ) {
					echo '<p>' . "<a href='" . esc_attr( $trackingUrl ) . "' target='_blank'>" . esc_html__( 'Ver Seguimiento', 'pedidosya' ) . '</a></p>';
				} else {
					echo '<p>' . esc_html__( 'El pedido aún no tiene URL de Seguimiento.', 'pedidosya' ) . '</p>';
				}
			} else {
				echo esc_html__( 'El repartidor de PedidosYa aún no ha sido llamado.', 'pedidosya' ) . '<br>';
			}

			// ACTIONS.
			if ( empty( $shipping_method->get_meta( 'wc_peya_status' ) ) ) {
				echo "<a class='button button-primary peya-action' data-action='peya_action_call' data-id='" . esc_attr( $post->ID ) . "'>" . esc_html__( 'Solicitar Repartidor', 'pedidosya' ) . '</a>';
			} else {

				switch ( $shipping_method->get_meta( 'wc_peya_status' ) ) {
					case '':
						echo "<a class='button button-primary peya-action' data-action='peya_action_call' data-id='" . esc_attr( $post->ID ) . "'>" . esc_html__( 'Solicitar Repartidor', 'pedidosya' ) . '</a>';
						break;
					case 'EXPRESS_SCHEDULED':
						echo "<a class='button button-primary peya-action' data-action='peya_action_cancel_schedule' data-id='" . esc_attr( $post->ID ) . "'>" . esc_html__( 'Cancelar Programación', 'pedidosya' ) . '</a>';
						break;
					case 'PREORDER':
						echo "<a class='button button-primary peya-action' data-action='peya_action_cancel' data-id='" . esc_attr( $post->ID ) . "'>" . esc_html__( 'Cancelar Envío', 'pedidosya' ) . '</a>';
						echo "<a class='button button-primary peya-action' data-action='peya_action_confirm' data-id='" . esc_attr( $post->ID ) . "'>" . esc_html__( 'Confirmar Envío', 'pedidosya' ) . '</a>';
						break;
					case 'CONFIRMED':
						echo "<a class='button button-primary peya-action' data-action='peya_action_cancel' data-id='" . esc_attr( $post->ID ) . "'>" . esc_html__( 'Cancelar Envío', 'pedidosya' ) . '</a>';
						break;
					case 'COMPLETED':
						echo "<a class='button button-primary peya-action' data-action='peya_action_call' data-id='" . esc_attr( $post->ID ) . "'>" . esc_html__( 'Volver a Solicitar Repartidor', 'pedidosya' ) . '</a>';
						break;
					case 'CANCELLED':
						echo "<a class='button button-primary peya-action' data-action='peya_action_call' data-id='" . esc_attr( $post->ID ) . "'>" . esc_html__( 'Volver a Solicitar Repartidor', 'pedidosya' ) . '</a>';
						break;
					default:
						break;
				}
				echo '<br>';
			}

			// Proof of Delivery.
			if ( 'COMPLETED' === $shipping_method->get_meta( 'wc_peya_status' ) ) {
				echo '<br>';
				echo "<a class='button button-primary peya-action' data-action='peya_action_proof_of_delivery' data-id='" . esc_attr( $post->ID ) . "'>" . esc_html__( 'Obtener Prueba de Envío', 'pedidosya' ) . '</a>';
			}

			// Online Support.
			$online_support = isset( get_post_meta( $order->get_id(), '_peya_online_support' )[0] ) ? get_post_meta( $order->get_id(), '_peya_online_support' )[0] : '';
			if ( $online_support ) {
				echo '<p><strong>' . "</strong><a class='button' href='" . esc_url( $online_support ) . "' target='_blank'>" . esc_html__( 'Soporte Online', 'pedidosya' ) . '</a></p>';
			}
		
		} else {
			echo esc_html__( 'El pedido no tiene PedidosYa como método de envío.', 'pedidosya' );

		}

	}

}
