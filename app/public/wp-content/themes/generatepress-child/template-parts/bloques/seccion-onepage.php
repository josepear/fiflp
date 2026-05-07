<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$seccion_ref = get_sub_field( 'seccion_onepage' );
$seccion_id  = function_exists( 'fiflp_resolve_onepage_seccion_post_id' ) ? fiflp_resolve_onepage_seccion_post_id( $seccion_ref ) : 0;

if ( $seccion_id <= 0 ) {
	return;
}

$numero   = trim( (string) get_field( 'numero_seccion', $seccion_id ) );
$titulo   = trim( (string) get_field( 'titulo_seccion', $seccion_id ) );
$fondo    = strtolower( trim( (string) get_field( 'fondo_color', $seccion_id ) ) );
$columnas = (string) get_field( 'columnas_texto_desktop', $seccion_id );
$tipografia_numero = strtolower( trim( (string) get_field( 'tipografia_numero_onepage', $seccion_id ) ) );
$numero_color_relleno = sanitize_hex_color( (string) get_field( 'numero_color_relleno', $seccion_id ) );
$numero_color_linea   = sanitize_hex_color( (string) get_field( 'numero_color_linea', $seccion_id ) );
$numero_grosor_linea_raw   = get_field( 'numero_grosor_linea', $seccion_id );
$numero_opacidad_relleno_raw = get_field( 'numero_opacidad_relleno', $seccion_id );
$numero_opacidad_linea_raw   = get_field( 'numero_opacidad_linea', $seccion_id );
$numero_escala_vh_raw        = get_field( 'numero_escala_vh', $seccion_id );
$numero_offset_x_raw         = get_field( 'numero_offset_x', $seccion_id );
$numero_top_vh_raw           = get_field( 'numero_top_vh', $seccion_id );
$onepage_morph_end_pct_raw   = get_field( 'onepage_morph_end_pct', $seccion_id );

$numero_grosor_linea     = (float) $numero_grosor_linea_raw;
$numero_opacidad_relleno = (float) $numero_opacidad_relleno_raw;
$numero_opacidad_linea   = (float) $numero_opacidad_linea_raw;
$numero_escala_vh        = (float) $numero_escala_vh_raw;
$numero_offset_x         = (float) $numero_offset_x_raw;
$numero_top_vh           = (float) $numero_top_vh_raw;
$onepage_morph_end_pct   = (float) $onepage_morph_end_pct_raw;
$modulos  = get_field( 'modulos_onepage', $seccion_id );
$items    = get_field( 'items_contenido', $seccion_id ); // Fallback legacy.

if ( '' === $titulo ) {
	$titulo = get_the_title( $seccion_id );
}

$palette = array( '#ea4142', '#fde25f', '#072728', '#e9e9e9', '#73c3b6' );
if ( ! in_array( $fondo, $palette, true ) ) {
	$fondo = '#e9e9e9';
}

if ( ! in_array( $columnas, array( '1', '2', '3' ), true ) ) {
	$columnas = '2';
}

if ( ! in_array( $tipografia_numero, array( 'slanted', 'upright', 'backslanted' ), true ) ) {
	$tipografia_numero = 'slanted';
}

if ( '' === $numero_color_relleno ) {
	$numero_color_relleno = '#1e1e1e';
}

if ( '' === $numero_color_linea ) {
	$numero_color_linea = '#ccb958';
}

if ( $numero_grosor_linea <= 0 ) {
	$numero_grosor_linea = 2;
}
$numero_grosor_linea = max( 1, min( 12, $numero_grosor_linea ) );

if ( '' === (string) $numero_opacidad_relleno_raw ) {
	$numero_opacidad_relleno = 1;
}
if ( '' === (string) $numero_opacidad_linea_raw ) {
	$numero_opacidad_linea = 1;
}
$numero_opacidad_relleno = max( 0, min( 1, $numero_opacidad_relleno ) );
$numero_opacidad_linea   = max( 0, min( 1, $numero_opacidad_linea ) );

if ( $numero_escala_vh <= 0 ) {
	$numero_escala_vh = 260;
}
$numero_escala_vh = max( 40, min( 400, $numero_escala_vh ) );

if ( '' === (string) $numero_offset_x_raw ) {
	$numero_offset_x = 36;
}
if ( '' === (string) $numero_top_vh_raw ) {
	$numero_top_vh = 50;
}
$numero_offset_x = max( -40, min( 80, $numero_offset_x ) );
$numero_top_vh   = max( 20, min( 80, $numero_top_vh ) );

if ( '' === (string) $onepage_morph_end_pct_raw || $onepage_morph_end_pct <= 0 ) {
	$onepage_morph_end_pct = 10;
}
$onepage_morph_end_pct = max( 2, min( 40, $onepage_morph_end_pct ) );

$has_modulos = is_array( $modulos ) && ! empty( $modulos );
$has_legacy  = is_array( $items ) && ! empty( $items );

if ( ! $has_modulos && ! $has_legacy ) {
	return;
}

$onepage_row_index = function_exists( 'get_row_index' ) ? (int) get_row_index() : 0;
$onepage_anchor_id = $onepage_row_index > 0 ? 'fiflp-onepage-row-' . $onepage_row_index : '';
$outline_gradient_id = 'onepage-outline-gradient-' . $seccion_id . '-' . max( 1, $onepage_row_index );
?>

<section class="bloque seccion-onepage seccion-onepage--fullscreen fade-in"<?php echo '' !== $onepage_anchor_id ? ' id="' . esc_attr( $onepage_anchor_id ) . '"' : ''; ?>>
	<div class="seccion-onepage__shell seccion-onepage__cols-<?php echo esc_attr( $columnas ); ?> seccion-onepage__numero-font--<?php echo esc_attr( $tipografia_numero ); ?>" style="--onepage-bg: <?php echo esc_attr( $fondo ); ?>; --onepage-number-solid-color: <?php echo esc_attr( $numero_color_relleno ); ?>; --onepage-number-outline-color: <?php echo esc_attr( $numero_color_linea ); ?>; --onepage-number-stroke-width: <?php echo esc_attr( $numero_grosor_linea ); ?>px; --onepage-number-solid-opacity: <?php echo esc_attr( (string) $numero_opacidad_relleno ); ?>; --onepage-number-outline-opacity: <?php echo esc_attr( (string) $numero_opacidad_linea ); ?>; --onepage-number-size-vh: <?php echo esc_attr( (string) $numero_escala_vh ); ?>; --onepage-number-offset-x: <?php echo esc_attr( (string) $numero_offset_x ); ?>%; --onepage-number-top-vh: <?php echo esc_attr( (string) $numero_top_vh ); ?>vh;" data-onepage-shell data-onepage-morph-end="<?php echo esc_attr( (string) ( $onepage_morph_end_pct / 100 ) ); ?>">
		<aside class="seccion-onepage__indice" aria-label="Índice de sección">
			<h2 class="seccion-onepage__titulo"><?php echo esc_html( $titulo ); ?></h2>
		</aside>
		<?php if ( '' !== $numero ) : ?>
			<p class="seccion-onepage__numero-wrap" aria-hidden="true">
				<svg class="seccion-onepage__numero seccion-onepage__numero--solid" viewBox="0 0 2400 2400" preserveAspectRatio="xMidYMid meet" role="presentation" focusable="false">
					<text class="seccion-onepage__numero-text seccion-onepage__numero-text--solid" x="50%" y="64%" text-anchor="middle"><?php echo esc_html( $numero ); ?></text>
				</svg>
				<svg class="seccion-onepage__numero seccion-onepage__numero--outline" viewBox="0 0 2400 2400" preserveAspectRatio="xMidYMid meet" role="presentation" focusable="false">
					<defs>
						<linearGradient id="<?php echo esc_attr( $outline_gradient_id ); ?>" x1="0%" y1="0%" x2="100%" y2="100%">
							<stop offset="0%" stop-color="<?php echo esc_attr( $numero_color_linea ); ?>" stop-opacity="1" />
							<stop offset="100%" stop-color="<?php echo esc_attr( $numero_color_relleno ); ?>" stop-opacity="1" />
						</linearGradient>
					</defs>
					<text class="seccion-onepage__numero-text seccion-onepage__numero-text--outline" x="50%" y="64%" text-anchor="middle" style="stroke: url(#<?php echo esc_attr( $outline_gradient_id ); ?>);"><?php echo esc_html( $numero ); ?></text>
				</svg>
			</p>
		<?php endif; ?>

		<div class="seccion-onepage__contenido-wrap">
			<?php if ( $has_modulos ) : ?>
				<div class="seccion-onepage__contenido seccion-onepage__contenido--modulos" role="list">
					<?php foreach ( $modulos as $modulo ) : ?>
						<?php
						$layout = isset( $modulo['acf_fc_layout'] ) ? (string) $modulo['acf_fc_layout'] : '';
						if ( '' === $layout ) {
							continue;
						}

						$template_slug = str_replace( '_', '-', $layout );
						$template_path = 'template-parts/bloques/' . $template_slug;
						if ( ! locate_template( $template_path . '.php', false, false ) ) {
							continue;
						}
						?>
						<div class="seccion-onepage__modulo seccion-onepage__modulo--<?php echo esc_attr( $template_slug ); ?>" role="listitem">
							<?php get_template_part( $template_path, null, array( 'module' => $modulo, 'onepage' => true, 'onepage_section_id' => $seccion_id ) ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<?php $stage_index = 0; ?>
				<div class="seccion-onepage__foto-stage" aria-hidden="true" data-onepage-photo-stage>
					<?php foreach ( $items as $item_stage ) : ?>
						<?php
						$foto_stage = $item_stage['foto_vinculada'] ?? null;
						$foto_url_stage = '';
						$foto_alt_stage = '';

						if ( is_array( $foto_stage ) ) {
							$foto_url_stage = (string) ( $foto_stage['sizes']['large'] ?? $foto_stage['url'] ?? '' );
							$foto_alt_stage = (string) ( $foto_stage['alt'] ?? '' );
						} elseif ( is_string( $foto_stage ) ) {
							$foto_url_stage = trim( $foto_stage );
						}

						if ( '' === $foto_url_stage ) {
							continue;
						}
						?>
						<figure class="seccion-onepage__foto-stage-item<?php echo 0 === $stage_index ? ' is-active' : ''; ?>" data-onepage-photo="<?php echo esc_attr( (string) $stage_index ); ?>">
							<img src="<?php echo esc_url( $foto_url_stage ); ?>" alt="<?php echo esc_attr( $foto_alt_stage ); ?>" loading="lazy" decoding="async">
						</figure>
						<?php $stage_index++; ?>
					<?php endforeach; ?>
				</div>

				<?php $stage_index = 0; ?>
				<div class="seccion-onepage__contenido" role="list">
					<?php foreach ( $items as $item ) : ?>
						<?php
						$texto          = $item['texto'] ?? '';
						$sumario        = trim( (string) ( $item['sumario'] ?? '' ) );
						$ladillo        = trim( (string) ( $item['ladillo'] ?? '' ) );
						$nombre_persona = trim( (string) ( $item['nombre_persona'] ?? '' ) );
						$cargo_persona  = trim( (string) ( $item['cargo_persona'] ?? '' ) );
						$foto           = $item['foto_vinculada'] ?? null;
						$posicion_foto  = (string) ( $item['posicion_foto'] ?? 'derecha_contenido' );

						if ( '' === trim( wp_strip_all_tags( (string) $texto ) ) && '' === $sumario && '' === $ladillo && '' === $nombre_persona && '' === $cargo_persona && empty( $foto ) ) {
							continue;
						}

						$foto_url = '';
						$foto_alt = '';
						if ( is_array( $foto ) ) {
							$foto_url = (string) ( $foto['sizes']['large'] ?? $foto['url'] ?? '' );
							$foto_alt = (string) ( $foto['alt'] ?? '' );
						} elseif ( is_string( $foto ) ) {
							$foto_url = trim( $foto );
						}

						if ( ! in_array( $posicion_foto, array( 'izquierda_titulo', 'derecha_contenido' ), true ) ) {
							$posicion_foto = 'derecha_contenido';
						}

						$persona_ficha_url = '';
						$persona_ficha_alt = '';
						if ( is_array( $foto ) ) {
							$persona_ficha_url = (string) ( $foto['sizes']['thumbnail'] ?? $foto['sizes']['medium'] ?? $foto['url'] ?? '' );
							$persona_ficha_alt = (string) ( $foto['alt'] ?? '' );
						} elseif ( is_string( $foto ) && '' !== trim( $foto ) ) {
							$persona_ficha_url = trim( $foto );
						}

						$tiene_ficha_persona = ( '' !== $persona_ficha_url ) || ( '' !== $nombre_persona ) || ( '' !== $cargo_persona );
						$item_classes        = array( 'seccion-onepage__item', 'seccion-onepage__item--' . $posicion_foto );
						$photo_index         = '';
						if ( '' !== $foto_url ) {
							$item_classes[] = 'has-photo';
							$photo_index    = (string) $stage_index;
							$stage_index++;
						}
						?>
						<article class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>" role="listitem"<?php echo '' !== $photo_index ? ' data-onepage-photo-index="' . esc_attr( $photo_index ) . '"' : ''; ?> data-onepage-item>
							<?php if ( '' !== $foto_url && 'izquierda_titulo' === $posicion_foto ) : ?>
								<figure class="seccion-onepage__foto seccion-onepage__foto--left">
									<img src="<?php echo esc_url( $foto_url ); ?>" alt="<?php echo esc_attr( $foto_alt ); ?>" loading="lazy" decoding="async">
								</figure>
							<?php endif; ?>

							<div class="seccion-onepage__texto-wrap">
								<?php if ( '' !== $sumario ) : ?><p class="seccion-onepage__sumario"><?php echo esc_html( $sumario ); ?></p><?php endif; ?>

								<?php if ( $tiene_ficha_persona ) : ?>
									<div class="seccion-onepage__persona">
										<?php if ( '' !== $persona_ficha_url ) : ?><figure class="seccion-onepage__persona-foto"><img src="<?php echo esc_url( $persona_ficha_url ); ?>" alt="<?php echo esc_attr( $persona_ficha_alt ); ?>" width="60" height="60" loading="lazy" decoding="async"></figure><?php endif; ?>
										<?php if ( '' !== $nombre_persona ) : ?><p class="seccion-onepage__persona-nombre"><?php echo esc_html( $nombre_persona ); ?></p><?php endif; ?>
										<?php if ( '' !== $cargo_persona ) : ?><p class="seccion-onepage__persona-cargo"><?php echo esc_html( $cargo_persona ); ?></p><?php endif; ?>
									</div>
								<?php endif; ?>

								<?php if ( '' !== trim( wp_strip_all_tags( (string) $texto ) ) ) : ?><div class="seccion-onepage__texto"><?php echo wp_kses_post( $texto ); ?></div><?php endif; ?>
								<?php if ( '' !== $ladillo ) : ?><p class="seccion-onepage__ladillo"><?php echo esc_html( $ladillo ); ?></p><?php endif; ?>
							</div>

							<?php if ( '' !== $foto_url && 'derecha_contenido' === $posicion_foto ) : ?>
								<figure class="seccion-onepage__foto seccion-onepage__foto--right">
									<img src="<?php echo esc_url( $foto_url ); ?>" alt="<?php echo esc_attr( $foto_alt ); ?>" loading="lazy" decoding="async">
								</figure>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
