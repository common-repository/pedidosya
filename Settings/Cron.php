<?php
/**
 * Class Cron Processor's Main
 *
 * @package Ecomerciar\PedidosYa\Settings
 */

namespace Ecomerciar\PedidosYa\Settings;

use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Helper\Helper;

defined( 'ABSPATH' ) || exit;

class Cron {


	/**
	 * Run Cron Action
	 */
	public static function run_cron() {

		Helper::log( __( 'Ejecución Cron Settings PedidosYa', 'pedidosya' ) );

		// Validate Credentials.
		$sdk = new PeyaSdk();
		if ( $sdk->checkCredentials() ) {
			if ( ! wp_next_scheduled( 'wc_peya_cron_update_order' ) ) {
				wp_schedule_event( current_time( 'timestamp' ), 'wc_peya_schedule', 'wc_peya_cron_update_order' );
			}
			// Update Schedules.
			Helper::set_peya_schedule_hs_array();
			// Update callbacks.
			Helper::set_callback();
		}
	}
}
