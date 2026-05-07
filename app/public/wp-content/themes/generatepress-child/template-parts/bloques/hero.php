<?php
$module_args = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$titulo      = function_exists( 'fiflp_get_sub_field_compat' ) ? fiflp_get_sub_field_compat( 'titulo', $module_args ) : get_sub_field( 'titulo' );

if ( ! $titulo ) {
	return;
}
?>

<section class="bloque hero fade-in">
	<h1><?php echo esc_html( $titulo ); ?></h1>
</section>
