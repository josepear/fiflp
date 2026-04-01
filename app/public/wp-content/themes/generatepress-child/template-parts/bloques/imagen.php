<?php
/**
 * BLOQUE IMAGEN
 * - Soporta modo normal / full
 * - Añade lightbox (clic en imagen)
 */

$imagen  = get_sub_field( 'imagen' );
$caption = get_sub_field( 'caption' );
$full    = get_sub_field( 'full' );

if ( ! $imagen ) {
	return;
}

$imagen_url = is_array( $imagen ) ? ( $imagen['url'] ?? '' ) : $imagen;
$imagen_alt = is_array( $imagen ) ? ( $imagen['alt'] ?? '' ) : '';

if ( ! $imagen_url ) {
	return;
}

$clases = array( 'bloque', 'imagen', 'fade-in' );

if ( $full ) {
	$clases[] = 'imagen-full';
}
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">

	<figure>

		<a href="<?php echo esc_url( $imagen_url ); ?>" class="lightbox-trigger" data-caption="<?php echo esc_attr( $caption ?? '' ); ?>">
			<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $imagen_alt ); ?>">
		</a>

		<?php if ( $caption ) : ?>
			<figcaption>
				<?php echo esc_html( $caption ); ?>
			</figcaption>
		<?php endif; ?>

	</figure>

</section>
