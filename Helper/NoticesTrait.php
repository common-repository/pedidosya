<?php
/**
 * Trait Notices
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

trait NoticesTrait {

	/**
	 * Checks if there are notices to print
	 *
	 * @return void
	 */
	public static function check_notices() {
		$notices_types = array( 'error', 'success', 'info' );
		foreach ( $notices_types as $type ) {
			$notices = get_transient( 'wc-peya-' . $type . '-notices' );
			if ( empty( $notices ) ) {
				continue;
			}
			foreach ( $notices as $notice ) {
				echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible">';
				echo '<p>' . esc_html( $notice ) . '</p>';
				echo '</div>';
			}
			delete_transient( 'wc-peya-' . $type . '-notices' );
		}
	}

	/**
	 * Create a generic notice
	 *
	 * @param string  $type
	 * @param string  $msg
	 * @param boolean $do_action
	 * @return void
	 */
	private static function add_notice( string $type, string $msg, bool $do_action = false ) {
		$notices = get_transient( 'wc-peya-' . $type . '-notices' );
		if ( ! empty( $notices ) ) {
			$notices[] = $msg;
		} else {
			$notices = array( $msg );
		}
		set_transient( 'wc-peya-' . $type . '-notices', $notices, 60 );
		if ( $do_action ) {
			do_action( 'admin_notices' );
		}
	}

	/**
	 * Adds an error message
	 *
	 * @param string  $msg
	 * @param boolean $do_action
	 * @return void
	 */
	public static function add_error( string $msg, bool $do_action = false ) {
		self::add_notice( 'error', $msg, $do_action );
	}

	/**
	 * Adds a success message
	 *
	 * @param string  $msg
	 * @param boolean $do_action
	 * @return void
	 */
	public static function add_success( string $msg, bool $do_action = false ) {
		self::add_notice( 'success', $msg, $do_action );
	}

	/**
	 * Adds an info message
	 *
	 * @param string  $msg
	 * @param boolean $do_action
	 * @return void
	 */
	public static function add_info( string $msg, bool $do_action = false ) {
		self::add_notice( 'info', $msg, $do_action );
	}
}
