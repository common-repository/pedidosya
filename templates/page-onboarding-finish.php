<?php
/**
 * Main Onboarding Template - Finish Page
 *
 * @package Ecomerciar\PedidosYa\Templates
 */

?>
<div>
  <h1 class="peya-welcome"><span class="peya-sitename"><?php echo esc_html( get_option( 'blogname' ) ); ?> </span></h1>
  <p><?php echo esc_html__( 'Has finalizado la configuración inicial para PedidosYa.', 'pedidosya' ); ?></p>
  <p><?php echo esc_html__( 'Ahora necesitas configurar los puntos de retiro desde los ajustes de WooCommerce.', 'pedidosya' ); ?></p>
  <p><?php echo esc_html__( 'Dirígete a', 'pedidosya' ); ?> <a href="<?php echo esc_url( get_admin_url( null, 'admin.php?page=wc-settings&tab=shipping' ) ); ?>">  <?php echo esc_html__( 'WooCommerce > Ajustes > Envíos > Zonas de Envíos', 'pedidosya' ); ?> </a> <?php echo esc_html__( 'para configurar el método de envío PedidosYa y su punto de retiro.', 'pedidosya' ); ?></p>
</div>
