<?php
/**
 * Class Our main Shipping method
 *
 * @package Ecomerciar\PedidosYa\ShippingMethod
 */

namespace Ecomerciar\PedidosYa\ShippingMethod;

use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use WC_Shipping_Method;

defined( 'ABSPATH' ) || class_exists( '\WC_Shipping_Method' ) || exit;

class WC_Peya extends \WC_Shipping_Method {
	/**
	 * Default constructor
	 *
	 * @param int $instance_id Shipping Method Instance from Order
	 * @return void
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'peya';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'PedidosYa', 'pedidosya' );
		$this->method_description = __( 'Permite a tus clientes recibir sus pedidos con PedidosYa.', 'pedidosya' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();

	}

	/**
	 * Init user set variables.
	 */
	public function init() {
		$this->instance_form_fields = include 'settings-peya.php';
		$this->title                = $this->get_option( 'title' );
		$this->retirement           = $this->get_option( 'retirement' );

		// Save settings in admin if you have any defined
		add_action(
			'woocommerce_update_options_shipping_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
	 */
	public function process_admin_options() {
		parent::process_admin_options();

		$sdk = new PeyaSdk();
		if ( ! empty( $this->get_post_data()['woocommerce_peya_wc-peya-pickup-latitude'] ) && ! empty( $this->get_post_data()['woocommerce_peya_wc-peya-pickup-longitude'] ) ) {
			$res = $sdk->getWaypointImage( $this->get_post_data()['woocommerce_peya_wc-peya-pickup-address'], $this->get_post_data()['woocommerce_peya_wc-peya-pickup-city'], $this->get_post_data()['woocommerce_peya_wc-peya-pickup-latitude'], $this->get_post_data()['woocommerce_peya_wc-peya-pickup-longitude'] );
		} else {
			$res = $sdk->getWaypointImage( $this->get_post_data()['woocommerce_peya_wc-peya-pickup-address'], $this->get_post_data()['woocommerce_peya_wc-peya-pickup-city'] );
		}

		Helper::log( 'TEST NUEVO'.$res );

		if ( isset( $res['coverageStaticMap'] ) ) {
			update_option( 'wc-peya-coveragestaticmap-' . $this->instance_id, $res['coverageStaticMap'] );
			$this->add_error( '<p>' . __( '¿La dirección de despacho ingresada corresponde con la siguiente imagen? Si no es así, revisela en el método de envío por favor.', 'pedidosya' ) . "</p> <img src='data:image/png;base64, " . $res['coverageStaticMap'] . "' >" );
		} else {
			$this->add_error( '<p>' . __( 'PedidosYa no puede ubicar la dirección de despacho ingresada.', 'pedidosya' ) . '</p>' );
		}

		if ( ! helper::check_shipping_zone_overlapping() ) {
			$this->add_error( '<p>' . __( '<strong>Solapamiento de Zonas de Envíos:</strong> Existe al menos un solapamiento de Zonas de Envíos en su configuración, esto puede provocar que sus métodos de envío no se muestren correctamente.', 'pedidosya' ) . '</p>' );
		}

	}


	/**
	 * Validate Text Fields
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function validate_text_field( $key, $value ) {
		return parent::validate_text_field( $key, $value );
	}

	/**
	 * Sanitize Phone number.
	 *
	 * @param string $value
	 * @return $value
	 */
	public function sanitize_phone( $value ) {
		return preg_replace( '/[^0-9]/', '', $value );
	}

	/**
	 * Sanitize Country -> force to use setting country.
	 *
	 * @param string $value
	 * @return $value
	 */
	public function sanitize_country( $value ) {
		return Helper::get_option( 'country' );
	}

	/**
	 * Sanitize the cost field.
	 *
	 * @return string
	 */
	public function sanitize_cost( $value ) {
		return filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	}



	/**
	 * Sanitize the latitude and longitude fields
	 *
	 * @return string
	 */
	public function sanitize_latlong( $value ) {
		$value = str_replace( ',', '.', $value );
		return filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	}

	/**
	 * Calculate the shipping costs.
	 *
	 * @param array $package Package of items from cart.
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		$rate = array(
			'label'    => $this->get_option( 'title' ), // Label for the rate
			'cost'     => '0', // Amount for shipping or an array of costs (for per item shipping)
			'taxes'    => '', // Pass an array of taxes, or pass nothing to have it calculated for you, or pass 'false' to calculate no tax for this method
			'calc_tax' => 'per_order', // Calc tax per_order or per_item. Per item needs an array of costs passed via 'cost'
			'package'  => $package,
			'term'     => '',
		);

		$has_costs = false;

		$items = Helper::get_items_from_cart( WC()->cart );
		if ( false === $items ) {
			return;
		}

		// Get Coords;
		$coords = array(
			'lat' => '',
			'lng' => '',
		);
		if ( ! empty( $this->get_post_data()['post_data'] ) ) {
			parse_str( $this->get_post_data()['post_data'], $post_data );
			if ( isset( $post_data['shipping_wc_lat'] ) && ! empty( $post_data['shipping_wc_lat'] ) && isset( $post_data['ship_to_different_address'] ) && $post_data['ship_to_different_address'] ) {
				$coords['lat'] = $post_data['shipping_wc_lat'];
				$coords['lng'] = $post_data['shipping_wc_lng'];
			} else {
				$coords['lat'] = $post_data['billing_wc_lat'];
				$coords['lng'] = $post_data['billing_wc_lng'];
			}
		}

		$sdk     = new PeyaSdk();
		$resCost = $sdk->get_price( $items, $package['destination'], $this->id, $this->instance_id, $coords );

		if ( ! $resCost || 0 === $resCost ) {
			return;
		}

		if ( $resCost['price'] > 0 ) {
			$has_costs    = true;
			$rate['cost'] = $resCost['price'];
			$settings     = Helper::get_shipping_settings( $this->id, $this->instance_id );
			// Check Fixed Price.
			if ( 'FIXED' === $settings['delivery-fee-type'] ) {
				$rate['cost'] = floatval( $settings['fixed-delivery-fee-value'] );
			}

			if ( 'yes' === $settings['free-delivery']
			&& floatval( $settings['free-delivery-from'] ) <= floatval( $package['contents_cost'] ) ) {
				$rate['cost']  = 0;
				$rate['label'] = $this->get_option( 'title' ) . ' ' . __( ' - Gratis', 'pedidosya' );
			}

			// Check Promesa de Envíoptimize
			if ( Helper::is_express_available( $this->id, $this->instance_id ) === 'yes' ) {
				$promiseString = '';
				$deliveryDate  = helper::convert_from_UTC_2_gtm( $resCost['deliverytime'] );
				$now           = get_date_from_gmt( 'now' );
				$diff          = abs( strtotime( $deliveryDate ) - strtotime( $now ) );
				
				$deliveryTimeTomorrow = Helper::get_tomorrow_express_available( $this->id, $this->instance_id );
			  
				Helper::log( 'LOG $deliveryDate: : '. print_r( $deliveryDate, true ));
				Helper::log( 'LOG now: : '. print_r( date( 'Y-m-d', strtotime( $now ) ), true ));

				$days    = floor( $diff / ( 60 * 60 * 24 ) );
				$minutes = floor( $diff / 60 ) + 60;

				if ( $minutes <= 120 ) {
					$promiseString = __( ' - Entrega en menos de 2hs', 'pedidosya' );
				}

				if ( empty( $promiseString ) && $minutes <= 180 ) {
					$promiseString = __( ' - Entrega en menos de 3hs', 'pedidosya' );
				}

				if ( empty( $promiseString ) ) {
					
					if ( 0 === intval($days) && date( 'Y-m-d', strtotime( $deliveryDate ) ) === date( 'Y-m-d', strtotime( $now ) ) ) {
						$promiseString = __( ' - Llega Hoy', 'pedidosya' );
					}
					if ( 0 === intval($days) && date( 'Y-m-d', strtotime( $deliveryDate ) ) !== date( 'Y-m-d', strtotime( $now ) ) ) {
						$promiseString = __( ' - Llega Mañana', 'pedidosya' );
					}
				    if ( 1 === intval($days) ) {
					    $promiseString = __( ' - Llega Mañana', 'pedidosya' );
					}
					if ( intval($days) >= 1 ) {
						$weekday = date( 'w', strtotime( $deliveryDate ) );
						switch ( $weekday ) {
							case 0:
								$promiseString = __( ' - Llega el Domingo', 'pedidosya' );
								break;
							case 1:
								$promiseString = __( ' - Llega el Lunes', 'pedidosya' );
								break;
							case 2:
								$promiseString = __( ' - Llega el Martes', 'pedidosya' );
								break;
							case 3:
								$promiseString = __( ' - Llega el Miercoles', 'pedidosya' );
								break;
							case 4:
								$promiseString = __( ' - Llega el Jueves', 'pedidosya' );
								break;
							case 5:
								$promiseString = __( ' - Llega el Viernes', 'pedidosya' );
								break;
							case 6:
								$promiseString = __( ' - Llega el Sábado', 'pedidosya' );
								break;
						}
					}
				}

				$rate['label'] = $rate['label'] . $promiseString;
			}
		}

		if ( $has_costs ) {
			// Register the rate.
			$this->add_rate( $rate );
		}
	}

	/**
	 * Adds Map Image after Shipping Rate on PedidosYa Method
	 *
	 * @param \WC_Shipping_Rate $method
	 * @return void
	 */
	public static function after_shipping_rate( \WC_Shipping_Rate $method ) {
		if ( 'peya' === $method->method_id ) {
			?>
			<script>
			 var peyaEdit = jQuery('label[for=shipping_method_0_<?php echo esc_attr( $method->method_id . $method->instance_id ); ?>]');
			 peyaEdit.after('<img src="<?php echo esc_url( Helper::get_assets_folder_url() . '/img/pedidosya_logo.png' ); ?>" alt="pedidosya logo"></img>');
			 </script>
			<?php
			if ( WC()->session->get( 'chosen_shipping_methods' )[0] === $method->id ) {
				$package = WC()->shipping->get_packages()[0];
				echo '<div class="peya-shipping-rate">';
				$sdk = new PeyaSdk();
				$res = $sdk->getWaypointImage( $package['destination']['address_1'], $package['destination']['city'] );
				if ( isset( $res['coverageStaticMap'] ) ) {
					echo "<img src='data:image/png;base64, " . esc_html( $res['coverageStaticMap'] ) . "' >";
				}
				echo '</div>';
			}

			return;
		}

	}

}