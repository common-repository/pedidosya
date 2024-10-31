<?php
/**
 * Class Main Onboarding
 *
 * @package Ecomerciar\PedidosYa\Onboarding
 */

namespace Ecomerciar\PedidosYa\Onboarding;

use Ecomerciar\PedidosYa\Helper\Helper;

defined( 'ABSPATH' ) || exit;

class Main {

	/**
	 * Register Onboarding Page
	 */
	public static function register_onboarding_page() {
		add_options_page( 'Onboarding - PedidosYa', 'Onboarding - PedidosYa', 'manage_options', 'wc-peya-onboarding', array( '\Ecomerciar\PedidosYa\Onboarding\Main', 'content' ) );
	}

	/**
	 * Get content
	 */
	public static function content() {
		helper::get_template_part( 'page', 'onboarding' );
	}

	/**
	 * Register assets files
	 */
	public static function add_assets_files() {
		wp_enqueue_style( 'wc-peya-onboarding-css' );
	}

}
