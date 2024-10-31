<?php
/**
 * Trait Logger
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

trait LoggerTrait {
	private static $logger;
	private static $SOURCE = 'WooCommerce PedidosYa';

	/**
	 * Inits our logger singleton
	 *
	 * @return void
	 */
	public static function init() {
		if ( function_exists( 'wc_get_logger' ) && ! isset( self::$logger ) ) {
				self::$logger = wc_get_logger();
		}
	}

	/**
	 * Logs an info message
	 *
	 * @param mixed $msg
	 * @return void
	 */
	public static function log_info( $msg ) {
		self::$logger->info( wc_print_r( $msg, true ), array( 'source' => self::$SOURCE ) );
	}

	/**
	 * Logs an error message
	 *
	 * @param mixed $msg
	 * @return void
	 */
	public static function log_error( $msg ) {
		self::$logger->error( wc_print_r( $msg, true ), array( 'source' => self::$SOURCE ) );
	}

	/**
	 * Logs an warning message
	 *
	 * @param mixed $msg
	 * @return void
	 */
	public static function log_warning( $msg ) {
		self::$logger->warning( wc_print_r( $msg, true ), array( 'source' => self::$SOURCE ) );
	}

	/**
	 * Logs a debug message
	 *
	 * @param mixed $msg
	 * @return void
	 */
	public static function log_debug( $msg ) {
		self::$logger->debug( wc_print_r( $msg, true ), array( 'source' => self::$SOURCE ) );
	}
}
