<?php
/**
 * Trait Shipping Method
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

trait ShippingMethodTrait {
	private static $UTC_FORMAT_PREFIX = 'Y-m-d\TH:i:s';
	private static $GTM_FORMAT        = 'Y-m-d H:i:s';
	private static $ADD_MINUTES       = ' minutes';
	private static $DATEPART_FORMAT   = 'Y-m-d';
	private static $TIMEPART_FORMAT   = 'H:i:s';
	private static $HI_FORMAT         = 'H:i';
	private static $GTM_OFFSET_OPTION = 'gmt_offset';
	/**
	 * Gets the shipping Service Values from Enviamelo Setting
	 *
	 * @param WC_Order $order
	 * @return array|false
	 */
	public static function get_shipping_settings_from_order( \WC_Order $order ) {

		$shipping_methods = $order->get_shipping_methods();
		if ( empty( $shipping_methods ) ) {
			return '';
		}
		$shipping_method = array_shift( $shipping_methods );
		return self::get_shipping_settings( $shipping_method['method_id'], $shipping_method['instance_id'] );
	}


	/**
	 * Gets the shipping Service Values from Enviamelo Setting
	 *
	 * @param WC_Order $order
	 * @return array|false
	 */
	public static function get_shipping_settings( $shipping_method_id, $shipping_method_instance ) {

		$option = get_option( 'woocommerce_' . $shipping_method_id . '_' . $shipping_method_instance . '_settings' );

		return array(
			'pickup-name'                       => isset( $option['wc-peya-pickup-name'] ) ? $option['wc-peya-pickup-name'] : '',
			'pickup-address'                    => isset( $option['wc-peya-pickup-address'] ) ? $option['wc-peya-pickup-address'] : '',
			'pickup-address2'                   => isset( $option['wc-peya-pickup-address2'] ) ? $option['wc-peya-pickup-address2'] : '',
			'pickup-city'                       => isset( $option['wc-peya-pickup-city'] ) ? $option['wc-peya-pickup-city'] : '',
			'pickup-phone'                      => isset( $option['wc-peya-pickup-phone'] ) ? $option['wc-peya-pickup-phone'] : '',
			'pickup-country-state'              => isset( $option['wc-peya-pickup-country-state'] ) ? $option['wc-peya-pickup-country-state'] : '',
			'forwarding-agent-name'             => isset( $option['wc-peya-forwarding-agent-name'] ) ? $option['wc-peya-forwarding-agent-name'] : '',
			'process-order-status'              => isset( $option['wc-peya-process-order-status'] ) ? $option['wc-peya-process-order-status'] : '',
			'packaging-delay'                   => isset( $option['wc-peya-packaging-delay'] ) ? $option['wc-peya-packaging-delay'] : '',
			'express'                           => isset( $option['wc-peya-express'] ) ? $option['wc-peya-express'] : '',
			'pickup-schedule-monday-checked'    => isset( $option['wc-peya-pickup-schedule-monday-checked'] ) ? $option['wc-peya-pickup-schedule-monday-checked'] : '',
			'pickup-schedule-tuesday-checked'   => isset( $option['wc-peya-pickup-schedule-tuesday-checked'] ) ? $option['wc-peya-pickup-schedule-tuesday-checked'] : '',
			'pickup-schedule-wednesday-checked' => isset( $option['wc-peya-pickup-schedule-wednesday-checked'] ) ? $option['wc-peya-pickup-schedule-wednesday-checked'] : '',
			'pickup-schedule-thursday-checked'  => isset( $option['wc-peya-pickup-schedule-thursday-checked'] ) ? $option['wc-peya-pickup-schedule-thursday-checked'] : '',
			'pickup-schedule-friday-checked'    => isset( $option['wc-peya-pickup-schedule-friday-checked'] ) ? $option['wc-peya-pickup-schedule-friday-checked'] : '',
			'pickup-schedule-saturday-checked'  => isset( $option['wc-peya-pickup-schedule-saturday-checked'] ) ? $option['wc-peya-pickup-schedule-saturday-checked'] : '',
			'pickup-schedule-sunday-checked'    => isset( $option['wc-peya-pickup-schedule-sunday-checked'] ) ? $option['wc-peya-pickup-schedule-sunday-checked'] : '',
			'pickup-schedule-monday-from'       => isset( $option['wc-peya-pickup-schedule-monday-from'] ) ? $option['wc-peya-pickup-schedule-monday-from'] : '',
			'pickup-schedule-tuesday-from'      => isset( $option['wc-peya-pickup-schedule-tuesday-from'] ) ? $option['wc-peya-pickup-schedule-tuesday-from'] : '',
			'pickup-schedule-wednesday-from'    => isset( $option['wc-peya-pickup-schedule-wednesday-from'] ) ? $option['wc-peya-pickup-schedule-wednesday-from'] : '',
			'pickup-schedule-thursday-from'     => isset( $option['wc-peya-pickup-schedule-thursday-from'] ) ? $option['wc-peya-pickup-schedule-thursday-from'] : '',
			'pickup-schedule-friday-from'       => isset( $option['wc-peya-pickup-schedule-friday-from'] ) ? $option['wc-peya-pickup-schedule-friday-from'] : '',
			'pickup-schedule-saturday-from'     => isset( $option['wc-peya-pickup-schedule-saturday-from'] ) ? $option['wc-peya-pickup-schedule-saturday-from'] : '',
			'pickup-schedule-sunday-from'       => isset( $option['wc-peya-pickup-schedule-sunday-from'] ) ? $option['wc-peya-pickup-schedule-sunday-from'] : '',
			'pickup-schedule-monday-to'         => isset( $option['wc-peya-pickup-schedule-monday-to'] ) ? $option['wc-peya-pickup-schedule-monday-to'] : '',
			'pickup-schedule-tuesday-to'        => isset( $option['wc-peya-pickup-schedule-tuesday-to'] ) ? $option['wc-peya-pickup-schedule-tuesday-to'] : '',
			'pickup-schedule-wednesday-to'      => isset( $option['wc-peya-pickup-schedule-wednesday-to'] ) ? $option['wc-peya-pickup-schedule-wednesday-to'] : '',
			'pickup-schedule-thursday-to'       => isset( $option['wc-peya-pickup-schedule-thursday-to'] ) ? $option['wc-peya-pickup-schedule-thursday-to'] : '',
			'pickup-schedule-friday-to'         => isset( $option['wc-peya-pickup-schedule-friday-to'] ) ? $option['wc-peya-pickup-schedule-friday-to'] : '',
			'pickup-schedule-saturday-to'       => isset( $option['wc-peya-pickup-schedule-saturday-to'] ) ? $option['wc-peya-pickup-schedule-saturday-to'] : '',
			'pickup-schedule-sunday-to'         => isset( $option['wc-peya-pickup-schedule-sunday-to'] ) ? $option['wc-peya-pickup-schedule-sunday-to'] : '',
			'pickup-latitude'                   => isset( $option['wc-peya-pickup-latitude'] ) ? $option['wc-peya-pickup-latitude'] : '',
			'pickup-longitude'                  => isset( $option['wc-peya-pickup-longitude'] ) ? $option['wc-peya-pickup-longitude'] : '',
			'free-delivery'                     => isset( $option['wc-peya-free-delivery'] ) ? $option['wc-peya-free-delivery'] : 'no',
			'free-delivery-from'                => isset( $option['wc-peya-free-delivery-from'] ) ? $option['wc-peya-free-delivery-from'] : 0,
			'delivery-fee-type'                 => isset( $option['wc-peya-delivery-fee-type'] ) ? $option['wc-peya-delivery-fee-type'] : 'DEFAULT',
			'fixed-delivery-fee-value'          => isset( $option['wc-peya-fixed-delivery-fee-value'] ) ? $option['wc-peya-fixed-delivery-fee-value'] : 0,
		);

	}

	/**
	 * Check if Shipping Method has PedidosYa-Express ON
	 *
	 * @param int $shipping_method_id
	 * @param int $shipping_method_instance
	 * @return string
	 */
	public static function is_express_available( $shipping_method_id, $shipping_method_instance ) {
		$settings = Helper::get_shipping_settings( $shipping_method_id, $shipping_method_instance );
		return $settings['express'];
	}

	/**
	 * Get & Check if Shipping Method has PedidosYa-Express ON (and available) right Now
	 *
	 * @param int $shipping_method_id
	 * @param int $shipping_method_instance
	 * @return bool|string
	 */
	public static function get_today_express_available( $shipping_method_id, $shipping_method_instance ) {
		$settings  = Helper::get_shipping_settings( $shipping_method_id, $shipping_method_instance );
		$startDate = get_date_from_gmt( 'now', self::$GTM_FORMAT );

		$weekday     = ( 0 === date( 'w', strtotime( $startDate ) ) ) ? 7 : date( 'w', strtotime( $startDate ) );
		$weekdayname = Helper::getDayName( $weekday );

		// Handicap minutes from now.
		$buffer_offset            = $settings['packaging-delay'];
		$buffer_offset            = ( $buffer_offset < 10 ) ? 10 : $buffer_offset;
		$correction_offset        = $buffer_offset;
		$correction_offset_string = ( $correction_offset >= 0 ) ? '+' . $correction_offset . self::$ADD_MINUTES : '-' . $correction_offset . self::$ADD_MINUTES;

		$deliveryDatePart = date( self::$DATEPART_FORMAT, strtotime( $correction_offset_string, strtotime( $startDate ) ) );
		$deliveryTimePart = date( self::$HI_FORMAT, strtotime( $correction_offset_string, strtotime( $startDate ) ) );
		$todayDatePart    = date( self::$DATEPART_FORMAT, strtotime( $startDate ) );

		// Get Delivery Time in UTC.
		$gmt_offset               = get_option( self::$GTM_OFFSET_OPTION );
		$timezone_offset          = 60 * ( $gmt_offset + date( 'I' ) ) * -1;
		$correction_offset        = $timezone_offset + $buffer_offset;
		$correction_offset_string = ( $correction_offset >= 0 ) ? '+' . $correction_offset . self::$ADD_MINUTES : '-' . $correction_offset . self::$ADD_MINUTES;
		$deliveryDatePartUTC      = date( self::$DATEPART_FORMAT, strtotime( $correction_offset_string, strtotime( $startDate ) ) );
		$deliveryTimePartUTC      = date( self::$TIMEPART_FORMAT, strtotime( $correction_offset_string, strtotime( $startDate ) ) );

		// Check PedidosYa Schedule
		$peya_schedule = self::get_peya_schedule_hs_array();
		if ( isset( $peya_schedule[ self::getDayName( $weekday ) ] ) ) {
			$checkClause1 = ( ! ( $deliveryTimePart >= $peya_schedule[ self::getDayName( $weekday ) ]['from'] && $deliveryTimePart <= $peya_schedule[ self::getDayName( $weekday ) ]['to'] ) );
		} else {
			$checkClause1 = false;
		}

		// Check Is Peya Express Active.
		$checkPeyaExpressActive = ( 'yes' === self::is_express_available( $shipping_method_id, $shipping_method_instance ) );

		// Check Picking Settings.
		$checkClause2 = ( $checkPeyaExpressActive && ( ! ( $deliveryDatePart === $todayDatePart && 'yes' === $settings[ 'pickup-schedule-' . $weekdayname . '-checked' ] ) ) );

		// Check Picking hours.
		$checkClause3 = ( $checkPeyaExpressActive && ( ! ( $deliveryTimePart <= $settings[ 'pickup-schedule-' . $weekdayname . '-to' ] && $deliveryTimePart >= $settings[ 'pickup-schedule-' . $weekdayname . '-from' ] ) ) );

		if ( $checkClause1 || $checkClause2 || $checkClause3 ) {
			return false;
		}
		return $deliveryDatePartUTC . 'T' . $deliveryTimePartUTC . 'Z';
	}

	/**
	 * Get & Check if Shipping Method has PedidosYa-Express ON (and available) for Today (not Now)
	 *
	 * @param int $shipping_method_id
	 * @param int $shipping_method_instance
	 * @return bool|string
	 */
	public static function get_today_next_express_available( $shipping_method_id, $shipping_method_instance ) {
		$settings  = Helper::get_shipping_settings( $shipping_method_id, $shipping_method_instance );
		$startDate = get_date_from_gmt( 'now', self::$GTM_FORMAT );

		$weekday     = ( 0 === date( 'w', strtotime( $startDate ) ) ) ? 7 : date( 'w', strtotime( $startDate ) );
		$weekdayname = Helper::getDayName( $weekday );

		// Check Picking Settings.
		if ( 'yes' !== $settings[ 'pickup-schedule-' . $weekdayname . '-checked' ] ) {
			$return = false;
		} else {
			// Handicap minutes from now.
			$buffer_offset = $settings['packaging-delay'];
			$buffer_offset = ( $buffer_offset < 10 ) ? 10 : $buffer_offset;

			$deliveryDatePart = date( self::$DATEPART_FORMAT, strtotime( $startDate ) );
			$deliveryTimePart = $settings[ 'pickup-schedule-' . $weekdayname . '-from' ];
			$todayDatePart    = date( self::$DATEPART_FORMAT, strtotime( $startDate ) );
			$todayTimePart    = date( self::$HI_FORMAT, strtotime( $startDate ) );

			if ( $deliveryTimePart < $todayTimePart ) {
				$return = false;
			} else {
				// Get Delivery Time in UTC.
				$gmt_offset               = get_option( self::$GTM_OFFSET_OPTION );
				$timezone_offset          = 60 * ( $gmt_offset + date( 'I' ) ) * -1;
				$correction_offset        = $timezone_offset + $buffer_offset;
				$correction_offset_string = ( $correction_offset >= 0 ) ? '+' . $correction_offset . self::$ADD_MINUTES : '-' . $correction_offset . self::$ADD_MINUTES;
				$deliveryDatePartUTC      = date( self::$DATEPART_FORMAT, strtotime( $correction_offset_string, strtotime( $deliveryDatePart . ' ' . $deliveryTimePart ) ) );
				$deliveryTimePartUTC      = date( self::$TIMEPART_FORMAT, strtotime( $correction_offset_string, strtotime( $deliveryDatePart . ' ' . $deliveryTimePart ) ) );

				// Check PedidosYa Schedule.
				$peya_schedule = self::get_peya_schedule_hs_array();
				if ( isset( $peya_schedule[ self::getDayName( $weekday ) ] ) ) {
					$checkClause1 = ( ! ( $deliveryTimePart >= $peya_schedule[ self::getDayName( $weekday ) ]['from'] && $deliveryTimePart <= $peya_schedule[ self::getDayName( $weekday ) ]['to'] ) );
				} else {
					$checkClause1 = false;
				}

				// Check Is Peya Express Active.
				$checkPeyaExpressActive = ( 'yes' === self::is_express_available( $shipping_method_id, $shipping_method_instance ) );

				// Check Picking Settings.
				$checkClause2 = ( $checkPeyaExpressActive && ( ! ( $deliveryDatePart === $todayDatePart && 'yes' === $settings[ 'pickup-schedule-' . $weekdayname . '-checked' ] ) ) );

				// Check Picking hours.
				$checkClause3 = ( $checkPeyaExpressActive && ( ! ( $deliveryTimePart <= $settings[ 'pickup-schedule-' . $weekdayname . '-to' ] && $deliveryTimePart >= $settings[ 'pickup-schedule-' . $weekdayname . '-from' ] ) ) );

				if ( $checkClause1 || $checkClause2 || $checkClause3 ) {
					$return = false;
				} else {
					return $deliveryDatePartUTC . 'T' . $deliveryTimePartUTC . 'Z';
				}
			}
		}
		return $return;
	}

	/**
	 * Get & Check if Shipping Method has PedidosYa-Express ON (and available) for the day before
	 *
	 * @param int $shipping_method_id
	 * @param int $shipping_method_instance
	 * @return bool|string
	 */
	public static function get_tomorrow_express_available( $shipping_method_id, $shipping_method_instance ) {
		$settings  = Helper::get_shipping_settings( $shipping_method_id, $shipping_method_instance );
		$startDate = get_date_from_gmt( 'now', self::$GTM_FORMAT );

		$scheduleDate = date( self::$DATEPART_FORMAT, strtotime( $startDate ) );
		$exit         = false;
		$count        = 0;
		while ( ! ( $exit ) ) {
			$count        = $count + 1;
			$scheduleDate = date( self::$DATEPART_FORMAT, strtotime( '+1 day', strtotime( $scheduleDate ) ) );
			$weekday      = ( 0 === date( 'w', strtotime( $scheduleDate ) ) ) ? 7 : date( 'w', strtotime( $scheduleDate ) );
			$weekdayname  = Helper::getDayName( $weekday );
			if ( 'yes' === $settings[ 'pickup-schedule-' . $weekdayname . '-checked' ] ) {
				self::log( 'true' );
				$exit = true;
			}
			if ( $count > 8 ) {
				break;
			}
		}

		// Handicap minutes from now.
		$buffer_offset = $settings['packaging-delay'];
		$buffer_offset = ( $buffer_offset < 10 ) ? 10 : $buffer_offset;

		$deliveryDate     = date( self::$DATEPART_FORMAT, strtotime( $scheduleDate ) );
		$deliveryTime     = ( $settings[ 'pickup-schedule-' . $weekdayname . '-from' ] ) ? $settings[ 'pickup-schedule-' . $weekdayname . '-from' ] : '00:00' . ':00';
		$deliveryDateTime = date( self::$GTM_FORMAT, strtotime( $deliveryDate . ' ' . $deliveryTime ) );

		// Get Delivery Time in UTC.
		$gmt_offset               = get_option( self::$GTM_OFFSET_OPTION );
		$timezone_offset          = 60 * ( $gmt_offset + date( 'I' ) ) * -1;
		$correction_offset        = $timezone_offset + $buffer_offset;
		$correction_offset_string = ( $correction_offset >= 0 ) ? '+' . $correction_offset . self::$ADD_MINUTES : '-' . $correction_offset . self::$ADD_MINUTES;
		$deliveryDatePartUTC      = date( self::$DATEPART_FORMAT, strtotime( $correction_offset_string, strtotime( $deliveryDateTime ) ) );
		$deliveryTimePartUTC      = date( self::$TIMEPART_FORMAT, strtotime( $correction_offset_string, strtotime( $deliveryDateTime ) ) );

		return date( self::$UTC_FORMAT_PREFIX, strtotime( $deliveryDatePartUTC . ' ' . $deliveryTimePartUTC ) ) . 'Z';
	}

	/**
	 * Get Now datetime in UTC
	 *
	 * @return string
	 */
	public static function get_now_delivery_time_UTC() {

		$gmt_offset      = get_option( self::$GTM_OFFSET_OPTION );
		$timezone_offset = 60 * ( $gmt_offset + date( 'I' ) ) * -1;

		// Handicap minutes from now.
		$buffer_offset            = 10;
		$correction_offset        = $timezone_offset + $buffer_offset;
		$correction_offset_string = ( $correction_offset >= 0 ) ? '+' . abs( $correction_offset ) . self::$ADD_MINUTES : '-' . abs( $correction_offset ) . self::$ADD_MINUTES;
		$startDate                = get_date_from_gmt( 'now', self::$GTM_FORMAT );

		return date( self::$UTC_FORMAT_PREFIX, strtotime( $correction_offset_string, strtotime( $startDate ) ) ) . 'Z';
	}

	public static function convert_from_UTC_2_gtm( $string ) {
		$gmt_offset      = get_option( self::$GTM_OFFSET_OPTION );
		$timezone_offset = 60 * ( $gmt_offset + date( 'I' ) ) * -1;

		// Handicap minutes from now.
		$correction_offset        = $timezone_offset;
		$correction_offset_string = ( $correction_offset >= 0 ) ? '-' . abs( $correction_offset ) . self::$ADD_MINUTES : '+' . abs( $correction_offset ) . self::$ADD_MINUTES;

		return date( self::$GTM_FORMAT, strtotime( $correction_offset_string, strtotime( $string ) ) );
	}

}
