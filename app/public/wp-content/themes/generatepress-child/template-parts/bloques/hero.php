<?php
$titulo = fiflp_get_editorial_field( 'titulo', fiflp_normalize_editorial_args( $args ) );

if ( ! $titulo ) {
	return;
}
?>

<section class="bloque hero fade-in">
	<h1><?php echo esc_html( $titulo ); ?></h1>
</section>
