<?php
/**
 * Main Onboarding Template
 *
 * @package Ecomerciar\PedidosYa\Templates
 */

use Ecomerciar\PedidosYa\Helper\Helper;

defined( 'ABSPATH' ) || exit;

if ( isset( $_GET['wc-peya-ostep'] ) ) {
	$current_step = sanitize_text_field( wp_unslash( $_GET['wc-peya-ostep'] ) );
} else {
	$current_step = 1;
}
if ( empty( $current_step ) ) {
	$current_step = 1;
}
if ( $current_step < 1 || $current_step > 3 ) {
	$current_step = 1;
}
$next_step = $current_step + 1;
$prev_step = $current_step - 1;

$leftImage = helper::get_assets_folder_url() . '/img/onboarding_s' . $current_step . '.png';
$rightPart = 'onboarding-welcome';
switch ( $current_step ) {
	case 1:
		$rightPart = 'onboarding-welcome';
		break;
	case 2:
		$rightPart = 'onboarding-settings';
		break;
	case 3:
		$rightPart = 'onboarding-finish';
		break;
}
?>

<div class="wrap" id="peya-onboarding">
  <div class="container-fluid">
	<div class="row">

	  <div class="col-sm-3 my-auto" >
		<img class="imageLeft" alt="breadcum" src="<?php echo esc_url( $leftImage ); ?>" />
	  </div>
	  <?php if ( '2' === $current_step ) { ?>
		<div class="col-sm-9 mt-auto">
	  <?php } else { ?>
		<div class="col-sm-9 my-auto">
	  <?php } ?>
		<div class="container-fluid">
		  <div class="row ">
			<div class="col ">
			  <?php helper::get_template_part( 'page', $rightPart ); ?>
			</div>
		  </div>
		  <div class="row mb-0" >
			<div class="col">

			</div>
		  </div>
		</div>

	  </div>
	</div>
	<div class="row" >
	  <div class="col-3"></div>
	  <div class="col-9">
		<?php if ( 2 === $current_step || 3 === $current_step ) { ?>
		  <a href="<?php echo esc_url( get_admin_url( null, 'admin.php?page=wc-peya-onboarding' ) ) . '&wc-peya-ostep=' . $prev_step; ?>"> <button class="secondary-button"> <?php echo esc_html__( 'Volver', 'pedidosya' ); ?> </button></a>
		<?php } ?>
		<?php if ( $current_step == 1 ) { ?>
		<a href="<?php echo esc_url( get_admin_url( null, 'admin.php?page=wc-peya-onboarding' ) ) . '&wc-peya-ostep=' . $next_step; ?>"> <button class="primary-button" style="margin-top:-50px"> <?php echo esc_html__( 'Siguiente  >', 'pedidosya' ); ?> </button></a>
		<?php } ?>
	  </div>
	</div>
  </div>
</div>
