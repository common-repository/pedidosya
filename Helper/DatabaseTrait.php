<?php
/**
 * Trait Database
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

trait DatabaseTrait {

	/**
	 * Find an order id by itemmeta value
	 *
	 * @param string $meta_value
	 * @return int|false
	 */
	public static function find_order_by_itemmeta_value( string $meta_value ) {
		global $wpdb;

		$order_items_table    = $wpdb->prefix . 'woocommerce_order_items';
		$order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';
		$query                = "SELECT items.order_id
        FROM {$order_items_table} as items
        INNER JOIN {$order_itemmeta_table} as itemmeta ON items.order_item_id = itemmeta.order_item_id
        WHERE itemmeta.meta_value = '%s';";
		$row                  = $wpdb->get_row( $wpdb->prepare( $query, $meta_value ), ARRAY_A );
		if ( ! empty( $row ) ) {
			return (int) $row['order_id'];
		}
		return $row;
	}

	/**
	 * Find orders by scheduled datetime
	 *
	 * @param string $meta_time
	 * @return array|string
	 */
	public static function find_order_scheduled( string $meta_time ) {
		global $wpdb;

		$options = $wpdb->prefix . 'options';
		$query   = "SELECT option_value
      FROM {$options}
      WHERE option_name like 'wc-peya-order-schedule-%'
      AND option_name <='wc-peya-order-schedule-$meta_time';";
		return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Deletes Schedules for an Order Id
	 *
	 * @param string $order_id
	 */
	public static function delete_schedules_for_order( string $order_id ) {
		global $wpdb;

		$options = $wpdb->prefix . 'options';
		$query   = "SELECT option_name
      FROM {$options}
      WHERE option_name like 'wc-peya-order-schedule-%'
      AND option_value <='$order_id';";
		$results = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $results as $result ) {
			delete_option( $result['option_name'] );
		}

	}

	/**
	 * Check Shipping Z>one Overlapping
	 *
	 * @return bool
	 */
	public static function check_shipping_zone_overlapping() {
		global $wpdb;

		$shipping_zones = $wpdb->prefix . 'woocommerce_shipping_zone_locations';
		$query          = "SELECT max(substr(location_code,1,2))
      FROM {$shipping_zones}
      group by substr(location_code,1,2)
      having count(distinct location_type) > 1;";
		$results        = $wpdb->get_results( $query, ARRAY_A );

		if ( empty( $results ) ) {
			return true;
		}
		return false;
	}
}
