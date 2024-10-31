<?php
/**
 * Main Onboarding Template - Settings Page
 *
 * @package Ecomerciar\PedidosYa\Templates
 */

 use Ecomerciar\PedidosYa\Settings\Section;
 use Ecomerciar\PedidosYa\Sdk\PeyaSdk;
 use Ecomerciar\PedidosYa\Helper\Helper;
 use Ecomerciar\PedidosYa\Api\GoogleApi;
?>

<?php
  $error_descr = '';
  $error_flg   = false;
?>
<!-- Modal HTML -->
<div id="peya-onboarding-modal" class="modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><?php echo esc_html__( 'Información!', 'pedidosya' ); ?></h5>
				<button type="button" class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn button primary-button" data-dismiss="modal"><?php echo esc_html__( 'Ok!', 'pedidosya' ); ?></button>

			</div>
		</div>
	</div>
</div>

<div class="row">

<div class="col-sm-3 mb-auto mt-4" >
  <h1 class="peya-welcome"><span class="peya-sitename"><?php echo esc_html( get_option( 'blogname' ) ); ?> </span> </h1>
	<p><?php echo esc_html__( 'Por favor ingresá los siguientes datos.', 'pedidosya' ); ?></p>
</div>
<div class="col-sm-9 my-auto">

  <?php
	if ( isset( $_POST['submit'] ) ) {

		if ( ! isset( $_POST['wc-peya-nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wc-peya-nonce'] ) ), 'wc-peya-save-settings' )
		) {
			exit;
		} else {
			// Validations.
			if ( ! ( isset( $_POST['wc-peya-country'] ) &&
			isset( $_POST['wc-peya-client-id'] ) &&
			isset( $_POST['wc-peya-client-secret'] ) &&
			isset( $_POST['wc-peya-email'] ) &&
			isset( $_POST['wc-peya-password'] ) ) ) {
				$error_flg   = true;
				$error_descr = __( 'Todos los campos son requeridos para poder validar sus credenciales.', 'pedidosya' );
			} else {
				if ( empty( $_POST['wc-peya-country'] ) ||
				empty( $_POST['wc-peya-client-id'] ) ||
				empty( $_POST['wc-peya-client-secret'] ) ||
				empty( $_POST['wc-peya-email'] ) ||
				empty( $_POST['wc-peya-password'] ) ) {
					$error_flg   = true;
					$error_descr = __( 'Todos los campos son requeridos para poder validar sus credenciales.', 'pedidosya' );
				}
			}
			// Validar Google Api Key para determinados paises.
			if ( in_array( wp_unslash( $_POST['wc-peya-country'] ), array( 'BO', 'PA', 'GT', 'CR', 'EC', 'VE', 'HN', 'SV', 'NI' ) )
				&& empty( $_POST['wc-peya-google-api-key'] ) ) {
				$error_flg   = true;
				$error_descr = __( 'El Google API Key es obligatorio para el país seleccionado.', 'pedidosya' );
			}

			if ( ! empty( $_POST['wc-peya-google-api-key'] ) ) {
				/*Validar Google Api Key válida*/
				$gApi     = new GoogleApi( sanitize_text_field( wp_unslash( $_POST['wc-peya-google-api-key'] ) ) );
				$response = $gApi->validateApiKey();
				if ( isset( $response['error_message'] ) ) {
					$error_flg   = true;
					$error_descr = __( 'El Google API Key es inválido.', 'pedidosya' );
				}
			}
			?>

			<?php

			// Save Values & Validations.
			if ( false === $error_flg ) {
				update_option( 'wc-peya-country', sanitize_text_field( wp_unslash( $_POST['wc-peya-country'] ) ) );
				update_option( 'wc-peya-client-id', sanitize_text_field( wp_unslash( $_POST['wc-peya-client-id'] ) ) );
				update_option( 'wc-peya-client-secret', sanitize_text_field( wp_unslash( $_POST['wc-peya-client-secret'] ) ) );
				update_option( 'wc-peya-email', sanitize_text_field( wp_unslash( $_POST['wc-peya-email'] ) ) );
				update_option( 'wc-peya-password', sanitize_text_field( wp_unslash( $_POST['wc-peya-password'] ) ) );
				update_option( 'wc-peya-express-cron', 'yes' );
				update_option( 'wc-peya-environment', 'production' );
				update_option( 'wc-peya-google-api-key', sanitize_text_field( wp_unslash( $_POST['wc-peya-google-api-key'] ) ) );

				// Validate Credentials.
				$sdk = new PeyaSdk();

				if ( ! $sdk->checkCredentials() ) {
					$error_flg   = true;
					$error_descr = __( 'Las credenciales ingresadas son incorrectas.', 'pedidosya' );
				}
			}

			if ( false === $error_flg ) {
				// If cron should be scheduled but it's not.
				if ( ! wp_next_scheduled( 'wc_peya_cron_update_order' ) ) {
					wp_schedule_event( current_time( 'timestamp' ), 'wc_peya_schedule', 'wc_peya_cron_update_order' );
				}

				// If cron should be scheduled but it's not.
				if ( ! wp_next_scheduled( 'wc_peya_cron_update_settings' ) ) {
					wp_schedule_event( current_time( 'timestamp' ), 'twicedaily', 'wc_peya_cron_update_settings' );
				}

				// Update Schedules.
				Helper::set_peya_schedule_hs_array();

				// Update callbacks.
				Helper::set_callback();
			}

			if ( ! $error_flg ) {
				wp_redirect( esc_url( get_admin_url( null, 'admin.php?page=wc-peya-onboarding' ) ) . '&wc-peya-ostep=3' );
			}
		}
	}

	?>
  <form method="post" action="admin.php?page=wc-peya-onboarding&wc-peya-ostep=2">
  <?php settings_fields( 'wc-peya-settings-onboarding' ); ?>
  <?php do_settings_sections( 'wc-peya-settings-onboarding' ); ?>

	  <label for="wc-peya-country_field" class="wc-peya-required"><?php echo esc_html__( 'País', 'pedidosya' ); ?></label>
	  <?php
		woocommerce_form_field(
			'wc-peya-country',
			array(
				'type'     => 'select',
				'required' => true,
				'options'  => Helper::get_countries(),
			),
			isset( $_POST['wc-peya-country'] ) ? sanitize_text_field( wp_unslash( $_POST['wc-peya-country'] ) ) : get_option( 'wc-peya-country' )
		);
		?>
	  <label for="wc-peya-client-id_field" class="wc-peya-required"><?php echo esc_html__( 'Client ID', 'pedidosya' ); ?></label>
	  <?php
		woocommerce_form_field(
			'wc-peya-client-id',
			array(
				'type'              => 'text',
				'required'          => true,
				'custom_attributes' => array( 'size' => 40 ),
			),
			isset( $_POST['wc-peya-client-id'] ) ? sanitize_text_field( wp_unslash( $_POST['wc-peya-client-id'] ) ) : get_option( 'wc-peya-client-id' )
		);
		?>
	  <span class="tip"><?php echo esc_html__( 'Estos datos los podés encontrar ingresando en tu cuenta de PedidosYa (<a href="https://envios.pedidosya.com">https://envios.pedidosya.com</a>).', 'pedidosya' ); ?></span>
	  <label for="wc-peya-client-secret_field" class="wc-peya-required"><?php echo esc_html__( 'Client Secret', 'pedidosya' ); ?></label>
	  <?php
		woocommerce_form_field(
			'wc-peya-client-secret',
			array(
				'type'              => 'text',
				'required'          => true,
				'custom_attributes' => array( 'size' => 40 ),
			),
			isset( $_POST['wc-peya-client-secret'] ) ? sanitize_text_field( wp_unslash( $_POST['wc-peya-client-secret'] ) ) : get_option( 'wc-peya-client-secret' )
		);
		?>
	  <span class="tip"><?php echo esc_html__( 'Estos datos los podés encontrar ingresando en tu cuenta de PedidosYa (<a href="https://envios.pedidosya.com">https://envios.pedidosya.com</a>).', 'pedidosya' ); ?></span>
	  <label for="wc-peya-email_field" class="wc-peya-required"><?php echo esc_html__( 'Username', 'pedidosya' ); ?></label>
	  <?php
		woocommerce_form_field(
			'wc-peya-email',
			array(
				'type'              => 'email',
				'required'          => true,
				'custom_attributes' => array( 'size' => 40 ),
			),
			isset( $_POST['wc-peya-email'] ) ? sanitize_text_field( wp_unslash( $_POST['wc-peya-email'] ) ) : get_option( 'wc-peya-email' )
		);
		?>
	  <span class="tip"><?php echo esc_html__( 'El username lo podrás encontrar en el mail de bienvenida a PedidosYa Envíos', 'pedidosya' ); ?></span>
	  <label for="wc-peya-password_field" class="wc-peya-required"><?php echo esc_html__( 'Password', 'pedidosya' ); ?></label>
	  <?php
		woocommerce_form_field(
			'wc-peya-password',
			array(
				'type'              => 'password',
				'required'          => true,
				'custom_attributes' => array( 'size' => 40 ),
			),
			isset( $_POST['wc-peya-password'] ) ? sanitize_text_field( wp_unslash( $_POST['wc-peya-password'] ) ) : get_option( 'wc-peya-password' )
		);
		?>
	  <span class="tip"><?php echo esc_html__( 'El Password lo podrás encontrar en el mail de bienvenida a PedidosYa Envíos', 'pedidosya' ); ?></span>
	  <label for="wc-peya-google-api-key_field" class="wc-peya-required"><?php echo esc_html__( 'Google API Key', 'pedidosya' ); ?></label>
	  <?php
		woocommerce_form_field(
			'wc-peya-google-api-key',
			array(
				'type'              => 'text',
				'required'          => true,
				'custom_attributes' => array( 'size' => 40 ),
			),
			isset( $_POST['wc-peya-google-api-key'] ) ? sanitize_text_field( wp_unslash( $_POST['wc-peya-google-api-key'] ) ) : get_option( 'wc-peya-google-api-key' )
		);
		?>
		<?php wp_nonce_field( 'wc-peya-save-settings', 'wc-peya-nonce' ); ?>
	  <span class="tip"><?php echo esc_html__( 'Esta opción te permitirá configurar una API KEY de Google para utilizar la Geolocalización al momento de Finalizar la Compra. De esta manera al comprador le será más facil completar su dirección, y obtendremos así una ubicación más precisa.', 'pedidosya' ); ?></span>

	<?php submit_button( __( 'Guardar', 'pedidosya' ), 'primary-button' ); ?>
  </form>

</div>
</div>

<?php if ( true === $error_flg ) { ?>
<script>
  jQuery(document).ready(function(){
	  jQuery("#peya-onboarding-modal .modal-body").html("<?php echo esc_html( $error_descr ); ?>");
	  jQuery("#peya-onboarding-modal").modal('show');
  });
</script>
<?php } ?>
<script>  
  var countryFieldChange = function(){
	var requireGAKforCountries = ['BO', 'PA', 'GT', 'CR', 'EC', 'VE', 'HN', 'SV','NI'];
	var selectedCountry = jQuery('#wc-peya-country').find(":selected").val();
	if(requireGAKforCountries.includes(selectedCountry)){
	  jQuery("label[for='wc-peya-google-api-key_field']").addClass("wc-peya-required");
	} else {
	  jQuery("label[for='wc-peya-google-api-key_field']").removeClass("wc-peya-required");
	}
  }
  jQuery(document).ready(function(){
	jQuery('#wc-peya-country').on('change', function() {
	  countryFieldChange();
	});
	countryFieldChange();
  })
</script>
<script>
    jQuery(document).ready( function(){              
        // When the user clicks on <span> (x), close the modal
        jQuery("button[data-dismiss='modal']", jQuery("#peya-onboarding-modal")).click( function() {
            jQuery("#peya-onboarding-modal").css("display", 'none'); 
			jQuery("div.modal-backdrop").remove(); 
			jQuery("body").removeClass("modal-open");			
        });              
        
    });
</script>