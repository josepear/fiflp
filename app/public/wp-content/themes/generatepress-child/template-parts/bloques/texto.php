<?php
$contenido = get_sub_field( 'contenido' );

if ( ! $contenido ) {
	return;
}
?>

<section class="bloque texto fade-in">
	<?php echo wp_kses_post( $contenido ); ?>
</section>
