<?php
$module_args = fiflp_normalize_editorial_args( $args );
$contenido   = fiflp_get_editorial_field( 'contenido', $module_args );
$capitular_activa = fiflp_get_editorial_field( 'capitular_activa', $module_args, true );

if ( ! $contenido ) {
	return;
}

$capitular_activa = (bool) $capitular_activa;
$clases = array( 'bloque', 'texto', 'fade-in', $capitular_activa ? 'texto--capitular-on' : 'texto--capitular-off' );
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">
	<?php echo wp_kses_post( $contenido ); ?>
</section>
