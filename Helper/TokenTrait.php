<?php
/**
 * Trait Token
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

trait TokenTrait {

				/**
				 * Get Token option
				 *
				 * @param string $option
				 * @return string
				 */
	public static function get_token_option( string $option ) {
					return self::get_option( $option );
	}

		/**
		 * Set Token option
		 *
		 * @param string $key
		 * @param string $value
		 */
	public static function set_token_option( string $key, $value ) {
		self::set_option( $key, $value );
	}


}
