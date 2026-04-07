<?php
$cronologia_id = (int) get_sub_field( 'cronologia' );
$titulo        = '';
$hitos         = array();

if ( $cronologia_id > 0 ) {
	$titulo = get_the_title( $cronologia_id );
	$hitos  = get_field( 'hitos', $cronologia_id );
}

// Compatibilidad temporal por si existe contenido del enfoque anterior en una página.
if ( empty( $hitos ) || ! is_array( $hitos ) ) {
	$hitos = get_sub_field( 'hitos' );
}

if ( empty( $hitos ) || ! is_array( $hitos ) ) {
	return;
}
?>

<section class="bloque cronologia-editorial fade-in">
	<?php if ( '' !== trim( (string) $titulo ) ) : ?>
		<header class="cronologia-editorial__header">
			<h2 class="cronologia-editorial__titulo"><?php echo esc_html( $titulo ); ?></h2>
		</header>
	<?php endif; ?>

	<div class="cronologia-editorial__lista">
		<?php foreach ( $hitos as $hito ) : ?>
			<?php
			$fecha_titulo = isset( $hito['fecha_titulo'] ) ? trim( (string) $hito['fecha_titulo'] ) : '';
			$texto        = isset( $hito['texto'] ) ? (string) $hito['texto'] : '';
			$imagen       = $hito['imagen'] ?? null;
			$caption      = isset( $hito['caption'] ) ? trim( (string) $hito['caption'] ) : '';
			$img_pos      = isset( $hito['imagen_posicion'] ) ? (string) $hito['imagen_posicion'] : 'izquierda';
			$txt_pos      = isset( $hito['texto_posicion'] ) ? (string) $hito['texto_posicion'] : 'derecha';
			$img_bleed    = isset( $hito['imagen_sangre'] ) ? (string) $hito['imagen_sangre'] : 'none';

			if ( '' === $fecha_titulo && '' === trim( wp_strip_all_tags( $texto ) ) && empty( $imagen ) ) {
				continue;
			}

			$imagen_url = '';
			$imagen_alt = '';

			if ( is_array( $imagen ) ) {
				$imagen_url = (string) ( $imagen['sizes']['large'] ?? $imagen['url'] ?? '' );
				$imagen_alt = (string) ( $imagen['alt'] ?? '' );
			} elseif ( is_string( $imagen ) ) {
				$imagen_url = trim( $imagen );
			}

			if ( ! in_array( $img_pos, array( 'izquierda', 'derecha' ), true ) ) {
				$img_pos = 'izquierda';
			}

			if ( ! in_array( $txt_pos, array( 'izquierda', 'derecha' ), true ) ) {
				$txt_pos = 'derecha';
			}

			if ( ! in_array( $img_bleed, array( 'none', 'izquierda', 'derecha' ), true ) ) {
				$img_bleed = 'none';
			}
			?>
			<?php
			$item_classes = array(
				'cronologia-editorial__item',
				'cronologia-editorial__item--txt-' . $txt_pos,
			);

			if ( '' !== $imagen_url ) {
				$item_classes[] = 'has-image';
				$item_classes[] = 'cronologia-editorial__item--img-' . $img_pos;
			}
			?>
			<article class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
				<?php if ( '' !== $imagen_url ) : ?>
					<figure class="cronologia-editorial__media cronologia-editorial__media--bleed-<?php echo esc_attr( $img_bleed ); ?>">
						<a href="<?php echo esc_url( $imagen_url ); ?>" class="lightbox-trigger" data-caption="<?php echo esc_attr( $caption ); ?>">
							<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $imagen_alt ); ?>">
						</a>
						<?php if ( '' !== $caption ) : ?>
							<figcaption class="imagen-meta__caption imagen-meta__caption--tamano-m imagen-meta__caption--tipografia-body">
								<?php echo esc_html( $caption ); ?>
							</figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>

				<div class="cronologia-editorial__meta">
				</div>

				<div class="cronologia-editorial__contenido">
					<?php if ( '' !== $fecha_titulo ) : ?>
						<h3 class="cronologia-editorial__fecha"><?php echo esc_html( $fecha_titulo ); ?></h3>
					<?php endif; ?>

					<?php if ( '' !== trim( wp_strip_all_tags( $texto ) ) ) : ?>
						<div class="cronologia-editorial__texto">
							<?php echo wp_kses_post( wpautop( $texto ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
