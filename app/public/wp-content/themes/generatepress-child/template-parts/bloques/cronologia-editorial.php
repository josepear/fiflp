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
$onepage       = ! empty( $args['onepage'] );
$onepage_section_id = isset( $args['onepage_section_id'] ) ? (int) $args['onepage_section_id'] : 0;
$decor_numero  = '';
$decor_numero_font = 'slanted';
$decor_numero_line_color = '#ccb958';
$decor_numero_stroke_width = 2.0;
$decor_numero_opacity = 1.0;
$decor_numero_size_vh = 260.0;
$decor_numero_offset_x = 36.0;
$decor_numero_top_vh = 50.0;
$decor_stroke_override = '';

if ( $onepage && $onepage_section_id > 0 ) {
	$numero_raw = get_field( 'numero_seccion', $onepage_section_id );
	$decor_numero = function_exists( 'fiflp_format_onepage_section_number' )
		? fiflp_format_onepage_section_number( $numero_raw )
		: trim( (string) $numero_raw );

	$font_raw = strtolower( trim( (string) get_field( 'tipografia_numero_onepage', $onepage_section_id ) ) );
	if ( in_array( $font_raw, array( 'slanted', 'upright', 'backslanted' ), true ) ) {
		$decor_numero_font = $font_raw;
	}

	$line_color_raw = sanitize_hex_color( (string) get_field( 'numero_color_linea', $onepage_section_id ) );
	if ( '' !== $line_color_raw ) {
		$decor_numero_line_color = $line_color_raw;
	}

	$stroke_width_raw = (float) get_field( 'numero_grosor_linea', $onepage_section_id );
	if ( $stroke_width_raw > 0 ) {
		$decor_numero_stroke_width = max( 1, min( 12, $stroke_width_raw ) );
	}

	$opacity_raw = (float) get_field( 'numero_opacidad_linea', $onepage_section_id );
	$opacity_raw_field = get_field( 'numero_opacidad_linea', $onepage_section_id );
	if ( '' !== (string) $opacity_raw_field ) {
		$decor_numero_opacity = max( 0, min( 1, $opacity_raw ) );
	}

	$size_vh_raw = (float) get_field( 'numero_escala_vh', $onepage_section_id );
	if ( $size_vh_raw > 0 ) {
		$decor_numero_size_vh = max( 40, min( 400, $size_vh_raw ) );
	}

	$offset_x_raw = get_field( 'numero_offset_x', $onepage_section_id );
	if ( '' !== (string) $offset_x_raw ) {
		$decor_numero_offset_x = max( -40, min( 80, (float) str_replace( ',', '.', (string) $offset_x_raw ) ) );
	}

	$top_vh_raw = get_field( 'numero_top_vh', $onepage_section_id );
	if ( '' !== (string) $top_vh_raw ) {
		$decor_numero_top_vh = max( 0, min( 120, (float) str_replace( ',', '.', (string) $top_vh_raw ) ) );
	}
}

$decor_stroke_override = sanitize_hex_color( (string) $get_field( 'color_trazo_svg_cronologia', '' ) );
if ( '' !== $decor_stroke_override ) {
	$decor_numero_line_color = $decor_stroke_override;
}

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

<section class="bloque cronologia-editorial cronologia-editorial--texto-<?php echo esc_attr( $ancho_texto ); ?><?php echo '' !== $decor_numero ? ' cronologia-editorial--with-onepage-number seccion-onepage__numero-font--' . esc_attr( $decor_numero_font ) : ''; ?>"<?php echo '' !== $decor_numero ? ' style="--cron-onepage-number-line-color:' . esc_attr( $decor_numero_line_color ) . '; --cron-onepage-number-stroke-width:' . esc_attr( (string) $decor_numero_stroke_width ) . 'px; --cron-onepage-number-opacity:' . esc_attr( (string) $decor_numero_opacity ) . '; --cron-onepage-number-size-vh:' . esc_attr( (string) $decor_numero_size_vh ) . '; --cron-onepage-number-offset-x:' . esc_attr( (string) $decor_numero_offset_x ) . '%; --cron-onepage-number-top-vh:' . esc_attr( (string) $decor_numero_top_vh ) . 'vh; --onepage-number-outline-color:' . esc_attr( $decor_numero_line_color ) . '; --onepage-number-stroke-width:' . esc_attr( (string) $decor_numero_stroke_width ) . 'px; --onepage-number-outline-opacity:' . esc_attr( (string) $decor_numero_opacity ) . '; --onepage-number-size-vh:' . esc_attr( (string) $decor_numero_size_vh ) . '; --onepage-number-offset-x:' . esc_attr( (string) $decor_numero_offset_x ) . '%; --onepage-number-top-vh:' . esc_attr( (string) $decor_numero_top_vh ) . 'vh;"' : ''; ?>>
	<?php if ( '' !== $decor_numero ) : ?>
		<div class="cronologia-editorial__onepage-number" aria-hidden="true">
			<svg class="seccion-onepage__numero seccion-onepage__numero--outline cronologia-editorial__onepage-number-svg" viewBox="0 0 2400 2400" preserveAspectRatio="xMidYMid meet" role="presentation" focusable="false">
				<text class="seccion-onepage__numero-text seccion-onepage__numero-text--outline cronologia-editorial__onepage-number-text" x="50%" y="64%" text-anchor="middle"><?php echo esc_html( $decor_numero ); ?></text>
			</svg>
		</div>
	<?php endif; ?>

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

			// Compatibilidad: si un hito antiguo solo tiene el campo legacy
			// "imagen_multiplicar", lo reutilizamos para ambas imágenes.
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
											<img src="<?php echo esc_url( $media['url'] ); ?>" alt="<?php echo esc_attr( $media['alt'] ); ?>" class="<?php echo ! empty( $media['multiply'] ) ? 'is-multiply' : ''; ?>"<?php echo '' !== $media['tone_style'] ? ' style="' . esc_attr( $media['tone_style'] ) . '"' : ''; ?>>
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
