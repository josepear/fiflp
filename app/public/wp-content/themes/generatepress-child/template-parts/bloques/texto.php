<?php
$module_args = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$contenido   = function_exists( 'fiflp_get_sub_field_compat' ) ? fiflp_get_sub_field_compat( 'contenido', $module_args ) : get_sub_field( 'contenido' );

if ( ! $contenido ) {
	return;
}
?>

<section class="bloque texto fade-in">
	<?php echo wp_kses_post( $contenido ); ?>
</section>
