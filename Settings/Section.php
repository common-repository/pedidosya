<?php
/**
 * Class PedidosYa Setting Section
 *
 * @package Ecomerciar\PedidosYa\Settings
 */

namespace Ecomerciar\PedidosYa\Settings;

use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Helper\Helper;

defined( 'ABSPATH' ) || exit;

class Section {

	/**
	 * Checks system requirements
	 *
	 * @return Array Fields Settings for PedidosYa
	 */
	public static function get() {

		// get webhook configured.
		$webhook = get_option( 'wc-peya-webhook-url' );

		return array(
			array(
				'title'             => __( 'Pedidos Ya', 'pedidosya' ),
				'type'              => 'title',
				'id'                => 'wc-peya_shipping_options',
				'custom_attributes' => array( 'required' => 'required' ),
			),
			array(
				'id'                => 'wc-peya-country',
				'name'              => __( 'Pais', 'pedidosya' ),
				'type'              => 'select',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
				'description'       => __( 'Nombre del Pais', 'pedidosya' ),
				'options'           => Helper::get_countries(),
			),
			array(
				'name'              => __( 'Client ID', 'pedidosya' ),
				'type'              => 'text',
				'id'                => 'wc-peya-client-id',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
				'desc'              => __( 'Estos datos los podes encontrar ingresando en tu cuenta de PedidosYa (https://envios.pedidosya.com.ar).', 'pedidosya' ),
			),
			array(
				'name'              => __( 'Client Secret', 'pedidosya' ),
				'type'              => 'text',
				'id'                => 'wc-peya-client-secret',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
				'desc'              => __( 'Estos datos los podes encontrar ingresando en tu cuenta de PedidosYa (https://envios.pedidosya.com.ar).', 'pedidosya' ),
			),
			array(
				'name'              => __( 'Correo Electrónico', 'pedidosya' ),
				'type'              => 'email',
				'id'                => 'wc-peya-email',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
				'desc'              => __( 'Ingresá el correco con el que te regsitraste en PedidosYa.', 'pedidosya' ),
			),
			array(
				'name'              => __( 'Contraseña', 'pedidosya' ),
				'type'              => 'password',
				'id'                => 'wc-peya-password',
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
				'desc'              => __( 'Ingresá la contraseña con la que te regsitraste en PedidosYa.', 'pedidosya' ),
			),
			array(
				'name'     => __( 'Ambiente', 'pedidosya' ),
				'type'     => 'select',
				'id'       => 'wc-peya-environment',
				'desc_tip' => true,
				'desc'     => __( 'PedidosYa te permite configurar el plugin en modo <i>Testing</i>, para que puedas realizar pruebas antes de comenzar a despachar.', 'pedidosya' ),
				'default'  => 'production',
				'options'  => array(
					'production' => __( 'Producción', 'pedidosya' ),
					'sandbox'    => __( 'Testeo', 'pedidosya' ),
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wc-peya_shipping_options',
			),
			array(
				'title' => __( 'Geolocalizacion', 'pedidosya' ),
				'desc'  => __( 'Esta opción te permitirá configurar una API KEY de Google para utilizar la Geolocalización al momento de Finalizar la Compra. De esta manera al comprador le será más facil completar su dirección, y obtendremos así una ubicación más precisa.', 'pedidosya' ),
				'type'  => 'title',
				'id'    => 'wc-peya_shipping_geolocalization',
			),
			array(
				'name' => __( 'Google API Key', 'pedidosya' ),
				'id'   => 'wc-peya-google-api-key',
				'type' => 'text',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wc-peya_shipping_geolocalization',
			),

			array(
				'title' => __( 'Notificaciones', 'pedidosya' ),
				'desc'  => ( empty( $webhook ) ) ? __( 'Un WebHook será configurado en PedidosYa cuando las credenciales sean guardadas.', 'pedidosya' ) : sprintf( __( 'Para recibir notificaciones acerca de tus envíos con PedidosYa, se ha configurado un WebHook automáticamente en PedidosYa con esta URL: <strong>%s</strong> con el método POST.', 'pedidosya' ), get_site_url( null, '/wc-api/wc-peya-orders-status' ) ),
				'type'  => 'title',
				'id'    => 'wc-peya_shipping_options_webhook',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wc-peya_shipping_options_webhook',
			),
			array(
				'title' => __( 'PedidosYa Express', 'pedidosya' ),
				'desc'  => __( 'Para que PedidosYa Express pueda programar tus envíos, es necesario activar el Cron de PedidosYa. El Cron se encargá de revisar a cada hora si hay pedidos programados y realiza el llamado al repartidor de PedidosYa.', 'pedidosya' ) . ( ( wp_next_scheduled( 'wc_peya_cron_update_order' ) ) ? __( ' Actualmente el cron <i>"wc_peya_cron_update_order"</i> se encuentra programado.', 'qc-peya' ) : '' ) . __( ' Si necesita ayuda puede consultar la documentación de Wordpress.org referida al tema accediendo al siguiente link <a href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/">https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/</a>.', 'pedidosya' ),
				'type'  => 'title',
				'id'    => 'wc-peya_shipping_options_express',
			),
			array(
				'name'    => '',
				'id'      => 'wc-peya-express-cron',
				'type'    => 'checkbox',
				'default' => ( wp_next_scheduled( 'wc_peya_cron_update_order' ) ? 'yes' : 'no' ),
				'desc'    => __( 'Activar Cron', 'pedidosya' ),

			),
			array(
				'type' => 'sectionend',
				'id'   => 'wc-peya_shipping_options_express',
			),
			array(
				'title' => __( 'Debug', 'pedidosya' ),
				'desc'  => sprintf( __( 'Puede habilitar el debug del plugin para realizar un seguimiento de la comunicación efectuada entre el plugin y la API de PedidosYa. Podrá ver el registro desde el menú <a href="%s">WooCommerce > Estado > Registros</a>.', 'pedidosya' ), esc_url( get_admin_url( null, 'admin.php?page=wc-status&tab=logs' ) ) ),
				'type'  => 'title',
				'id'    => 'wc-peya_shipping_options_debug',
			),
			array(
				'name'    => '',
				'id'      => 'wc-peya-debug',
				'type'    => 'checkbox',
				'default' => 'no',
				'desc'    => __( 'Habilitar Debug', 'pedidosya' ),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wc-peya_shipping_options_debug',
			),

		);

	}

}
