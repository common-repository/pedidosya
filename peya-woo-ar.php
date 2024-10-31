<?php
/**
 * Plugin Name: PedidosYa - WooCommerce
 * Description: Plugin to connect PedidosYa's Shipping services with WooCommerce
 * Version: 1.1.21
 * Requires PHP: 7.0
 * Author: Ecomerciar
 * Author URI: https://ecomerciar.com
 * Text Domain: pedidosya
 * WC requires at least: 5.4.1
 * WC tested up to: 5.6
 */

use Ecomerciar\PedidosYa\Helper\Helper;

defined( 'ABSPATH' ) || exit;

add_action( 'plugins_loaded', array( 'WCPeya', 'init' ) );
add_action( 'admin_enqueue_scripts', array( 'WCPeya', 'register_scripts' ) );
add_action( 'activated_plugin', array( 'WCPeya', 'activation' ) );
add_action( 'deactivated_plugin', array( 'WCPeya', 'deactivation' ) );

/**
 * Plugin's base Class
 */
class WCPeya {

	const VERSION     = '1.1.21';
	const PLUGIN_NAME = 'PedidosYa';
	const MAIN_FILE   = __FILE__;
	const MAIN_DIR    = __DIR__;

	/**
	 * Checks system requirements
	 *
	 * @return bool
	 */
	public static function check_system() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$system = self::check_components();

		if ( $system['flag'] ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p>' . sprintf( esc_html__( '<strong>%1$s/strong> Requiere al menos %2$s versión %3$s o superior.', 'pedidosya' ), esc_html( self::PLUGIN_NAME ), esc_html( $system['flag'] ), esc_html( $system['version'] ) ) . '</p>';
			echo '</div>';
			return false;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p>' . sprintf( esc_html__( 'WooCommerce debe estar activo antes de usar <strong>%s</strong>', 'pedidosya' ), esc_html( self::PLUGIN_NAME ) ) . '</p>';
			echo '</div>';
			return false;
		}
		return true;
	}

	/**
	 * Check the components required for the plugin to work (PHP, WordPress and WooCommerce)
	 *
	 * @return array
	 */
	private static function check_components() {
		global $wp_version;
		$flag    = false;
		$version = false;

		if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
			$flag    = 'PHP';
			$version = '7.0';
		} elseif ( version_compare( $wp_version, '5.4', '<' ) ) {
			$flag    = 'WordPress';
			$version = '5.4';
		} elseif ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, '4.3', '<' ) ) {
			$flag    = 'WooCommerce';
			$version = '4.3';
		}

		return array(
			'flag'    => $flag,
			'version' => $version,
		);
	}

	/**
	 * Print Notices
	 *
	 * @return void
	 */
	public static function print_notices() {

		$settings = Helper::get_setup_from_settings();
		if ( empty( $settings['client-id'] ) ) {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="notice notice-info is-dismissible">';
					echo '<p>' . esc_html__( 'Todavía no configuró sus envíos por PedidosYa. Hágalo rápidamente haciendo click ', 'pedidosya' ) . '<strong><a href="' . esc_url( get_admin_url( null, 'admin.php?page=wc-peya-onboarding' ) ) . '">' . esc_html__( 'AQUÍ!', 'pedidosya' ) . '</a></strong></p>';
					echo '</div>';
				}
			);
		}
		if ( 'production' !== $settings['environment'] ) {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="notice notice-warning is-dismissible">';
					echo '<p>' . esc_html__( 'Recordá que tu cuenta de PedidosYa está en modo Testing. Podés cambiarlo a modo Producción desde ', 'pedidosya' ) . '<strong><a href="' . esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=shipping&section=peya_shipping_options' ) ) . '">' . esc_html__( 'AQUÍ!', 'pedidosya' ) . '</a></strong></p>';
					echo '</div>';
				}
			);
		}

	}

	/**
	 * Inits our plugin
	 *
	 * @return void
	 */
	public static function init() {
		if ( ! self::check_system() ) {
			return false;
		}

		spl_autoload_register(
			function ( $class ) {
				if ( strpos( $class, 'PedidosYa' ) === false ) {
					return;
				}

				$name = str_replace( '\\', '/', $class );
				$name = str_replace( 'Ecomerciar/PedidosYa/', '', $name );
				if ( 'WCPeya' === $name ) {
					return;
				}
				require_once plugin_dir_path( __FILE__ ) . $name . '.php';
			}
		);
		include_once __DIR__ . '/Hooks.php';
		Helper::init();
		self::load_textdomain();

		self::print_notices();

	}

	/**
	 * Create a link to the settings page, in the plugins page
	 *
	 * @param array $links
	 * @return array
	 */
	public static function create_settings_link( array $links ) {
		$link = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=shipping&section=peya_shipping_options' ) ) . '">' . __( 'Ajustes', 'pedidosya' ) . '</a>';
		array_unshift( $links, $link );

		$link = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=wc-peya-onboarding' ) ) . '">' . __( 'Onboarding', 'pedidosya' ) . '</a>';
		array_unshift( $links, $link );

		return $links;
	}

	/**
	 * Adds our shipping method to WooCommerce
	 *
	 * @param array $shipping_methods
	 * @return array
	 */
	public static function add_shipping_method( $shipping_methods ) {
		$shipping_methods['peya'] = '\Ecomerciar\PedidosYa\ShippingMethod\WC_Peya';
		return $shipping_methods;
	}

	/**
	 * Loads the plugin text domain
	 *
	 * @return void
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'pedidosya', false, basename( dirname( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * Registers all scripts to be loaded laters
	 *
	 * @return void
	 */
	public static function register_scripts() {
		 global $pagenow;

		// Order Ajax Handlers.
		wp_enqueue_script( 'wc-peya-orders-js', Helper::get_assets_folder_url() . '/js/orders.min.js', array( 'jquery' ), '1.1.20', true );

		// Onboardings.
		if ( isset( $_GET['page'] ) ) {
			if ( ( 'options-general.php' === $pagenow && 'wc-peya-onboarding' === $_GET['page'] )
				|| ( 'admin.php' === $pagenow && 'wc-peya-onboarding' === $_GET['page'] ) ) {
				wp_enqueue_script( 'wc-peya-bootstrap-js', Helper::get_assets_folder_url() . '/js/bootstrap.min.js', array( 'jquery' ), '5.2.1', true );
				wp_register_style( 'wc-peya-bootstrap-css', Helper::get_assets_folder_url() . '/css/bootstrap.min.css', '5.2.1' );
				wp_register_style( 'wc-peya-bootstrap-grid-css', Helper::get_assets_folder_url() . '/css/bootstrap-grid.min.css', array( 'wc-peya-bootstrap-css' ), '5.2.1' );
				wp_register_style( 'wc-peya-onboarding-css', Helper::get_assets_folder_url() . '/css/onboarding.css', array( 'wc-peya-bootstrap-grid-css' ), '1.1.20' );
			}
		}
	}

	/**
	 * Register Ajax Settings
	 *
	 * @return void
	 */
	public static function ajax_settings_js() {
		?>
		<script type="text/javascript">
		var wc_peya_settings = {
			ajax_url : "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>", // /wp-admin/admin-ajax.php',
			ajax_nonce : "<?php echo esc_html( wp_create_nonce( 'pedidosya' ) ); ?>",
			spinner_id : 'peya-metabox-container-spinner',
			spinner_url : '<?php echo esc_url( Helper::get_assets_folder_url() ); ?>/img/Spin-1s-16px.gif',
			error_id: 'peya-ajax-error',
			error_notification : '<div class="notice notice-error is-dismissible" id="peya-ajax-error"><p> <?php echo esc_html__( ' PedidosYa No pudo completar la acción solicitada sobre el pedido <strong>#%1</strong>. Revise el detalle en las notas del pedido.', 'pedidosya' ); ?> </p></div>',
			cancel_reason_text: '<?php echo esc_html__( 'Por favor ingrese el motivo por el cual quiere cancelar el envío: ', 'pedidosya' ); ?>',
			cancel_reason_default: '<?php echo esc_html__( 'Pedido cancelado por el vendedor', 'pedidosya' ); ?>',
		  }
		</script>
		<?php
	}

	/**
	 * Activation Plugin Actions
	 *
	 * @return void
	 */
	public static function activation( $plugin ) {
		self::start_schedule_cron();
		self::redirect_to_onboarding_on_activation( $plugin );
	}

	/**
	 * DeActivation Plugin Actions
	 *
	 * @return void
	 */
	public static function deactivation( $plugin ) {
		self::stop_schedule_cron();
	}

	/**
	 * Redirects to onboarding page on register_activation_hook
	 */
	public static function redirect_to_onboarding_on_activation( $plugin ) {
		if ( $plugin === plugin_basename( self::MAIN_FILE ) ) {
			exit( wp_safe_redirect( admin_url( 'admin.php?page=wc-peya-onboarding' ) ) );
		}

	}

	/**
	 * Add WooCommerce Template
	 */
	public static function add_wc_locate_template( $template, $template_name, $template_path ) {
		global $woocommerce;
		$_template = $template;
		if ( ! $template_path ) {
			$template_path = $woocommerce->template_url;
		}

		$plugin_path = untrailingslashit( plugin_dir_path( self::MAIN_FILE ) ) . '/templates/woocommerce/';

		// Look within passed path within the theme - this is priority
		$template = locate_template(
			array(
				$template_path . $template_name,
				$template_name,
			)
		);

		if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
			$template = $plugin_path . $template_name;
		}

		if ( ! $template ) {
			$template = $_template;
		}

		return $template;
	}


	/**
	 * Start Cron Schedule
	 *
	 * @return void
	 */
	public static function start_schedule_cron() {
		if ( ! wp_next_scheduled( 'wc_peya_cron_update_order' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'wc_peya_schedule', 'wc_peya_cron_update_order' );
		}

		if ( ! wp_next_scheduled( 'wc_peya_cron_update_settings' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'twicedaily', 'wc_peya_cron_update_settings' );
		}
	}

	/**
	 * Stop Cron Schedule
	 *
	 * @return void
	 */
	public static function stop_schedule_cron() {
		if ( wp_next_scheduled( 'wc_peya_cron_update_order' ) ) {
			wp_clear_scheduled_hook( 'wc_peya_cron_update_order' );
		}
		if ( wp_next_scheduled( 'wc_peya_cron_update_settings' ) ) {
			wp_clear_scheduled_hook( 'wc_peya_cron_update_settings' );
		}
	}

}

?>
