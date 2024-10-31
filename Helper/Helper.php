<?php
/**
 * Class Helper
 *
 * @package Ecomerciar\PedidosYa\Helper
 */

namespace Ecomerciar\PedidosYa\Helper;

class Helper {
	use NoticesTrait;
	use LoggerTrait;
	use SettingsTrait;
	use TokenTrait;
	use WooCommerceTrait;
	use ShippingMethodTrait;
	use DatabaseTrait;
	use TemplatesTrait;
	use PeyaTrait;
	use DebugTrait;

	/**
	 * Returns an url pointing to the main filder of the plugin assets
	 *
	 * @return string
	 */
	public static function get_assets_folder_url() {
		return plugin_dir_url( \WCPeya::MAIN_FILE ) . 'assets';
	}
}
