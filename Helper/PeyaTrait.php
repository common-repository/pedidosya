<?php
/**
 * Trait PedidosYa
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

trait PeyaTrait {

	/**
	 * Get Rank/Priority/Order value for PedidosYa Status
	 *
	 * @param string $status
	 * @return int
	 */
	public static function get_status_rank( $status ) {
		$translations = array(
			'EXPRESS_SCHEDULED' => 0,
			'PREORDER'          => 1,
			'CONFIRMED'         => 2,
			'IN_PROGRESS'       => 3,
			'NEAR_PICKUP'       => 4,
			'PICKED_UP'         => 5,
			'NEAR_DROPOFF'      => 6,
			'COMPLETED'         => 7,
			'CANCELLED'         => 8,
		);
		return $translations[ $status ];
	}


	/**
	 * Get Error Description for PedidosYa error code
	 *
	 * @param string $error_code
	 * @return string
	 */
	public static function get_error_descr( $error_code ) {
		$translations = array(
			'WAYPOINT_OUT_OF_ZONE'            => __( 'Punto de Retiro y Punto de Entrega fuera de zona', 'pedidosya' ),
			'WAYPOINTS_NOT_FOUND'             => __( 'No fue posible encontrar el Punto de Entrega o Retiro del pedido', 'pedidosya' ),
			'MAX_DISTANCE_EXCEEDED'           => __( 'La distancia excede el límite', 'pedidosya' ),
			'INVALID_DELIVERY_TIME'           => __( 'La fecha de entrega se encuentra fuera del rango de servicio de flota', 'pedidosya' ),
			'TEMPORARILY_CLOSED'              => __( 'El servicio se encuentra inactivo temporalmente', 'pedidosya' ),
			'DELAY_IN_ZONE_FOR_DELIVERY_TIME' => __( 'Hay un retraso de la flota en la zona de trabajo por el tiempo de entrega deseado.', 'pedidosya' ),
		);
			return $translations[ $error_code ];
	}

	/**
	 * Get Status Description for PedidosYa Status
	 *
	 * @param string $status
	 * @return string
	 */
	public static function get_status_descr( $status ) {

		$translations = array(
			'EXPRESS_SCHEDULED' => __( 'El pedido fue programado, el repartidor será llamado automáticamente', 'pedidosya' ),
			'PREORDER'          => __( 'Pre Orden', 'pedidosya' ),
			'CONFIRMED'         => __( 'Estamos procesando tu orden de envío', 'pedidosya' ),
			'IN_PROGRESS'       => __( 'Estamos yendo a buscar el envío', 'pedidosya' ),
			'NEAR_PICKUP'       => __( 'El repartidor está llegando al punto de retiro', 'pedidosya' ),
			'PICKED_UP'         => __( 'El repartidor está en camino al punto de entrega', 'pedidosya' ),
			'NEAR_DROPOFF'      => __( 'El repartidor está llegando al punto de entrega', 'pedidosya' ),
			'COMPLETED'         => __( 'Envío finalizado', 'pedidosya' ),
			'CANCELLED'       	=> __( 'Envío cancelado', 'pedidosya' ),
		);

		  return ( isset( $translations[ strtoupper( $status ) ] ) ) ? $translations[ strtoupper( $status ) ] : __( 'El repartidor no fue llamado', 'pedidosya' );
	}

	/**
	 * Get Countries list option
	 *
	 * @return Array
	 */
	public static function get_countries() {

		return array(
			'AR' => __( 'Argentina', 'woocommerce' ),
			'BO' => __( 'Bolivia', 'woocommerce' ),
			'CL' => __( 'Chile', 'woocommerce' ),
			'CR' => __( 'Costa Rica', 'woocommerce' ),
			'EC' => __( 'Ecuador', 'woocommerce' ),
			'HN' => __( 'Honduras', 'woocommerce' ),
			'SV' => __( 'El Salvador', 'woocommerce' ),
			'GT' => __( 'Guatemala', 'woocommerce' ),
			'NI' => __( 'Nicaragua', 'woocommerce' ),
			'PA' => __( 'Panama', 'woocommerce' ),
			'PE' => __( 'Perú', 'woocommerce' ),
			'PY' => __( 'Paraguay', 'woocommerce' ),
			'DO' => __( 'Dominican Republic', 'woocommerce' ),
			'UY' => __( 'Uruguay', 'woocommerce' ),
			'VE' => __( 'Venezuela', 'woocommerce' ),

		);

	}
}
