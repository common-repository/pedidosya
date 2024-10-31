<?php
/**
 * Class PedidosYa SDK Main
 *
 * @package Ecomerciar\PedidosYa\Sdk
 */

namespace Ecomerciar\PedidosYa\Sdk;

use Ecomerciar\PedidosYa\Api\PeyaApi;
use Ecomerciar\PedidosYa\Helper\Helper;

class PeyaSdk {

	/**
	 * Constructor Method
	 */
	public function __construct() {
		$this->settings = Helper::get_setup_from_settings();
		$this->api      = new PeyaApi( $this->settings );
	}

	/**
	 * get Categories from Peya
	 *
	 * @return array
	 */
	public function getCategories() {
		$endpoint = '/categories';
		$data     = array();
		return $this
			->api
			->get( $endpoint, $data );
	}

	/**
	 * get Fleet Schedules from Peya
	 *
	 * @return array
	 */
	public function getSchedules() {
		$endpoint = '/schedules';
		$data     = array();
		return $this
			->api
			->get( $endpoint, $data );
	}

	/**
	 * get Waypoint Image
	 *
	 * @param string $address
	 * @param string $city
	 * @return array
	 */
	public function getWaypointImage( $address, $city, $lat = null, $long = null ) {
		$endpoint = '/estimates/coverage?mapRequired=true';
		if ( ! empty( $lat ) && ! empty( $long ) ) {
			$data = array(
				'waypoints' => array(
					array(
						'addressStreet' => $address,
						'city'          => $city,
						'latitude'      => floatval( $lat ),
						'longitude'     => floatval( $long ),
					),
				),

			);
		} else {
			$data = array(
				'waypoints' => array(
					array(
						'addressStreet' => $address,
						'city'          => $city,
					),
				),

			);
		}

			return $this
				->api
				->post( $endpoint, $data );
	}

	/**
	 * get price estimation
	 *
	 * @param array $item
	 * @param array $destination
	 * @param int   $shipping_method_id
	 * @param int   $shipping_method_instance
	 * @return int
	 */
	public function get_price( $items, $destination, $shipping_method_id, $shipping_method_instance, $coords = array(
		'lat' => '',
		'lng' => '',
	) ) {
		$price    = array(
			'price'        => 0,
			'deliverytime' => null,
		);
		$response = $this->process_cart( $items, $destination, $shipping_method_id, $shipping_method_instance, $coords );
		if ( ! $response ) {
			$price['price'] = 0;
		}
		if ( isset( $response['deliveryTime'] ) ) {
			$price['price']        = $response['price']['total'];
			$price['deliverytime'] = $response['deliveryTime'];
		} else {
			$price['price'] = 0;
		}
		return $price;
	}

	/**
	 * process order from cart
	 *
	 * @param array $item
	 * @param array $destination
	 * @param int   $shipping_method_id
	 * @param int   $shipping_method_instance
	 * @param array $coords
	 * @return array
	 */
	public function process_cart( $items, $destination, $shipping_method_id, $shipping_method_instance, $coords = array(
		'lat' => '',
		'lng' => '',
	) ) {
		$endpoint = '/estimates/shippings';

		$grouped_items = Helper::group_items( $items );
		$settings      = $this->settings;

		$shipping = Helper::get_shipping_settings( $shipping_method_id, $shipping_method_instance );

		$dataItems      = array();
		$totalWeight    = 0;
		$totalDimention = 0;

		foreach ( $grouped_items as $item ) {
			$totalWeight    += $item['weight'] * $item['quantity'];
			$totalDimention += $item['wc-peya-product-size'] * $item['quantity'];
			array_push(
				$dataItems,
				array(
					'value'       => $item['price'],
					'description' => $item['description'],
					'sku'         => $item['sku'],
					'quantity'    => $item['quantity'],
					'volume'      => $item['wc-peya-product-size'] * $item['quantity'],
					'weight'      => $item['weight'] * $item['quantity'],
				)
			);
		}

		// Volumen max: 72.000 cm3 <—> Peso max: 10Kgs.
		if ( $totalWeight > 10 || $totalDimention > 72000 ) {
			return array();
		}

		$data                = array();
		$data['referenceId'] = $this->generate_track_code( 'PRICE' );
		if ( 'production' !== $settings['environment'] ) {
			$data['isTest'] = true;
		}

		$data['deliveryTime'] = Helper::get_today_express_available( $shipping_method_id, $shipping_method_instance );
		// IS Fleet Time???
		if ( false === $data['deliveryTime'] ) {
			// Today when branch is open.
			$data['deliveryTime'] = Helper::get_today_next_express_available( $shipping_method_id, $shipping_method_instance );
			if ( false === $data['deliveryTime'] ) {
				$data['deliveryTime'] = Helper::get_tomorrow_express_available( $shipping_method_id, $shipping_method_instance );
			}
		}

		if ( false === $data['deliveryTime'] ) {
			$data['deliveryTime'] = Helper::get_now_delivery_time_UTC();
		}

		$data['items']  = $dataItems;
		$data['volume'] = $totalDimention;
		$data['weight'] = $totalWeight;

		$data['waypoints'] = array(
			array(
				'type'              => 'PICK_UP',
				'addressStreet'     => $shipping['pickup-address'],
				'addressAdditional' => $shipping['pickup-address2'],
				'city'              => $shipping['pickup-city'],
				'latitude'          => ( ! empty( $shipping['pickup-latitude'] ) ) ? floatval( $shipping['pickup-latitude'] ) : '',
				'longitude'         => ( ! empty( $shipping['pickup-longitude'] ) ) ? floatval( $shipping['pickup-longitude'] ) : '',
				'phone'             => $shipping['pickup-phone'],
				'name'              => $shipping['pickup-name'] . ' (' . $shipping['forwarding-agent-name'] . ')',
				'order'             => 1,
			),
			array(
				'type'              => 'DROP_OFF',
				'addressStreet'     => $destination['address_1'],
				'addressAdditional' => $destination['address_2'],
				'city'              => $destination['city'],
				'latitude'          => ( ! empty( $coords['lat'] ) ) ? floatval( $coords['lat'] ) : '',
				'longitude'         => ( ! empty( $coords['lng'] ) ) ? floatval( $coords['lng'] ) : '',
				'phone'             => $shipping['pickup-phone'],
				'name'              => 'PRICING',
				'order'             => 2,
			),
		);

		return $this->api->post( $endpoint, $data );
	}

	/**
	 * Process Order with PedidosYa API
	 *
	 * @param WC_Order $order Order to process
	 * @param string   $deliveryTime
	 * @return array
	 */
	public function process_order( \WC_Order $order, string $deliveryTime = '' ) {
		$endpoint      = '/shippings';
		$customer      = Helper::get_customer_from_order( $order );
		$itemList      = array();
		$items         = Helper::get_items_from_order( $order );
		$grouped_items = Helper::group_items( $items );
		$settings      = $this->settings;

		$shipping = Helper::get_shipping_settings_from_order( $order );

		$dataItems      = array();
		$totalWeight    = 0;
		$totalDimention = 0;

		foreach ( $grouped_items as $item ) {
			$totalWeight    += $item['weight'] * $item['quantity'];
			$totalDimention += $item['wc-peya-product-size'] * $item['quantity'];
			array_push(
				$dataItems,
				array(
					'value'       => $item['price'],
					'description' => $item['description'],
					'sku'         => $item['sku'],
					'quantity'    => $item['quantity'],
					'volume'      => $item['wc-peya-product-size'] * $item['quantity'],
					'weight'      => $item['weight'] * $item['quantity'],
				)
			);
		}

		// Volumen max: 72.000 cm3 <—> Peso max: 10Kgs.
		if ( $totalWeight > 10 || $totalDimention > 72000 ) {
			return array();
		}

		$data                = array();
		$data['referenceId'] = $order->get_id();
		if ( 'production' !== $settings['environment'] ) {
			$data['isTest'] = true;
		}

		if ( empty( $deliveryTime ) ) {
			$deliveryTime = Helper::get_now_delivery_time_UTC();
		}

		$data['deliveryTime']     = $deliveryTime;
		$data['notificationMail'] = $customer['email'];
		$data['items']            = $dataItems;
		$data['volume']           = $totalDimention;
		$data['weight']           = $totalWeight;

		$data['waypoints'] = array(
			array(
				'type'              => 'PICK_UP',
				'addressStreet'     => $shipping['pickup-address'],
				'addressAdditional' => $shipping['pickup-address2'],
				'city'              => $shipping['pickup-city'],
				'latitude'          => ( ! empty( $shipping['pickup-latitude'] ) ) ? floatval( $shipping['pickup-latitude'] ) : '',
				'longitude'         => ( ! empty( $shipping['pickup-longitude'] ) ) ? floatval( $shipping['pickup-longitude'] ) : '',
				'phone'             => $shipping['pickup-phone'],
				'name'              => $shipping['pickup-name'] . ' ' . '(' . $shipping['forwarding-agent-name'] . ')',
				'order'             => 1,
			),
			array(
				'type'              => 'DROP_OFF',
				'addressStreet'     => $customer['address_1'],
				'addressAdditional' => $customer['address_2'],
				'city'              => $customer['locality'],
				'latitude'          => ( ! empty( $customer['lat'] ) ) ? floatval( $customer['lat'] ) : '',
				'longitude'         => ( ! empty( $customer['lng'] ) ) ? floatval( $customer['lng'] ) : '',
				'phone'             => $customer['phone'],
				'name'              => $customer['full_name'],
				'order'             => 2,
			),
		);

		return $this->api->post( $endpoint, $data );
	}

	/**
	 * confirm shipping
	 *
	 * @param string $id
	 * @param array  $destination
	 * @return array
	 */
	public function confirm_order( string $id ) {
		$endpoint = '/shippings/' . $id . '/confirm';
		$data     = array();

		return $this->api->post( $endpoint, $data );
	}


	/**
	 * cancel shipping
	 *
	 * @param string $id
	 * @param string $reason
	 *
	 * @return array
	 */
	public function cancel_order( string $id, string $reason ) {
		$endpoint = '/shippings/' . $id . '/cancel';
		$data     = array();

		$data['reasonText'] = $reason;

		return $this->api->post( $endpoint, $data );
	}

	/**
	 * Generates Unique ID for Trancking Codes
	 *
	 * @param int $order_id Order Identifier
	 * @return String Unique ID
	 */
	public function generate_track_code( $order_id ) {
		$prefix = 'WC' . $order_id;
		return strtoupper( uniqid( $prefix, false ) );
	}

	/**
	 * Checks if AutoProcess is enabled on PedidosYa Settings
	 *
	 * @return bool
	 */
	public function is_auto_process() {
		if ( $this->settings['process-order-status'] === '0' ) {
			return false;
		}
		return true;
	}

	/**
	 * Get Auto Process Status from Settings
	 *
	 * @return string
	 */
	public function get_auto_process_status() {
		return $this->settings['process-order-status'];
	}

	/**
	 * Set Webhook Callback
	 *
	 * @return array
	 */
	public function set_callback() {
		$endpoint          = '/callbacks';
		$data              = array();
		$data['callbacks'] = array(
			array(
				'url'              => get_site_url( null, '/wc-api/wc-peya-orders-status' ),
				'authorizationKey' => $this->settings['client-secret'],
				'topic'            => 'SHIPPING_STATUS',
				'notificationType' => 'WEBHOOK',
			),
		);

		return $this
			->api
			->put( $endpoint, $data );

	}

	/**
	 * Check PedidosYa Credentials from settings
	 *
	 * @return array
	 */
	public function checkCredentials() {
		return $this
			->api
			->checkCredentials();
	}


	/**
	 * get Proof of Delivery
	 *
	 * @param string $id
	 * @param array  $destination
	 * @return array
	 */
	public function get_proof_of_delivery( string $id ) {
		$endpoint = '/shippings/' . $id . '/proofOfDelivery';
		$data     = array();

		return $this->api->get( $endpoint, $data );
	}

}
