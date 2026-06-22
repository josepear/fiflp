<?php
$titulo = fiflp_get_editorial_field( 'titulo', isset( $args ) && is_array( $args ) ? $args : array() );

if ( ! $titulo ) {
	return;
}
?>

<section class="bloque hero fade-in">
	<h1><?php echo esc_html( $titulo ); ?></h1>
</section>
