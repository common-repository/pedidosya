<?php
/**
 * Trait Woocommerce
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

trait WooCommerceTrait {

	/**
	 * Gets the customer from a WooCommerce Cart
	 *
	 * @param WC_Customer $customer
	 * @return array|false
	 */
	public static function get_customer_from_cart( $customer ) {
		if ( ! $customer ) {
			return false;}
		$name         = self::get_customer_name( $customer );
		$first_name   = self::get_customer_first_name( $customer );
		$last_name    = self::get_customer_last_name( $customer );
		$address      = self::get_address( $customer );
		$postal_code  = self::get_postal_code( $customer );
		$province     = self::get_province( $customer );
		$locality     = self::get_locality( $customer );
		$full_address = self::get_full_address( $address, $locality, $postal_code, $province );
		$latlng       = self::get_latlong( $customer );
		return array(
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'full_name'    => $name,
			'street'       => $address['street'],
			'number'       => $address['number'],
			'floor'        => $address['floor'],
			'apartment'    => $address['apartment'],
			'full_address' => $full_address,
			'cp'           => $postal_code,
			'locality'     => $locality,
			'province'     => $province,
			'lat'          => $latlng['lat'],
			'lng'          => $latlng['lng'],
			'address_1'    => $address['address1'],
			'address_2'    => $address['address2'],
		);
	}

	/**
	 * Gets full address
	 *
	 * @param array  $address
	 * @param string $locality
	 * @param string $postal_code
	 * @param string $province
	 * @return string
	 */
	public static function get_full_address( array $address, string $locality, string $postal_code, string $province ) {
		$full_address = $address['street'];
		if ( ! empty( $address['number'] ) ) {
			$full_address .= ' ' . $address['number'];
		}
		if ( ! empty( $address['floor'] ) ) {
			$full_address .= ', ';
			$full_address .= $address['floor'];
			if ( ! empty( $address['apartment'] ) ) {
				$full_address .= ' ' . $address['apartment'];
			}
		}

		return $full_address . '. ' . $locality . ' ' . $postal_code . ', ' . $province;
	}

	/**
	 * Sanitize Phone number
	 *
	 * @param string $value Phone number
	 * @return string
	 */
	public static function sanitize_phone( $value ) {
		$value = preg_replace( '/[^0-9]/', '', $value );
		$value = substr( $value, 0, 30 );
		$value = ltrim( $value, '0' );
		$value = substr( $value, 0, 15 );
		return $value;
	}

	/**
	 * Gets customer data from an order
	 *
	 * @param WC_Order $order
	 * @return array|false
	 */
	public static function get_customer_from_order( $order ) {
		if ( ! $order ) {
			return false;}
		$data               = self::get_customer_from_cart( $order );
		$data['email']      = $order->get_billing_email();
		$data['phone']      = self::sanitize_phone( $order->get_billing_phone() );
		$data['extra_info'] = $order->get_customer_note();
		return $data;
	}

	/**
	 * Gets the province from a customer
	 *
	 * @param WC_Customer $customer
	 * @return string
	 */
	public static function get_province( $customer ) {
		$province = $customer->get_shipping_state();
		if ( ! ( $province ) ) {
			$province = $customer->get_billing_state();
		}
		return self::get_province_name( $province );
	}

	/**
	 * Gets the locality from a customer
	 *
	 * @param WC_Customer $customer
	 * @return string
	 */
	public static function get_locality( $customer ) {
		$locality = $customer->get_shipping_city();
		if ( ! ( $locality ) ) {
			$locality = $customer->get_billing_city();
		}
		return $locality;
	}

	/**
	 * Gets the postal code from a customer
	 *
	 * @param WC_Customer $customer
	 * @return string
	 */
	public static function get_postal_code( $customer ) {
		$postal_code = $customer->get_shipping_postcode();
		if ( ! ( $postal_code ) ) {
			$postal_code = $customer->get_billing_postcode();
		}
		return $postal_code;
	}

	/**
	 * Gets the full customer name
	 *
	 * @param WC_Customer $customer
	 * @return string
	 */
	public static function get_customer_name( $customer ) {
		return self::get_customer_first_name( $customer ) . ' ' . self::get_customer_last_name( $customer );
	}

	/**
	 * Gets the customer first name
	 *
	 * @param WC_Customer $customer
	 * @return string
	 */
	public static function get_customer_first_name( $customer ) {
		$name = '';
		if ( $customer->get_shipping_first_name() ) {
			$name = $customer->get_shipping_first_name();
		} else {
			$name = $customer->get_billing_first_name();
		}
		return $name;
	}

	/**
	 * Gets the customer last name
	 *
	 * @param WC_Customer $customer
	 * @return string
	 */
	public static function get_customer_last_name( $customer ) {
		$name = false;
		if ( $customer->get_shipping_last_name() ) {
			$name = $customer->get_shipping_last_name();
		} else {
			$name = $customer->get_billing_last_name();
		}
		return $name;
	}

	/**
	 * Gets the customer Latitude and Longitude
	 *
	 * @param WC_Order $order
	 * @return array
	 */
	public static function get_latlong( $order ) {
		if ( ! $order ) {
			return false;}
		if ( $order->get_shipping_address_1() ) {
			$lat = get_post_meta( $order->get_id(), '_shipping_wc_lat', true );
			$lng = get_post_meta( $order->get_id(), '_shipping_wc_lng', true );
		} else {
			$lat = get_post_meta( $order->get_id(), '_billing_wc_lat', true );
			$lng = get_post_meta( $order->get_id(), '_billing_wc_lng', true );
		}

		return array(
			'lat' => $lat,
			'lng' => $lng,
		);
	}

	/**
	 * Gets the address of an order
	 *
	 * @param WC_Order $order
	 * @return false|array
	 */
	public static function get_address( $order ) {
		if ( ! $order ) {
			return false;}

		if ( $order->get_shipping_address_1() ) {
			$shipping_line_1 = $order->get_shipping_address_1();
			$shipping_line_2 = $order->get_shipping_address_2();
		} else {
			$shipping_line_1 = $order->get_billing_address_1();
			$shipping_line_2 = $order->get_billing_address_2();
		}
		$address_1     = $shipping_line_1;
		$address_2     = $shipping_line_2;
		$street_name   = '';
		$street_number = '';
		$floor         = '';
		$apartment     = '';
		if ( ! empty( $shipping_line_2 ) ) {
			// there is something in the second line. Let's find out what.
			$fl_apt_array = self::get_floor_and_apt( $shipping_line_2 );
			$floor        = $fl_apt_array[0];
			$apartment    = $fl_apt_array[1];
		}

		// Now let's work on the first line.
		preg_match( '/(^\d*[\D]*)(\d+)(.*)/i', $shipping_line_1, $res );
		$line1 = $res;

		if ( ( isset( $line1[1] ) && ! empty( $line1[1] ) && $line1[1] !== ' ' ) && ! empty( $line1 ) ) {
			// everything's fine. Go ahead.
			if ( empty( $line1[3] ) || $line1[3] === ' ' ) {
				// the user just wrote the street name and number, as he should.
				$street_name   = trim( $line1[1] );
				$street_number = trim( $line1[2] );
				unset( $line1[3] );
			} else {
				// there is something extra in the first line. We'll save it in case it's important.
				$street_name     = trim( $line1[1] );
				$street_number   = trim( $line1[2] );
				$shipping_line_2 = trim( $line1[3] );

				if ( empty( $floor ) && empty( $apartment ) ) {
					// if we don't have either the floor or the apartment, they should be in our new $shipping_line_2.
					$fl_apt_array = self::get_floor_and_apt( $shipping_line_2 );
					$floor        = $fl_apt_array[0];
					$apartment    = $fl_apt_array[1];
				} elseif ( empty( $apartment ) ) {
					// we've already have the floor. We just need the apartment.
					$apartment = trim( $line1[3] );
				} else {
					// we've got the apartment, so let's just save the floor.
					$floor = trim( $line1[3] );
				}
			}
		} else {
			// the user didn't write the street number. Maybe it's in the second line.
			// given the fact that there is no street number in the fist line, we'll asume it's just the street name.
			$street_name = $shipping_line_1;

			if ( ! empty( $floor ) && ! empty( $apartment ) ) {
				// we are in a pickle. It's a risky move, but we'll move everything one step up.
				$street_number = $floor;
				$floor         = $apartment;
				$apartment     = '';
			} elseif ( ! empty( $floor ) && empty( $apartment ) ) {
				// it seems the user wrote only the street number in the second line. Let's move it up.
				$street_number = $floor;
				$floor         = '';
			} elseif ( empty( $floor ) && ! empty( $apartment ) ) {
				// I don't think there's a chance of this even happening, but let's write it to be safe.
				$street_number = $apartment;
				$apartment     = '';
			}
		}
		return array(
			'address1'  => $address_1,
			'address2'  => $address_2,
			'street'    => $street_name,
			'number'    => $street_number,
			'floor'     => $floor,
			'apartment' => $apartment,
		);
	}

	/**
	 * Get specific details from an address (floor and apt)
	 *
	 * @param string $fl_apt
	 * @return array
	 */
	public static function get_floor_and_apt( $fl_apt ) {
		// firts we'll asume the user did things right. Something like "piso 24, depto. 5h".
		preg_match( '/(piso|p|p.) ?(\w+),? ?(departamento|depto|dept|dpto|dpt|dpt.º|depto.|dept.|dpto.|dpt.|apartamento|apto|apt|apto.|apt.) ?(\w+)/i', $fl_apt, $res );
		$line2 = $res;

		if ( ! empty( $line2 ) ) {
			// everything was written great. Now lets grab what matters.
			$floor     = trim( $line2[2] );
			$apartment = trim( $line2[4] );
		} else {
			// maybe the user wrote something like "depto. 5, piso 24". Let's try that
			preg_match( '/(departamento|depto|dept|dpto|dpt|dpt.º|depto.|dept.|dpto.|dpt.|apartamento|apto|apt|apto.|apt.) ?(\w+),? ?(piso|p|p.) ?(\w+)/i', $fl_apt, $res );
			$line2 = $res;
		}

		if ( ! empty( $line2 ) && empty( $apartment ) && empty( $floor ) ) {
			// apparently, that was the case. Guess some people just like to make things difficult.
			$floor     = trim( $line2[4] );
			$apartment = trim( $line2[2] );
		} else {
			// something is wrong. Let's be more specific. First we'll try with only the floor.
			preg_match( '/^(piso|p|p.) ?(\w+)$/i', $fl_apt, $res );
			$line2 = $res;
		}

		if ( ! empty( $line2 ) && empty( $floor ) ) {
			// now we've got it! The user just wrote the floor number. Now lets grab what matters.
			$floor = trim( $line2[2] );
		} else {
			// still no. Now we'll try with the apartment.
			preg_match( '/^(departamento|depto|dept|dpto|dpt|dpt.º|depto.|dept.|dpto.|dpt.|apartamento|apto|apt|apto.|apt.) ?(\w+)$/i', $fl_apt, $res );
			$line2 = $res;
		}

		if ( ! empty( $line2 ) && empty( $apartment ) && empty( $floor ) ) {
			// success! The user just wrote the apartment information. No clue why, but who am I to judge.
			$apartment = trim( $line2[2] );
		} else {
			// ok, weird. Now we'll try a more generic approach just in case the user missplelled something.
			preg_match( '/(\d+),? [a-zA-Z.,!*]* ?([a-zA-Z0-9 ]+)/i', $fl_apt, $res );
			$line2 = $res;
		}

		if ( ! empty( $line2 ) && empty( $floor ) && empty( $apartment ) ) {
			// finally! The user just missplelled something. It happens to the best of us.
			$floor     = trim( $line2[1] );
			$apartment = trim( $line2[2] );
		} else {
			// last try! This one is in case the user wrote the floor and apartment together ("12C").
			preg_match( '/(\d+)(\D*)/i', $fl_apt, $res );
			$line2 = $res;
		}

		if ( ! empty( $line2 ) && empty( $floor ) && empty( $apartment ) ) {
			// ok, we've got it. I was starting to panic.
			$floor     = trim( $line2[1] );
			$apartment = trim( $line2[2] );
		} elseif ( empty( $floor ) && empty( $apartment ) ) {
			// I give up. I can't make sense of it. We'll save it in case it's something useful.
			$floor = $fl_apt;
		}

		return array(
			$floor,
			$apartment,
		);
	}

	/**
	 * Gets the province name
	 *
	 * @param string $province_id
	 * @return string
	 */
	public static function get_province_name( string $province_id = '' ) {
					/*Mexico*/
		$translateZone['DF'] = 'Ciudad de Mexico';
		$translateZone['JA'] = 'Jalisco';
		$translateZone['NL'] = 'Nuevo León';
		$translateZone['AG'] = 'Aguascalientes';
		$translateZone['BC'] = 'Baja California';
		$translateZone['BS'] = 'Baja California Sur';
		$translateZone['CM'] = 'Campeche';
		$translateZone['CS'] = 'Chiapas';
		$translateZone['CH'] = 'Chihuahua';
		$translateZone['CO'] = 'Coahuila';
		$translateZone['CL'] = 'Colima';
		$translateZone['DG'] = 'Durango';
		$translateZone['GT'] = 'Guanajuato';
		$translateZone['GR'] = 'Guerrero';
		$translateZone['HG'] = 'Hidalgo';
		$translateZone['MX'] = 'Estado de México';
		$translateZone['MI'] = 'Michoacán';
		$translateZone['MO'] = 'Morelos';
		$translateZone['NA'] = 'Nayarit';
		$translateZone['OA'] = 'Oaxaca';
		$translateZone['PU'] = 'Puebla';
		$translateZone['QR'] = 'Quintana Roo';
		$translateZone['QT'] = 'Querétaro';
		$translateZone['SL'] = 'San Luis Potosí';
		$translateZone['SI'] = 'Sinaloa';
		$translateZone['SO'] = 'Sonora';
		$translateZone['TB'] = 'Tabasco';
		$translateZone['TM'] = 'Tamaulipas';
		$translateZone['TL'] = 'Tlaxcala';
		$translateZone['VE'] = 'Veracruz';
		$translateZone['YU'] = 'Yucatán';
		$translateZone['ZA'] = 'Zacatecas';

						/*Peru*/
		$translateZone['CAL'] = 'El Callao';
		$translateZone['LMA'] = 'Municipalidad Metropolitana de Lima';
		$translateZone['AMA'] = 'Amazonas';
		$translateZone['ANC'] = 'Ancash';
		$translateZone['APU'] = 'Apurímac';
		$translateZone['ARE'] = 'Arequipa';
		$translateZone['AYA'] = 'Ayacucho';
		$translateZone['CAJ'] = 'Cajamarca';
		$translateZone['CUS'] = 'Cusco';
		$translateZone['HUV'] = 'Huancavelica';
		$translateZone['HUC'] = 'Huánuco';
		$translateZone['ICA'] = 'Ica';
		$translateZone['JUN'] = 'Junín';
		$translateZone['LAL'] = 'La Libertad';
		$translateZone['LA']  = 'Lambayeque';
		$translateZone['LIM'] = 'Lima';
		$translateZone['LOR'] = 'Loreto';
		$translateZone['MDD'] = 'Madre de Dios';
		$translateZone['MOQ'] = 'Moquegua';
		$translateZone['PAS'] = 'Pasco';
		$translateZone['PIU'] = 'Piura';
		$translateZone['PUN'] = 'Puno';
		$translateZone['SAM'] = 'San Martín';
		$translateZone['TAC'] = 'Tacna';
		$translateZone['TUM'] = 'Tumbes';
		$translateZone['UCA'] = 'Ucayali';

						/*Argentina*/
		$translateZone['C'] = 'Ciudad Autónoma de Buenos Aires';
		$translateZone['B'] = 'Buenos Aires';
		$translateZone['K'] = 'Catamarca';
		$translateZone['H'] = 'Chaco';
		$translateZone['U'] = 'Chubut';
		$translateZone['X'] = 'Córdoba';
		$translateZone['W'] = 'Corrientes';
		$translateZone['E'] = 'Entre Ríos';
		$translateZone['P'] = 'Formosa';
		$translateZone['Y'] = 'Jujuy';
		$translateZone['L'] = 'La Pampa';
		$translateZone['F'] = 'La Rioja';
		$translateZone['M'] = 'Mendoza';
		$translateZone['N'] = 'Misiones';
		$translateZone['Q'] = 'Neuquén';
		$translateZone['R'] = 'Río Negro';
		$translateZone['A'] = 'Salta';
		$translateZone['J'] = 'San Juan';
		$translateZone['D'] = 'San Luis';
		$translateZone['Z'] = 'Santa Cruz';
		$translateZone['S'] = 'Santa Fe';
		$translateZone['G'] = 'Santiago del Estero';
		$translateZone['V'] = 'Tierra del Fuego';
		$translateZone['T'] = 'Tucumán';
		if ( isset( $translateZone[ $province_id ] ) ) {
			return $translateZone[ $province_id ];
		} else {
			return $province_id;
		}
	}

	/**
	 * Gets product dimensions and details
	 *
	 * @param int $product_id
	 * @return false|array
	 */
	public static function get_product_dimensions( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return false;}

		$dimension_unit = 'cm';
		$weight_unit    = 'kg';

		$height = ( $product->get_height() ? wc_get_dimension( $product->get_height(), $dimension_unit ) : '0' );
		$width  = ( $product->get_width() ? wc_get_dimension( $product->get_width(), $dimension_unit ) : '0' );
		$length = ( $product->get_length() ? wc_get_dimension( $product->get_length(), $dimension_unit ) : '0' );

		return array(
			'height'               => ( $product->get_height() ? wc_get_dimension( $product->get_height(), $dimension_unit ) : '0' ),
			'width'                => ( $product->get_width() ? wc_get_dimension( $product->get_width(), $dimension_unit ) : '0' ),
			'length'               => ( $product->get_length() ? wc_get_dimension( $product->get_length(), $dimension_unit ) : '0' ),
			'weight'               => ( $product->has_weight() ? wc_get_weight( $product->get_weight(), $weight_unit ) : '0' ),
			'price'                => $product->get_price(),
			'description'          => $product->get_name(),
			'id'                   => $product_id,
			'sku'                  => $product->get_sku(),
			'wc-peya-product-size' => $height * $width * $length,
		);
	}

	/**
	 * Gets all items from a cart
	 *
	 * @param WC_Cart $cart
	 * @return false|array
	 */
	public static function get_items_from_cart( $cart ) {
		$products = array();
		$items    = $cart->get_cart();
		foreach ( $items as $item ) {
			$product_id  = $item['data']->get_id();
			$new_product = self::get_product_dimensions( $product_id );
			if ( ! $new_product ) {
				self::log_error( __( 'Helper -> Error obteniendo productos del carrito, producto con malas dimensiones - ID: ', 'pedidosya' ) . $product_id );
				return false;
			}
			for ( $i = 0;$i < $item['quantity'];$i++ ) {
				$products[] = $new_product;
			}
		}
		return $products;
	}

	/**
	 * Gets items from an order
	 *
	 * @param WC_Order $order
	 * @return false|array
	 */
	public static function get_items_from_order( $order ) {
		$products = array();
		$items    = $order->get_items();
		foreach ( $items as $item ) {
			$product_id = $item->get_variation_id();
			if ( ! $product_id ) {
				$product_id = $item->get_product_id();}
			$new_product = self::get_product_dimensions( $product_id );
			if ( ! $new_product ) {
				self::log_error( __( 'Helper -> Error obteniendo productos de la orden, producto con malas dimensiones - ID: ', 'pedidosya' ) . $product_id );
				return false;
			}
			for ( $i = 0;$i < $item->get_quantity();$i++ ) {
				$products[] = $new_product;
			}
		}
		return $products;
	}

	/**
	 * Groups an array of items
	 *
	 * @param array $items
	 * @return array
	 */
	public static function group_items( array $items ) {
		$grouped_items = array();
		foreach ( $items as $item ) {
			if ( isset( $grouped_items[ $item['id'] ] ) ) {
				$grouped_items[ $item['id'] ]['quantity']++;
			} else {
				$grouped_items[ $item['id'] ]             = $item;
				$grouped_items[ $item['id'] ]['quantity'] = 1;
			}
		}
		return $grouped_items;
	}
}
