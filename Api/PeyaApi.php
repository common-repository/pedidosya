<?php
/**
 * Class PeyaApi
 *
 * @package Ecomerciar\PedidosYa\Api
 */

namespace Ecomerciar\PedidosYa\Api;

use Ecomerciar\PedidosYa\Helper\Helper;

/**
 * PedidosYa API Class
 */
class PeyaApi extends ApiConnector implements ApiInterface {
	const API_BASE_URL         = 'https://courier-api.pedidosya.com/v1';
	const API_TOKEN_URL        = 'https://auth-api.pedidosya.com/v1';
	const API_TOKEN_EXPIRE_MIN = 40;
	const APPLICATION_JSON     = 'application/json';
	const ORIGIN_VALUE         = 'WooCommerce';
	/**
	 * Class Constructor
	 *
	 * @param array $settings
	 */
	public function __construct( array $settings = array() ) {
		$this->client_id     = $settings['client-id'];
		$this->client_secret = $settings['client-secret'];
		$this->grant_type    = 'password';
		$this->password      = $settings['password'];
		$this->username      = $settings['email'];

		$this->token_access           = '';
		$this->token_last_access_dttm = '';
		$this->token_error            = array();
	}


	/**
	 * Use Put API
	 *
	 * @param string $endpoint
	 * @param array  $body
	 * @param array  $headers
	 * @param string $baseUrl
	 * @return bool|string
	 */
	public function put( string $endpoint, array $body = array(), array $headers = array(), string $baseUrl = '' ) {
		if ( $this->isTokenAlive() ) {
			if ( empty( $baseUrl ) || '' === $baseUrl ) {
				$baseUrl = $this->get_base_url();
			}
			$url                      = $baseUrl . $endpoint;
			$headers['Authorization'] = $this->token_access;
			$headers['Content-Type']  = self::APPLICATION_JSON;
			$headers['accept']        = self::APPLICATION_JSON;
			$headers['Origin']        = self::ORIGIN_VALUE;

			return $this->exec( 'PUT', $url, $body, $headers );
		} else {
			return $this->token_error;
		}
	}

	/**
	 * Use Post API
	 *
	 * @param string $endpoint
	 * @param array  $body
	 * @param array  $headers
	 * @param string $baseUrl
	 * @return bool|string
	 */
	public function post( string $endpoint, array $body = array(), array $headers = array(), string $baseUrl = '' ) {
		if ( $this->isTokenAlive() ) {
			if ( empty( $baseUrl ) || '' === $baseUrl ) {
				$baseUrl = $this->get_base_url();
			}
			$url                      = $baseUrl . $endpoint;
			$headers['Authorization'] = $this->token_access;
			$headers['Content-Type']  = self::APPLICATION_JSON;
			$headers['accept']        = self::APPLICATION_JSON;
			$headers['Origin']        = self::ORIGIN_VALUE;

			return $this->exec( 'POST', $url, $body, $headers );
		} else {
			return $this->token_error;
		}
	}

	/**
	 * Use Get API
	 *
	 * @param string $endpoint
	 * @param array  $body
	 * @param array  $headers
	 * @return bool|string
	 */
	public function get( string $endpoint, array $body = array(), array $headers = array(), string $baseUrl = '' ) {
		if ( $this->isTokenAlive() ) {
			if ( empty( $baseUrl ) || '' === $baseUrl ) {
				$baseUrl = $this->get_base_url();
			}
			$url = $baseUrl . $endpoint;
			if ( ! empty( $body ) ) {
				$url .= '?' . http_build_query( $body );
			}

			$headers['Authorization'] = $this->token_access;
			$headers['Content-Type']  = self::APPLICATION_JSON;
			$headers['accept']        = self::APPLICATION_JSON;
			$headers['Origin']        = self::ORIGIN_VALUE;

			return $this->exec( 'GET', $url, $body, $headers );
		} else {
			return $this->token_error;
		}
	}

	/**
	 * Get Access Token API
	 */
	private function getAccessToken() {
		$endpoint = '/token';
		$baseUrl  = self::API_TOKEN_URL;
		$url      = $baseUrl . $endpoint;

		$body                  = array();
		$body['client_id']     = $this->client_id;
		$body['client_secret'] = $this->client_secret;
		$body['grant_type']    = $this->grant_type;
		$body['password']      = $this->password;
		$body['username']      = $this->username;

		$headers                 = array();
		$headers['Content-Type'] = self::APPLICATION_JSON;
		$headers['accept']       = self::APPLICATION_JSON;

		$res = $this->exec( 'POST', $url, $body, $headers );

		if ( isset( $res['access_token'] ) ) {
			$this->token_access           = $res['access_token'];
			$this->token_last_access_dttm = current_datetime();
			$this->token_error            = array();
		}
		if ( isset( $res['code'] ) ) {
			$this->token_access           = '';
			$this->token_last_access_dttm = '';
			$this->token_error            = $res;
		}

		$h = new Helper();
		$h->set_token_option( 'token-access', $this->token_access );
		$h->set_token_option( 'token_last_access_dttm', $this->token_last_access_dttm );

	}

	/**
	 * Check if Token is Alive, If not Gets a newone
	 *
	 * @return bool
	 */
	public function isTokenAlive() {
		$return                       = true;
		$h                            = new Helper();
		$this->token_access           = $h->get_token_option( 'token-access' );
		$this->token_last_access_dttm = $h->get_token_option( 'token_last_access_dttm' );

		if ( empty( $this->token_access ) || '' === $this->token_access ) {
			$this->getAccessToken();

			// No se obtuvo token.
			if ( empty( $this->token_access ) || '' === $this->token_access ) {
				$return = false;
			}
		}

		if ( $return ) {
			$now     = current_datetime();
			$now     = $now->diff( $this->token_last_access_dttm );
			$minutes = $now->i + $now->h * 60 + $now->d * 60 * 24 + $now->m * 60 * 24 * 30 + $now->y * 60 * 24 * 30 * 12;
			if ( $minutes > self::API_TOKEN_EXPIRE_MIN ) {
				$this->getAccessToken();
			}

			if ( ! empty( $this->token_error ) ) {
				$return = false;
			}

			if ( ! ( ! empty( $this->token_access ) && '' !== $this->token_access ) ) {
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * Check Credentials from settings
	 *
	 * @return bool
	 */
	public function checkCredentials() {
		$this->token_access           = '';
		$this->token_last_access_dttm = '';

		$h = new Helper();
		$h->set_token_option( 'token-access', $this->token_access );
		$h->set_token_option( 'token_last_access_dttm', $this->token_last_access_dttm );

		return $this->isTokenAlive();
	}


	/**
	 * Get Base API Url depending on Plugin Mode: Sandbox | Production
	 *
	 * @return string
	 */
	public function get_base_url() {
		return self::API_BASE_URL;
	}

}