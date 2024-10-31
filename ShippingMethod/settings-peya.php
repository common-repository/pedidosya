<?php
/**
 * Class Settings for PedidosYa rate shipping
 *
 * @package Ecomerciar\PedidosYa\ShippingMethod
 */

namespace Ecomerciar\PedidosYa\ShippingMethod;

use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Helper\Helper;

defined( 'ABSPATH' ) || exit;

// Que quede activado de Lunes a Viernes de 10am a 18hs.
$hsDefaultStart = '10:00';
$hsDefaultEnd   = '18:00';

$settings = array(
	'title'                                     => array(
		'title'       => __( 'Nombre Método Envío', 'pedidosya' ),
		'type'        => 'text',
		'description' => __( 'Nombre con el que aparecerá el tipo de envío en tu tienda.', 'pedidosya' ),
		'default'     => __( 'PedidosYa', 'pedidosya' ),
		'desc_tip'    => true,
	),

	// Sección Dirección.
	'wc-peya_pickup_address'                    => array(
		'title'       => __( 'Dirección de Origen', 'pedidosya' ),
		'description' => __( 'Ingresá la dirección y datos de contacto por dónde debemos retirar tus envíos.', 'pedidosya' ),
		'type'        => 'title',
	),

	'wc-peya-pickup-name'                       => array(
		'title' => __( 'Nombre del Punto de Retiro', 'pedidosya' ),
		'type'  => 'text',
	),
	'wc-peya-pickup-address'                    => array(
		'title'             => __( 'Calle y Número', 'pedidosya' ),
		'type'              => 'text',
		'custom_attributes' => array( 'required' => 'required' ),
	),
	'wc-peya-pickup-address2'                   => array(
		'title' => __( 'Piso y Departamento', 'pedidosya' ),
		'type'  => 'text',
	),

	'wc-peya-pickup-city'                       => array(
		'title'             => __( 'Ciudad', 'pedidosya' ),
		'type'              => 'text',
		'custom_attributes' => array( 'required' => 'required' ),
		'desc_tip'          => true,
		'description'       => __( 'Nombre de la ciudad del punto geográfico. Por ejemplo: Buenos Aires (AR), Antofagasta (CL), Montevideo (UY).', 'pedidosya' ),
	),
	'wc-peya-pickup-country'                    => array(
		'title'             => __( 'País', 'pedidosya' ),
		'type'              => 'select',
		'custom_attributes' => array(
			'required' => 'required',
			'disabled' => 'disabled',
		),
		'desc_tip'          => true,
		'description'       => __( 'Nombre del País', 'pedidosya' ),
		'options'           => Helper::get_countries(),
		'default'           => Helper::get_option( 'country' ),
		'sanitize_callback' => array( $this, 'sanitize_country' ),

	),
	'wc-peya-pickup-latitude'                   => array(
		'title'             => __( 'Latitud', 'pedidosya' ),
		'type'              => 'text',
		'custom_attributes' => array( 'required' => 'required' ),
		'sanitize_callback' => array( $this, 'sanitize_latlong' ),
	),
	'wc-peya-pickup-longitude'                  => array(
		'title'             => __( 'Longitud', 'pedidosya' ),
		'type'              => 'text',
		'custom_attributes' => array( 'required' => 'required' ),
		'sanitize_callback' => array( $this, 'sanitize_latlong' ),
	),
	'wc-peya-pickup-phone'                      => array(
		'title'             => __( 'Teléfono', 'pedidosya' ),
		'type'              => 'text',
		'custom_attributes' => array( 'required' => 'required' ),
		'desc_tip'          => true,
		'sanitize_callback' => array( $this, 'sanitize_phone' ),
		'description'       => __( 'El formato del teléfono debe ser:<br/>un prefijo opcional con el símbolo +<br/>solo números, no pueden tener letras<br/>el número debe comenzar con un número entre 1 y 9 y luego de 5 a 14 dígitos (0 a 9).', 'pedidosya' ),
	),
	'wc-peya-forwarding-agent-name'             => array(
		'title'             => __( 'Nombre de quién prepara los Envíos', 'pedidosya' ),
		'type'              => 'text',
		'custom_attributes' => array( 'required' => 'required' ),
		'desc_tip'          => false,
	),

	// Sección Precio Envío.
	'wc-peya_delivey_fee'                       => array(
		'title'       => __( 'Precios del Envío', 'pedidosya' ),
		'description' => __( 'Editá el precio que verán tus clientes al momento de cotizar.', 'pedidosya' ),
		'type'        => 'title',
	),

	'wc-peya-delivery-fee-type'                 => array(
		'title'             => __( 'Opciones de Cotización', 'pedidosya' ),
		'type'              => 'select',
		'custom_attributes' => array( 'required' => 'required' ),
		'desc_tip'          => true,
		'description'       => __( '<b>Predeterminado:</b> El precio que verá el cliente será calculado por PedidosYa en base a la distancia y volumen del envío.<br/><b>Precio Fijo:</b> El precio que verá el cliente será el expresado en el campo Precio Fijo.', 'pedidosya' ),
		'options'           => array(
			'DEFAULT' => __( 'Predeterminado', 'pedidosya' ),
			'FIXED'   => __( 'Precio Fijo', 'pedidosya' ),
		),
		'default'           => 'DEFAULT',
	),
	'wc-peya-fixed-delivery-fee-value'          => array(
		'title'             => __( 'Precio Fijo', 'pedidosya' ),
		'type'              => 'number',
		'default'           => 0,
		'custom_attributes' => array( 'required' => 'required' ),
		'desc_tip'          => true,
		'sanitize_callback' => array( $this, 'sanitize_cost' ),
		'description'       => __( 'El costo del envío que pagará el cliente será monto indicado en este campo.', 'pedidosya' ),
	),

	// Sección Envío Gratuito.
	'wc-peya_free_delivey'                      => array(
		'title'       => __( 'Envíos Gratis', 'pedidosya' ),
		'description' => __( 'Puedes ofrecer envíos gratis a partir del monto que desees. el subsidio correra por tu cuenta.', 'pedidosya' ),
		'type'        => 'title',
	),

	'wc-peya-free-delivery'                     => array(
		'title'   => __( 'Envíos Gratis', 'pedidosya' ),
		'label'   => __( 'Activar', 'pedidosya' ),
		'type'    => 'checkbox',
		'default' => 'no',
	),

	'wc-peya-free-delivery-from'                => array(
		'title'             => __( 'Monto Mínimo de Compra', 'pedidosya' ),
		'type'              => 'number',
		'default'           => 0,
		'custom_attributes' => array( 'required' => 'required' ),
		'desc_tip'          => true,
		'sanitize_callback' => array( $this, 'sanitize_cost' ),
		'description'       => __( 'El envío será gratuito para el comprador siempre que el pedido supere el monto indicado en este campo.', 'pedidosya' ),
	),


	// Sección Envio Express.
	'wc-peya_pickup_process_express'            => array(
		'title'       => __( 'Completá la información necesaria para utilizar PedidosYa Express.', 'pedidosya' ),
		'description' => __( 'Al habilitar esta opción, ni bien recibas una compra con PedidosYa, si esa compra está dentro del rango horario elegido, se enviará automáticamente al repartidor de PedidosYa.', 'pedidosya' ),
		'type'        => 'title',
	),

	'wc-peya-express'                           => array(
		'title'   => __( 'PedidosYa Express', 'pedidosya' ),
		'label'   => __( 'Activar', 'pedidosya' ),
		'type'    => 'checkbox',
		'default' => 'yes',
	),

	'wc-peya-packaging-delay'                   => array(
		'title'       => __( 'Demora de Empaquetado (Minutos)', 'pedidosya' ),
		'type'        => 'select',
		'desc_tip'    => true,
		'description' => __( 'El pedido se programará teniendo en cuenta esta demora, para darte tiempo a empaquetar el pedido.', 'pedidosya' ),
		'default'     => '25',
		'options'     => array(
			'10'  => '10 minutos',
			'25'  => '25 minutos',
			'50'  => '50 minutos',
			'75'  => '75 minutos',
			'100' => '100 minutos',
		),

	),

	// Sección Horarios.
	'wc-peya_pickup_schedule'                   => array(
		'title'       => ' ',
		'description' => __( 'Ingresá los días y horarios en que estarás realizando los despachos. Esta información será utilizada solo si PedidosYa Express se encuentra activado.', 'pedidosya' ),
		'type'        => 'title',
	),

	'wc-peya-pickup-schedule-monday-checked'    => array(
		'title'   => __( 'Lunes', 'pedidosya' ),
		'label'   => __( 'Activar', 'pedidosya' ),
		'type'    => 'checkbox',
		'default' => 'yes',
	),
	'wc-peya-pickup-schedule-monday-from'       => array(
		'title'   => __( 'Desde', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultStart,
		'options' => Helper::get_hs_array( 'monday' ),
	),
	'wc-peya-pickup-schedule-monday-to'         => array(
		'title'   => __( 'Hasta', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultEnd,
		'options' => Helper::get_hs_array( 'monday' ),
	),

	'wc-peya-pickup-schedule-tuesday-checked'   => array(
		'title'   => __( 'Martes', 'pedidosya' ),
		'label'   => __( 'Activar', 'pedidosya' ),
		'type'    => 'checkbox',
		'default' => 'yes',
	),
	'wc-peya-pickup-schedule-tuesday-from'      => array(
		'title'   => __( 'Desde', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultStart,
		'options' => Helper::get_hs_array( 'tuesday' ),
	),
	'wc-peya-pickup-schedule-tuesday-to'        => array(
		'title'   => __( 'Hasta', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultEnd,
		'options' => Helper::get_hs_array( 'tuesday' ),
	),

	'wc-peya-pickup-schedule-wednesday-checked' => array(
		'title'   => __( 'Miércoles', 'pedidosya' ),
		'label'   => __( 'Activar', 'pedidosya' ),
		'type'    => 'checkbox',
		'default' => 'yes',
	),
	'wc-peya-pickup-schedule-wednesday-from'    => array(
		'title'   => __( 'Desde', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultStart,
		'options' => Helper::get_hs_array( 'wednesday' ),
	),
	'wc-peya-pickup-schedule-wednesday-to'      => array(
		'title'   => __( 'Hasta', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultEnd,
		'options' => Helper::get_hs_array( 'wednesday' ),
	),

	'wc-peya-pickup-schedule-thursday-checked'  => array(
		'title'   => __( 'Jueves', 'pedidosya' ),
		'label'   => __( 'Activar', 'pedidosya' ),
		'type'    => 'checkbox',
		'default' => 'yes',
	),
	'wc-peya-pickup-schedule-thursday-from'     => array(
		'title'   => __( 'Desde', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultStart,
		'options' => Helper::get_hs_array( 'thursday' ),
	),
	'wc-peya-pickup-schedule-thursday-to'       => array(
		'title'   => __( 'Hasta', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultEnd,
		'options' => Helper::get_hs_array( 'thursday' ),
	),

	'wc-peya-pickup-schedule-friday-checked'    => array(
		'title'   => __( 'Viernes', 'pedidosya' ),
		'label'   => __( 'Activar', 'pedidosya' ),
		'type'    => 'checkbox',
		'default' => 'yes',
	),
	'wc-peya-pickup-schedule-friday-from'       => array(
		'title'   => __( 'Desde', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultStart,
		'options' => Helper::get_hs_array( 'friday' ),
	),
	'wc-peya-pickup-schedule-friday-to'         => array(
		'title'   => __( 'Hasta', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultEnd,
		'options' => Helper::get_hs_array( 'friday' ),
	),

	'wc-peya-pickup-schedule-saturday-checked'  => array(
		'title' => __( 'Sábado', 'pedidosya' ),
		'label' => __( 'Activar', 'pedidosya' ),
		'type'  => 'checkbox',
	),
	'wc-peya-pickup-schedule-saturday-from'     => array(
		'title'   => __( 'Desde', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultStart,
		'options' => Helper::get_hs_array( 'saturday' ),
	),
	'wc-peya-pickup-schedule-saturday-to'       => array(
		'title'   => __( 'Hasta', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultEnd,
		'options' => Helper::get_hs_array( 'saturday' ),
	),

	'wc-peya-pickup-schedule-sunday-checked'    => array(
		'title' => __( 'Domingo', 'pedidosya' ),
		'label' => __( 'Activar', 'pedidosya' ),
		'type'  => 'checkbox',
	),
	'wc-peya-pickup-schedule-sunday-from'       => array(
		'title'   => __( 'Desde', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultStart,
		'options' => Helper::get_hs_array( 'sunday' ),
	),
	'wc-peya-pickup-schedule-sunday-to'         => array(
		'title'   => __( 'Hasta', 'pedidosya' ),
		'type'    => 'select',
		'default' => $hsDefaultEnd,
		'options' => Helper::get_hs_array( 'sunday' ),
	),

);

return $settings;
