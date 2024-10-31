<?php
/**
 * Trait Templates
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

trait TemplatesTrait {

	/**
	 * Get Template Part
	 *
	 * @param string $slug
	 * @param string $name
	 */
	public static function get_template_part( $slug, $name = null ) {
		do_action( "peya_get_template_part_{$slug}", $slug, $name );
		$templates = array();
		if ( isset( $name ) ) {
			$templates[] = "{$slug}-{$name}.php";
		}

		$templates[] = "{$slug}.php";

		self::get_template_path( $templates, true, false );
	}

	/**
	 * Get Template Path & Load
	 *
	 * @param string $template_names
	 * @param bool   $load
	 * @param bool   $require_once
	 */
	public static function get_template_path( $template_names, $load = false, $require_once = true ) {
		$located = '';
		foreach ( (array) $template_names as $template_name ) {
			if ( ! $template_name ) {
				continue;
			}
			// search file within the PLUGIN_DIR_PATH only.
			if ( file_exists( \WCPeya::MAIN_DIR . '/templates/' . $template_name ) ) {
				$located = \WCPeya::MAIN_DIR . '/templates/' . $template_name;
				break;
			}
		}

		if ( $load && '' !== $located ) {
			load_template( $located, $require_once );
		}
		return $located;
	}
}
