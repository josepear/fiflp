<?php
$get_field = static function ( $name, $default = null ) use ( $args ) {
	if ( function_exists( 'fiflp_get_sub_field_compat' ) ) {
		return fiflp_get_sub_field_compat( $name, $args ?? array(), $default );
	}

	$value = get_sub_field( $name );
	return null !== $value ? $value : $default;
};

$cronologia_id = (int) $get_field( 'cronologia', 0 );
$titulo        = '';
$titulo_modulo = trim( (string) $get_field( 'titulo_cronologia', '' ) );
$ancho_texto   = strtolower( trim( (string) $get_field( 'ancho_texto_cronologia', 'normal' ) ) );
$hitos         = array();

if ( ! in_array( $ancho_texto, array( 'estrecho', 'normal', 'ancho' ), true ) ) {
	$ancho_texto = 'normal';
}

if ( $cronologia_id > 0 ) {
	$titulo = get_the_title( $cronologia_id );
	$hitos  = get_field( 'hitos', $cronologia_id );
}

if ( '' !== $titulo_modulo ) {
	$titulo = $titulo_modulo;
}

// Compatibilidad temporal por si existe contenido del enfoque anterior en una página.
if ( empty( $hitos ) || ! is_array( $hitos ) ) {
	$hitos = $get_field( 'hitos', array() );
}

if ( empty( $hitos ) || ! is_array( $hitos ) ) {
	return;
}
?>

<section class="bloque cronologia-editorial cronologia-editorial--texto-<?php echo esc_attr( $ancho_texto ); ?> fade-in">
	<?php if ( '' !== trim( (string) $titulo ) ) : ?>
		<header class="cronologia-editorial__header">
			<h2 class="cronologia-editorial__titulo"><?php echo wp_kses_post( nl2br( esc_html( $titulo ) ) ); ?></h2>
		</header>
	<?php endif; ?>

	<div class="cronologia-editorial__lista">
		<?php foreach ( $hitos as $hito ) : ?>
			<?php
			$fecha_titulo = isset( $hito['fecha_titulo'] ) ? trim( (string) $hito['fecha_titulo'] ) : '';
			$texto        = isset( $hito['texto'] ) ? (string) $hito['texto'] : '';
			$imagen       = $hito['imagen'] ?? null;
			$caption      = isset( $hito['caption'] ) ? trim( (string) $hito['caption'] ) : '';
			$imagen_2     = $hito['imagen_2'] ?? null;
			$caption_2    = isset( $hito['caption_2'] ) ? trim( (string) $hito['caption_2'] ) : '';
			$img_pos      = isset( $hito['imagen_posicion'] ) ? (string) $hito['imagen_posicion'] : 'izquierda';
			$txt_pos      = isset( $hito['texto_posicion'] ) ? (string) $hito['texto_posicion'] : 'derecha';
			$img_bleed    = isset( $hito['imagen_sangre'] ) ? (string) $hito['imagen_sangre'] : 'none';

			if ( '' === $fecha_titulo && '' === trim( wp_strip_all_tags( $texto ) ) && empty( $imagen ) && empty( $imagen_2 ) ) {
				continue;
			}

			$extract_media = static function ( $raw_image, $raw_caption ) {
				$url = '';
				$alt = '';

				if ( is_array( $raw_image ) ) {
					$url = (string) ( $raw_image['sizes']['large'] ?? $raw_image['url'] ?? '' );
					$alt = (string) ( $raw_image['alt'] ?? '' );
				} elseif ( is_string( $raw_image ) ) {
					$url = trim( $raw_image );
				}

				if ( '' === $url ) {
					return null;
				}

				return array(
					'url'     => $url,
					'alt'     => $alt,
					'caption' => trim( (string) $raw_caption ),
				);
			};

			$medias = array_values(
				array_filter(
					array(
						$extract_media( $imagen, $caption ),
						$extract_media( $imagen_2, $caption_2 ),
					)
				)
			);

			$has_media = ! empty( $medias );

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

			if ( $has_media ) {
				$item_classes[] = 'has-image';
				$item_classes[] = 'cronologia-editorial__item--img-' . $img_pos;
			}
			?>
			<article class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
				<div class="cronologia-editorial__contenido-wrap">
					<div class="cronologia-editorial__contenido">
						<?php if ( '' !== $fecha_titulo ) : ?>
							<h3 class="cronologia-editorial__fecha"><?php echo esc_html( $fecha_titulo ); ?></h3>
						<?php endif; ?>

						<?php if ( '' !== trim( wp_strip_all_tags( $texto ) ) ) : ?>
							<div class="cronologia-editorial__texto">
								<?php echo wp_kses_post( wpautop( $texto ) ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $has_media ) : ?>
							<figure class="cronologia-editorial__media cronologia-editorial__media--bleed-<?php echo esc_attr( $img_bleed ); ?>">
								<div class="cronologia-editorial__media-stack <?php echo count( $medias ) > 1 ? 'cronologia-editorial__media-stack--cols-2' : 'cronologia-editorial__media-stack--cols-1'; ?>">
									<?php foreach ( $medias as $media ) : ?>
										<a href="<?php echo esc_url( $media['url'] ); ?>" class="lightbox-trigger" data-caption="<?php echo esc_attr( $media['caption'] ); ?>">
											<img src="<?php echo esc_url( $media['url'] ); ?>" alt="<?php echo esc_attr( $media['alt'] ); ?>">
										</a>
									<?php endforeach; ?>
								</div>
							</figure>
						<?php endif; ?>
					</div>

					<div class="cronologia-editorial__meta">
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
