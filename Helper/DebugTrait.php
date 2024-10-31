<?php
/**
 * Trait Debug
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

trait DebugTrait {

	public static function log( $log ) {
		if ( 'no' !== self::get_option( 'debug' ) ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				self::log_debug( print_r( $log, true ) );
			} else {
				self::log_debug( $log );
			}
		}
	}
}