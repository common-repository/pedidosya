<?php
/**
 * Trait Settings
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

use Ecomerciar\PedidosYa\Sdk\PeyaSdk;

trait SettingsTrait {
	private static $INIT_HOUR = '00:00';
	private static $END_HOUR  = '23:59';
				/**
				 * Gets a plugin option
				 *
				 * @param string  $key
				 * @param boolean $default
				 * @return mixed
				 */
	public static function get_option( string $key, $default = false ) {
					return get_option( 'wc-peya-' . $key, $default );
	}

				/**
				 * Gets a plugin option
				 *
				 * @param string  $key
				 * @param boolean $default
				 * @return mixed
				 */
	public static function set_option( string $key, $default = false ) {
					return update_option( 'wc-peya-' . $key, $default );
	}

				/**
				 * Gets the seller settings
				 *
				 * @return array
				 */
	public static function get_setup_from_settings() {
					return array(
						'client-id'      => self::get_option( 'client-id' ),
						'client-secret'  => self::get_option( 'client-secret' ),
						'email'          => self::get_option( 'email' ),
						'password'       => self::get_option( 'password' ),
						'environment'    => self::get_option( 'environment' ),
						'google-api-key' => self::get_option( 'google-api-key' ),
					);
	}

				/**
				 * Get Hours List Option for a WeekDay from PedidosYa
				 *
				 * @param string $day
				 * @return array
				 */
	public static function get_hs_array( string $day ) {
		$peyaSchedule = self::get_peya_schedule_hs_array();
		if ( ! isset( $peyaSchedule[ $day ] ) ) {
			return array( 'N/D' => __( 'No Disponible', 'pedidosya' ) );
		}

		$hs = array(
			'00:00' => __( '00:00', 'pedidosya' ),
			'00:30' => __( '00:30', 'pedidosya' ),
			'01:00' => __( '01:00', 'pedidosya' ),
			'01:30' => __( '01:30', 'pedidosya' ),
			'02:00' => __( '02:00', 'pedidosya' ),
			'02:30' => __( '02:30', 'pedidosya' ),
			'03:00' => __( '03:00', 'pedidosya' ),
			'03:30' => __( '03:30', 'pedidosya' ),
			'04:00' => __( '04:00', 'pedidosya' ),
			'04:30' => __( '04:30', 'pedidosya' ),
			'05:00' => __( '05:00', 'pedidosya' ),
			'05:30' => __( '05:30', 'pedidosya' ),
			'06:00' => __( '06:00', 'pedidosya' ),
			'06:30' => __( '06:30', 'pedidosya' ),
			'07:00' => __( '07:00', 'pedidosya' ),
			'07:30' => __( '07:30', 'pedidosya' ),
			'08:00' => __( '08:00', 'pedidosya' ),
			'08:30' => __( '08:30', 'pedidosya' ),
			'09:00' => __( '09:00', 'pedidosya' ),
			'09:30' => __( '09:30', 'pedidosya' ),
			'10:00' => __( '10:00', 'pedidosya' ),
			'10:30' => __( '10:30', 'pedidosya' ),
			'11:00' => __( '11:00', 'pedidosya' ),
			'11:30' => __( '11:30', 'pedidosya' ),
			'12:00' => __( '12:00', 'pedidosya' ),
			'12:30' => __( '12:30', 'pedidosya' ),
			'13:00' => __( '13:00', 'pedidosya' ),
			'13:30' => __( '13:30', 'pedidosya' ),
			'14:00' => __( '14:00', 'pedidosya' ),
			'14:30' => __( '14:30', 'pedidosya' ),
			'15:00' => __( '15:00', 'pedidosya' ),
			'15:30' => __( '15:30', 'pedidosya' ),
			'16:00' => __( '16:00', 'pedidosya' ),
			'16:30' => __( '16:30', 'pedidosya' ),
			'17:00' => __( '17:00', 'pedidosya' ),
			'17:30' => __( '17:30', 'pedidosya' ),
			'18:00' => __( '18:00', 'pedidosya' ),
			'18:30' => __( '18:30', 'pedidosya' ),
			'19:00' => __( '19:00', 'pedidosya' ),
			'19:30' => __( '19:30', 'pedidosya' ),
			'20:00' => __( '20:00', 'pedidosya' ),
			'20:30' => __( '20:30', 'pedidosya' ),
			'21:00' => __( '21:00', 'pedidosya' ),
			'21:30' => __( '21:30', 'pedidosya' ),
			'22:00' => __( '22:00', 'pedidosya' ),
			'22:30' => __( '22:30', 'pedidosya' ),
			'23:00' => __( '23:00', 'pedidosya' ),
			'23:30' => __( '23:30', 'pedidosya' ),
			'23:59' => __( '23:59', 'pedidosya' ),
		);

		foreach ( $hs as $key => $value ) {
			if ( $key < $peyaSchedule[ $day ]['from'] || $key > $peyaSchedule[ $day ]['to'] ) {
				// exclude.
				unset( $hs[ $key ] );
			}
		}
		return $hs;
	}

				/**
				 * Get Peya Fleet Schedule
				 *
				 * @return array
				 */
	public static function get_peya_schedule_hs_array() {
		$peya_schedules = array();
		$peya_schedules = get_option( 'wc-peya-schedule-list' );

		if ( empty( $peya_schedules ) ) {
			  self::set_peya_schedule_hs_array();
				$peya_schedules = get_option( 'wc-peya-schedule-list' );
		}

		if ( empty( $peya_schedules ) ) {
			$initSchedule = array(
				'from'    => self::$INIT_HOUR,
				'to'      => self::$END_HOUR,
				'fromUTC' => self::$INIT_HOUR,
				'toUTC'   => self::$END_HOUR,
			);

					$peya_schedules = array(
						'monday'    => $initSchedule,
						'tuesday'   => $initSchedule,
						'wednesday' => $initSchedule,
						'thursday'  => $initSchedule,
						'friday'    => $initSchedule,
						'saturday'  => $initSchedule,
						'sunday'    => $initSchedule,
					);
		}

		return $peya_schedules;

	}


				/**
				 * Get Day Name from Weekday
				 *
				 * @param string $day
				 * @return string
				 */
	public static function getDayName( $day ) {
		switch ( $day ) {
			case 1:
				$dayName = 'monday';
				break;
			case 2:
				$dayName = 'tuesday';
				break;
			case 3:
				$dayName = 'wednesday';
				break;
			case 4:
				$dayName = 'thursday';
				break;
			case 5:
				$dayName = 'friday';
				break;
			case 6:
				$dayName = 'saturday';
				break;
			case 7:
				$dayName = 'sunday';
				break;
			default:
				$dayName = 'monday';
				break;
		}
		return $dayName;
	}

				/**
				 * Get Fleet Schedule List Options from PedidosYa
				 *
				 * @return bool
				 */
	public static function set_peya_schedule_hs_array() {
		$sdk = new PeyaSdk();
		$res = $sdk->getSchedules();

		$gmt_offset             = get_option( 'gmt_offset' );
		$timezone_offset        = 60 * ( $gmt_offset + date( 'I' ) );
		$timezone_offset_string = ( $timezone_offset >= 0 ) ? '+' . abs( $timezone_offset ) . '  minutes' : '-' . abs( $timezone_offset ) . '  minutes';

		if ( ! isset( $res['code'] ) && ! empty( $res ) ) {
			foreach ( $res as $schedule ) {
				$dayId                    = self::getDayName( $schedule['day'] );
				$peya_schedules[ $dayId ] = array(
					'from'    => date( 'H:i', strtotime( $timezone_offset_string, strtotime( $schedule['from'] ) ) ),
					'to'      => ( date( 'H:i', strtotime( $timezone_offset_string, strtotime( $schedule['to'] ) ) ) < date( 'H:i', strtotime( $timezone_offset_string, strtotime( $schedule['from'] ) ) ) ) ? '23:59' : date( 'H:i', strtotime( $timezone_offset_string, strtotime( $schedule['to'] ) ) ),
					'fromUTC' => date( 'H:i', strtotime( $schedule['from'] ) ),
					'toUTC'   => ( date( 'H:i', strtotime( $schedule['to'] ) ) < date( 'H:i', strtotime( $schedule['from'] ) ) ) ? '23:59' : date( 'H:i', strtotime( $schedule['to'] ) ),
				);

			}
			update_option( 'wc-peya-schedule-list', $peya_schedules );
			return true;
		} else {
			update_option( 'wc-peya-schedule-list', array() );
			return false;
		}
	}

				/**
				 * Set Callback WebHook for PedidosYa
				 *
				 * @return bool
				 */
	public static function set_callback() {
		$updated  = false;
		$sdk      = new PeyaSdk();
		$response = $sdk->set_callback();
		if ( ! empty( $response ) && ! isset( $response['code'] ) ) {
			if ( isset( $response['callbacks'] ) ) {
				if ( isset( $response['callbacks'][0] ) ) {
					if ( isset( $response['callbacks'][0]['url'] ) ) {
							update_option( 'wc-peya-webhook-url', $response['callbacks'][0]['url'] );
							$updated = true;
					}
				}
			}
		}
		if ( ! $updated ) {
				update_option( 'wc-peya-webhook-url', '' );
		}
		return $updated;
	}

}
