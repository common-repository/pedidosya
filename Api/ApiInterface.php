<?php
/**
 * Interface ApiInterface
 *
 * @package Ecomerciar\PedidosYa\Api
 */

namespace Ecomerciar\PedidosYa\Api;

/**
 * API Interface Class
 */
interface ApiInterface {
	public function get( string $endpoint, array $body = array(), array $headers = array());
	public function post( string $endpoint, array $body = array(), array $headers = array());
	public function put( string $endpoint, array $body = array(), array $headers = array());
	public function delete( string $endpoint, array $body = array(), array $headers = array());
}
