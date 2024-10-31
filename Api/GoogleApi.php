<?php
/**
 * Class GoogleApi
 *
 * @package Ecomerciar\PedidosYa\Api
 */

namespace Ecomerciar\PedidosYa\Api;

/**
 * PedidosYa API Class
 */
class GoogleApi extends ApiConnector implements ApiInterface {
	/**
	 * Class Constructor
	 *
	 * @param string $apikey
	 */
	public function __construct( string $apikey ) {
		$this->apikey = $apikey;
	}

	/**
	 * Validates APIKEY
	 *
	 * @return bool|string
	 */
	public function validateApiKey() {
		$url = $this->get_base_url() . $this->apikey;
		return $this->exec( 'GET', $url, array(), array() );
	}

	/**
	 * Get Base API Url
	 *
	 * @return string
	 */
	public function get_base_url() {
		return 'https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=';
	}
}
