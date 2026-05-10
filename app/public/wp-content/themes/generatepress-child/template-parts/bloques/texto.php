<?php
$module_args = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$contenido   = function_exists( 'fiflp_get_sub_field_compat' ) ? fiflp_get_sub_field_compat( 'contenido', $module_args ) : get_sub_field( 'contenido' );
$capitular_activa = function_exists( 'fiflp_get_sub_field_compat' ) ? fiflp_get_sub_field_compat( 'capitular_activa', $module_args, true ) : get_sub_field( 'capitular_activa' );

if ( ! $contenido ) {
	return;
}

$capitular_activa = (bool) $capitular_activa;
$clases = array( 'bloque', 'texto', 'fade-in', $capitular_activa ? 'texto--capitular-on' : 'texto--capitular-off' );
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">
	<?php echo wp_kses_post( $contenido ); ?>
</section>
