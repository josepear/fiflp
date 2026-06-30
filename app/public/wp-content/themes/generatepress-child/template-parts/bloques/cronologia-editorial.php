<?php
$get_field = static function ( $name, $default = null ) use ( $args ) {
	return fiflp_get_editorial_field( $name, fiflp_normalize_editorial_args( $args ), $default );
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

// Compatibilidad con contenido anterior por si la página todavía lo usa.
if ( empty( $hitos ) || ! is_array( $hitos ) ) {
	$hitos = $get_field( 'hitos', array() );
}

if ( empty( $hitos ) || ! is_array( $hitos ) ) {
	return;
}
?>

<section class="bloque cronologia-editorial cronologia-editorial--texto-<?php echo esc_attr( $ancho_texto ); ?>">

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
			$img_scale    = isset( $hito['escala_visual_imagen'] ) ? (string) $hito['escala_visual_imagen'] : '100';
			$galeria_masonry_imgs = isset( $hito['galeria_masonry'] ) && is_array( $hito['galeria_masonry'] ) ? $hito['galeria_masonry'] : array();
			$has_galeria_masonry  = ! empty( $galeria_masonry_imgs );
			$galeria_masonry_cols = isset( $hito['galeria_masonry_columnas'] ) ? trim( (string) $hito['galeria_masonry_columnas'] ) : '3';

			$cuadro_hito_id      = isset( $hito['cuadro'] ) ? (int) $hito['cuadro'] : 0;
			$hito_tiene_cuadro   = $cuadro_hito_id > 0 && 'fiflp_cuadro' === get_post_type( $cuadro_hito_id );

			// Compatibilidad con el campo antiguo "imagen_multiplicar".
			$img_multiply_legacy = ! empty( $hito['imagen_multiplicar'] );
			$img_multiply_1      = array_key_exists( 'imagen_multiplicar_1', $hito )
				? ! empty( $hito['imagen_multiplicar_1'] )
				: $img_multiply_legacy;
			$img_multiply_2      = array_key_exists( 'imagen_multiplicar_2', $hito )
				? ! empty( $hito['imagen_multiplicar_2'] )
				: $img_multiply_legacy;
			$img_tone_1 = array(
				'shadows'    => isset( $hito['ajuste_sombras_imagen_1'] ) ? (float) $hito['ajuste_sombras_imagen_1'] : 0.0,
				'mids'       => isset( $hito['ajuste_medios_imagen_1'] ) ? (float) $hito['ajuste_medios_imagen_1'] : 0.0,
				'highlights' => isset( $hito['ajuste_luces_imagen_1'] ) ? (float) $hito['ajuste_luces_imagen_1'] : 0.0,
			);
			$img_tone_2 = array(
				'shadows'    => isset( $hito['ajuste_sombras_imagen_2'] ) ? (float) $hito['ajuste_sombras_imagen_2'] : 0.0,
				'mids'       => isset( $hito['ajuste_medios_imagen_2'] ) ? (float) $hito['ajuste_medios_imagen_2'] : 0.0,
				'highlights' => isset( $hito['ajuste_luces_imagen_2'] ) ? (float) $hito['ajuste_luces_imagen_2'] : 0.0,
			);

			if ( '' === $fecha_titulo && '' === trim( wp_strip_all_tags( $texto ) ) && empty( $imagen ) && empty( $imagen_2 ) && ! $has_galeria_masonry && ! $hito_tiene_cuadro ) {
				continue;
			}

			$extract_media = static function ( $raw_image, $raw_caption, $raw_multiply = false, $raw_tone = array() ) {
				$url = '';
				$lightbox_url = '';
				$alt = '';

				if ( is_array( $raw_image ) ) {
					$lightbox_url = (string) ( $raw_image['url'] ?? '' );
					$url          = (string) ( $raw_image['sizes']['large'] ?? $lightbox_url );
					$alt = (string) ( $raw_image['alt'] ?? '' );
				} elseif ( is_string( $raw_image ) ) {
					$url = trim( $raw_image );
					$lightbox_url = $url;
				}

				if ( '' === $url ) {
					return null;
				}

				if ( '' === $lightbox_url ) {
					$lightbox_url = $url;
				}

				$shadows    = isset( $raw_tone['shadows'] ) ? max( -100, min( 100, (float) $raw_tone['shadows'] ) ) : 0.0;
				$mids       = isset( $raw_tone['mids'] ) ? max( -100, min( 100, (float) $raw_tone['mids'] ) ) : 0.0;
				$highlights = isset( $raw_tone['highlights'] ) ? max( -100, min( 100, (float) $raw_tone['highlights'] ) ) : 0.0;
				$has_tone   = ( 0.0 !== $shadows || 0.0 !== $mids || 0.0 !== $highlights );
				$brightness = 1 + ( ( $mids + ( $shadows * 0.35 ) + ( $highlights * 0.25 ) ) * 0.003 );
				$contrast   = 1 + ( ( $highlights - $shadows ) * 0.002 );

				return array(
					'url'     => $url,
					'lightbox_url' => $lightbox_url,
					'alt'     => $alt,
					'caption' => trim( (string) $raw_caption ),
					'multiply' => ! empty( $raw_multiply ),
					'tone_style' => $has_tone
						? sprintf(
							'filter: brightness(%1$.4f) contrast(%2$.4f) !important;',
							$brightness,
							$contrast
						)
						: '',
				);
			};

			$medias = array_values(
				array_filter(
					array(
						$extract_media( $imagen, $caption, $img_multiply_1, $img_tone_1 ),
						$extract_media( $imagen_2, $caption_2, $img_multiply_2, $img_tone_2 ),
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

			if ( ! in_array( $img_scale, array( '100', '75', '50', '33' ), true ) ) {
				$img_scale = '100';
			}

			$figure_classes = array(
				'cronologia-editorial__media',
				'cronologia-editorial__media--bleed-' . $img_bleed,
				'cronologia-editorial__media--escala-' . $img_scale,
			);
			foreach ( $medias as $_m ) {
				if ( ! empty( $_m['multiply'] ) ) {
					$figure_classes[] = 'cronologia-editorial__media--multiply';
					break;
				}
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

						<?php if ( $hito_tiene_cuadro && function_exists( 'fiflp_render_cuadro' ) ) : ?>
							<?php fiflp_render_cuadro( $cuadro_hito_id, array( 'context' => 'cronologia' ) ); ?>
						<?php endif; ?>

						<?php if ( $has_galeria_masonry ) : ?>
							<?php
							get_template_part(
								'template-parts/bloques/galeria-masonry',
								null,
								array(
									'imagenes' => $galeria_masonry_imgs,
									'columnas' => $galeria_masonry_cols,
									'context'  => 'cronologia',
								)
							);
							?>
						<?php endif; ?>

						<?php if ( $has_media ) : ?>
							<figure class="<?php echo esc_attr( implode( ' ', $figure_classes ) ); ?>">
								<div class="cronologia-editorial__media-stack <?php echo count( $medias ) > 1 ? 'cronologia-editorial__media-stack--cols-2' : 'cronologia-editorial__media-stack--cols-1'; ?>">
									<?php foreach ( $medias as $media ) : ?>
										<a href="<?php echo esc_url( $media['lightbox_url'] ?? $media['url'] ); ?>" class="lightbox-trigger<?php echo ! empty( $media['multiply'] ) ? ' is-multiply' : ''; ?>" data-caption="<?php echo esc_attr( $media['caption'] ); ?>">
											<img src="<?php echo esc_url( $media['url'] ); ?>" alt="<?php echo esc_attr( $media['alt'] ); ?>" loading="lazy" decoding="async" class="<?php echo ! empty( $media['multiply'] ) ? 'is-multiply' : ''; ?>"<?php echo '' !== $media['tone_style'] ? ' style="' . esc_attr( $media['tone_style'] ) . '"' : ''; ?>>
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
