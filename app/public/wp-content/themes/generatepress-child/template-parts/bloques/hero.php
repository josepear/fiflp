<?php
$titulo = get_sub_field( 'titulo' );

if ( ! $titulo ) {
	return;
}
?>

<section class="bloque hero fade-in">
	<h1><?php echo esc_html( $titulo ); ?></h1>
</section>
