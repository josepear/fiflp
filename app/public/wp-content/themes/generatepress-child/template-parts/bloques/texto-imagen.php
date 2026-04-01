<?php
/**
 * BLOQUE TEXTO + IMAGEN
 * Alterna izquierda/derecha + alineación editorial
 */

global $bloque_index;

if ( ! isset( $bloque_index ) ) {
	$bloque_index = 0;
}

$bloque_index++;

$contenido = get_sub_field( 'contenido' );
$imagen    = get_sub_field( 'imagen' );

if ( ! $contenido && ! $imagen ) {
	return;
}

$invertido = ( $bloque_index % 2 === 0 );
$clases    = array( 'bloque', 'texto-imagen', 'fade-in' );

if ( $invertido ) {
	$clases[] = 'invertido';
	$clases[] = 'derecha';
} else {
	$clases[] = 'izquierda';
}
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">

	<?php if ( $contenido ) : ?>
		<div class="col texto">
			<?php echo wp_kses_post( $contenido ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $imagen ) : ?>
		<div class="col imagen">
			<a href="<?php echo esc_url( $imagen ); ?>" class="lightbox-trigger">
				<img src="<?php echo esc_url( $imagen ); ?>" alt="">
			</a>
		</div>
	<?php endif; ?>

</section>
