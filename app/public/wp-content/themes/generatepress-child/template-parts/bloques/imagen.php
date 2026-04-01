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

$clases = array( 'bloque', 'imagen', 'fade-in' );

if ( $full ) {
	$clases[] = 'imagen-full';
}
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">

	<figure>

		<a href="<?php echo esc_url( $imagen ); ?>" class="lightbox-trigger">

			<img src="<?php echo esc_url( $imagen ); ?>" alt="">

		</a>

		<?php if ( $caption ) : ?>
			<figcaption>
				<?php echo esc_html( $caption ); ?>
			</figcaption>
		<?php endif; ?>

	</figure>

</section>
