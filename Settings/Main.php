<?php
/**
 * Class A main class that holds all our settings logic
 *
 * @package Ecomerciar\PedidosYa\Settings
 */

namespace Ecomerciar\PedidosYa\Settings;

use Ecomerciar\PedidosYa\Settings\Section;
use Ecomerciar\PedidosYa\Helper\Helper;
use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
use Ecomerciar\PedidosYa\Api\GoogleApi;

defined( 'ABSPATH' ) || exit;

class Main {
	/**
	 * Add PedidosYa Setting Tab
	 *
	 * @param Array $settings_tab Shipping Methods
	 * @return Array Shipping Methods
	 */
	public static function add_tab_settings( $settings_tab ) {
		$settings_tab['peya_shipping_options'] = __( 'Pedidos Ya' );
		return $settings_tab;
	}

	/**
	 * Get PedidosYa Setting Tab
	 *
	 * @param Array  $settings Shipping Methods
	 * @param string $current_section Section which is beaing processing
	 * @return Array Shipping Method Settings
	 */
	public static function get_tab_settings( $settings, $current_section ) {
		if ( 'peya_shipping_options' === $current_section ) {
			return Section::get();
		} else {
			return $settings;
		}
	}

	/**
	 * Get PedidosYa Settings
	 *
	 * @return Array Shipping Methods
	 */
	public static function get_settings() {
		return apply_filters( 'wc_settings_peya_shipping_options', Section::get() );
	}

	/**
	 * Update PedidosYa Settings
	 */
	public static function update_settings() {
		woocommerce_update_options( self::get_settings() );
	}

	public static function validate_credentials() {
		$sdk = new PeyaSdk();
		if ( ! $sdk->checkCredentials() ) {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="notice notice-error is-dismissible">';
					echo '<p>' . esc_html__( 'Las credenciales de PedidosYa son incorrectas.', 'pedidosya' ) . '</p>';
					echo '</div>';
				}
			);
		}
	}

	public static function validate_googleapi() {
		$ApiKey = Helper::get_setup_from_settings()['google-api-key'];
		if ( ! empty( $ApiKey ) ) {
			/*Validar Google Api Key válida*/
			$gApi     = new GoogleApi( $ApiKey );
			$response = $gApi->validateApiKey();
			if ( isset( $response['error_message'] ) ) {
				add_action(
					'admin_notices',
					function() {
						echo '<div class="notice notice-error is-dismissible">';
						echo '<p>' . esc_html__( 'El Google API Key es inválido.', 'pedidosya' ) . '</p>';
						echo '</div>';
					}
				);
			}
		}
	}

	public static function manage_cron() {
		$programCron = false;
		if ( isset( $_POST['wc-peya-express-cron'] ) && wp_unslash( $_POST['wc-peya-express-cron'] ) ) {
			$programCron = true;
		}
		// If cron should be scheduled but it's not.
		if ( $programCron && ! wp_next_scheduled( 'wc_peya_cron_update_order' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'wc_peya_schedule', 'wc_peya_cron_update_order' );
		}

		// If cron is scheduled but it should not.
		if ( ! $programCron && wp_next_scheduled( 'wc_peya_cron_update_order' ) ) {
			wp_clear_scheduled_hook( 'wc_peya_cron_update_order' );
		}

		// If cron should be scheduled but it's not.
		if ( $programCron && ! wp_next_scheduled( 'wc_peya_cron_update_settings' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'twicedaily', 'wc_peya_cron_update_settings' );
		}

		// If cron is scheduled but it should not.
		if ( ! $programCron && wp_next_scheduled( 'wc_peya_cron_update_settings' ) ) {
			wp_clear_scheduled_hook( 'wc_peya_cron_update_settings' );
		}
	}

	public static function update_schedule() {
		if ( ! Helper::set_peya_schedule_hs_array() ) {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="notice notice-error is-dismissible">';
					echo '<p>' . esc_html__( 'No fue posible actualizar el horario de flota de PedidosYa.', 'pedidosya' ) . '</p>';
					echo '</div>';
				}
			);
		} else {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="notice notice-success is-dismissible">';
					echo '<p>' . esc_html__( 'Se actualizó correctamente el horario de flota de PedidosYa.', 'pedidosya' ) . '</p>';
					echo '</div>';
				}
			);
		}
	}

	public static function update_callback() {
		if ( ! Helper::set_callback() ) {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="notice notice-error is-dismissible">';
					echo '<p>' . esc_html__( 'No fue posible actualizar el WEBHOOK de PedidosYa.', 'pedidosya' ) . '</p>';
					echo '</div>';
				}
			);
		} else {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="notice notice-success is-dismissible">';
					echo '<p>' . esc_html__( 'Se actualizó correctamente el WEBHOOK de PedidosYa.', 'pedidosya' ) . '</p>';
					echo '</div>';
				}
			);
		}
	}

	/**
	 * Save PedidosYa Settings - Execute validations
	 *
	 * @return Void
	 */
	public static function save() {

		global $current_section;

		if ( 'peya_shipping_options' === $current_section ) {
			woocommerce_update_options( self::get_settings() );

			// Validate Credentials.
			self::validate_credentials();

			// Validate GoogleApi Keys.
			self::validate_googleapi();

			// Activate/Deactivate Cron.
			self::manage_cron();

			// Update Schedules.
			self::update_schedule();

			// Update callbacks.
			self::update_callback();
		}
	}
}
