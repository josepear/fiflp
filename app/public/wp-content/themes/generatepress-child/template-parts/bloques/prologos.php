<?php
$prologos = get_sub_field( 'prologos' );

if ( empty( $prologos ) || ! is_array( $prologos ) ) {
	return;
}
?>

<section class="bloque prologos fade-in">
	<?php foreach ( $prologos as $prologo ) : ?>
		<?php
		$nombre    = isset( $prologo['nombre'] ) ? trim( (string) $prologo['nombre'] ) : '';
		$cargo     = isset( $prologo['cargo'] ) ? trim( (string) $prologo['cargo'] ) : '';
		$contenido = $prologo['contenido'] ?? '';
		$foto      = $prologo['foto'] ?? null;
		$foto_url  = is_array( $foto ) ? ( $foto['url'] ?? '' ) : (string) $foto;
		$foto_alt  = is_array( $foto ) ? ( $foto['alt'] ?? $nombre ) : $nombre;

		if ( '' === $nombre && '' === $cargo && '' === $contenido && '' === $foto_url ) {
			continue;
		}
		?>

		<article class="prologo">
			<?php if ( $foto_url ) : ?>
				<div class="prologo-img">
					<img src="<?php echo esc_url( $foto_url ); ?>" alt="<?php echo esc_attr( $foto_alt ); ?>">
				</div>
			<?php endif; ?>

			<div class="prologo-content">
				<?php if ( $nombre ) : ?>
					<h2 class="prologo-nombre"><?php echo esc_html( $nombre ); ?></h2>
				<?php endif; ?>

				<?php if ( $cargo ) : ?>
					<p class="prologo-cargo"><?php echo esc_html( $cargo ); ?></p>
				<?php endif; ?>

				<?php if ( $contenido ) : ?>
					<div class="prologo-texto">
						<?php echo wp_kses_post( $contenido ); ?>
					</div>
				<?php endif; ?>
			</div>
		</article>
	<?php endforeach; ?>
</section>
