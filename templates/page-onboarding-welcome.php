<?php
/**
 * Main Onboarding Template - Welcome Page
 *
 * @package Ecomerciar\PedidosYa\Templates
 */

?>
<div class="vertical-center">
  <h1 class="peya-welcome"><span class="peya-sitename"><?php echo esc_html( get_option( 'blogname' ) ); ?> </span> </h1>
  <p><?php echo esc_html__( 'Sigue estas instrucciones para configurar PedidosYa como opción de envío en tu tienda WooCommerce:' ); ?></p>
  <p><span class="numbering"><?php echo esc_html__( '1)', 'pedidosya' ); ?> </span> <?php echo esc_html__( ' Para poder utilizar PedidosYa como medio de envío, es necesario que tengas una cuenta activa de PedidosYa. Si aún no la tienes, generala', 'pedidosya' ); ?>  <a href="<?php echo esc_html__( 'https://envios.pedidosya.com', 'pedidosya' ); ?>" target="_blank">  <?php echo esc_html__( 'ACÁ', 'pedidosya' ); ?></a></p>
  <p><span class="numbering"><?php echo esc_html__( '2)', 'pedidosya' ); ?> </span>  <?php echo esc_html__( ' En el próximo paso , ingresa los datos requeridos.', 'pedidosya' ); ?></p>
</div>
