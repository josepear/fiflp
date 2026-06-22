<?php
/**
 * BLOQUE TEXTO + IMAGEN
 * Posición de foto, tipografía FK, redondeo, alineación, interletraje, escala y pie (caption + título editorial, igual que Imagen).
 */

global $bloque_index;

if ( ! isset( $bloque_index ) ) {
	$bloque_index = 0;
}

$bloque_index++;

$args = $args ?? array();

$gf = static function ( $name, $default = null ) use ( $args ) {
	return fiflp_get_editorial_field( $name, $args, $default );
};

$contenido_raw = $gf( 'contenido', '' );
$imagen_raw    = $gf( 'imagen', null );

$imagen_url = '';
$imagen_alt = '';

if ( is_array( $imagen_raw ) ) {
	$imagen_url = (string) ( $imagen_raw['sizes']['large'] ?? $imagen_raw['url'] ?? '' );
	$imagen_alt = (string) ( $imagen_raw['alt'] ?? '' );
} elseif ( is_string( $imagen_raw ) ) {
	$imagen_url = trim( $imagen_raw );
}

$contenido = is_string( $contenido_raw ) ? $contenido_raw : '';

if ( ! $contenido && '' === $imagen_url ) {
	return;
}

$imagen_pos = strtolower( trim( (string) $gf( 'imagen_posicion', 'auto' ) ) );

if ( ! in_array( $imagen_pos, array( 'auto', 'izquierda', 'derecha' ), true ) ) {
	$imagen_pos = 'auto';
}

if ( 'auto' === $imagen_pos ) {
	$invertido = ( $bloque_index % 2 === 0 );
} else {
	$invertido = ( 'izquierda' === $imagen_pos );
}

$clases = array( 'bloque', 'texto-imagen', 'fade-in' );

if ( $invertido ) {
	$clases[] = 'invertido';
	$clases[] = 'derecha';
} else {
	$clases[] = 'izquierda';
}

$tipografia_fk = strtolower( trim( (string) $gf( 'tipografia_fk', 'body' ) ) );

if ( in_array( $tipografia_fk, array( 'upright', 'slanted', 'backslanted' ), true ) ) {
	$clases[] = 'texto-imagen--fk-' . $tipografia_fk;
}

$sin_redondeo = (bool) $gf( 'imagen_sin_redondeo', false );

if ( ! empty( $sin_redondeo ) ) {
	$clases[] = 'texto-imagen--sin-redondeo';
}

$alineacion = strtolower( trim( (string) $gf( 'alineacion_texto', 'auto' ) ) );

$align_map = array(
	'izquierda' => 'texto-imagen--align-left',
	'centro'    => 'texto-imagen--align-center',
	'derecha'   => 'texto-imagen--align-right',
);

if ( isset( $align_map[ $alineacion ] ) ) {
	$clases[] = $align_map[ $alineacion ];
}

$alineacion_vertical = strtolower( trim( (string) $gf( 'alineacion_vertical', 'centro' ) ) );

$valign_map = array(
	'arriba' => 'texto-imagen--valign-top',
	'centro' => 'texto-imagen--valign-center',
	'abajo'  => 'texto-imagen--valign-bottom',
);

if ( isset( $valign_map[ $alineacion_vertical ] ) ) {
	$clases[] = $valign_map[ $alineacion_vertical ];
}

$disposicion_bloque = strtolower( trim( (string) $gf( 'disposicion_bloque', 'columnas' ) ) );
if ( ! in_array( $disposicion_bloque, array( 'columnas', 'cenido' ), true ) ) {
	$disposicion_bloque = 'columnas';
}
if ( 'cenido' === $disposicion_bloque ) {
	$clases[] = 'texto-imagen--wrap';
}

$espaciado_letras = strtolower( trim( (string) $gf( 'espaciado_letras', 'auto' ) ) );

$tracking_map = array(
	'tight'  => 'texto-imagen--tracking-tight',
	'normal' => 'texto-imagen--tracking-normal',
	'wide'   => 'texto-imagen--tracking-wide',
	'wider'  => 'texto-imagen--tracking-wider',
);

if ( isset( $tracking_map[ $espaciado_letras ] ) ) {
	$clases[] = $tracking_map[ $espaciado_letras ];
}

$escala_visual_imagen = trim( (string) $gf( 'escala_visual_imagen', '100' ) );

if ( ! in_array( $escala_visual_imagen, array( '100', '75', '50', '33' ), true ) ) {
	$escala_visual_imagen = '100';
}

$ti_scale_map = array(
	'100' => '1',
	'75'  => '0.75',
	'50'  => '0.5',
	'33'  => '0.33',
);
$ti_img_scale_css = $ti_scale_map[ $escala_visual_imagen ] ?? '1';

if ( '100' !== $escala_visual_imagen ) {
	$clases[] = 'texto-imagen--img-scaled';
}

/* Columnas del grid: la columna de texto gana espacio cuando la escala visual es menor */
$clases[] = 'texto-imagen--grid-' . $escala_visual_imagen;

$clases_col_imagen = array( 'col', 'imagen', 'texto-imagen__imagen', 'texto-imagen__imagen--escala-' . $escala_visual_imagen );
if ( 'cenido' === $disposicion_bloque ) {
	$clases_col_imagen[] = $invertido ? 'texto-imagen__imagen--wrap-left' : 'texto-imagen__imagen--wrap-right';
}

/* Pie de foto (mismos campos y criterios que template-parts/bloques/imagen.php) */
$caption                    = trim( (string) $gf( 'caption', '' ) );
$titulo_editorial_imagen    = trim( (string) $gf( 'titulo_editorial_imagen', '' ) );
$variante_titulo_imagen     = trim( (string) $gf( 'variante_titulo_imagen', 'linea' ) );
$tipografia_titulo_imagen   = trim( (string) $gf( 'tipografia_titulo_imagen', 'backslanted' ) );
$ancho_titulo_imagen        = trim( (string) $gf( 'ancho_titulo_imagen', 'igual_rotulo' ) );
$alineacion_titulo_imagen   = trim( (string) $gf( 'alineacion_titulo_imagen', 'left' ) );
$disposicion_titulo_imagen  = trim( (string) $gf( 'disposicion_titulo_imagen', 'inline' ) );
$tamano_titulo_imagen       = trim( (string) $gf( 'tamano_titulo_imagen', 'm' ) );
$tamano_pie_imagen          = trim( (string) $gf( 'tamano_pie_imagen', 'm' ) );
$tipografia_pie_imagen      = trim( (string) $gf( 'tipografia_pie_imagen', 'body' ) );
$color_titulo_imagen        = sanitize_hex_color( (string) $gf( 'color_titulo_imagen', '' ) );
$color_borde_titulo_imagen  = sanitize_hex_color( (string) $gf( 'color_borde_titulo_imagen', '' ) );
$color_solido_titulo_imagen = sanitize_hex_color( (string) $gf( 'color_solido_titulo_imagen', '' ) );
$color_letra_titulo_imagen  = sanitize_hex_color( (string) $gf( 'color_letra_titulo_imagen', '' ) );

if ( ! in_array( $variante_titulo_imagen, array( 'linea', 'relleno', 'linea_inversa', 'relleno_inverso' ), true ) ) {
	$variante_titulo_imagen = 'linea';
}

if ( ! in_array( $tipografia_titulo_imagen, array( 'backslanted', 'slanted' ), true ) ) {
	$tipografia_titulo_imagen = 'backslanted';
}

if ( ! in_array( $ancho_titulo_imagen, array( 'igual_rotulo', 'estrecho', 'ancho' ), true ) ) {
	$ancho_titulo_imagen = 'igual_rotulo';
}

if ( ! in_array( $alineacion_titulo_imagen, array( 'left', 'center', 'right' ), true ) ) {
	$alineacion_titulo_imagen = 'left';
}

if ( ! in_array( $disposicion_titulo_imagen, array( 'inline', 'stacked' ), true ) ) {
	$disposicion_titulo_imagen = 'inline';
}

if ( ! in_array( $tamano_titulo_imagen, array( 'xs', 's', 'm', 'l', 'xl' ), true ) ) {
	$tamano_titulo_imagen = 'm';
}

if ( ! in_array( $tamano_pie_imagen, array( 's', 'm', 'l' ), true ) ) {
	$tamano_pie_imagen = 'm';
}

if ( ! in_array( $tipografia_pie_imagen, array( 'body', 'meta' ), true ) ) {
	$tipografia_pie_imagen = 'body';
}

if ( ! $color_titulo_imagen ) {
	$color_titulo_imagen = '#0f2d30';
}

if ( ! $color_borde_titulo_imagen ) {
	$color_borde_titulo_imagen = $color_titulo_imagen;
}

if ( ! $color_solido_titulo_imagen ) {
	$color_solido_titulo_imagen = $color_titulo_imagen;
}

$es_inverso_titulo  = in_array( $variante_titulo_imagen, array( 'linea_inversa', 'relleno_inverso' ), true );
$es_relleno_titulo  = in_array( $variante_titulo_imagen, array( 'relleno', 'relleno_inverso' ), true );
$clase_marco_titulo = $es_relleno_titulo
	? 'rotulo-editorial__marco-shape rotulo-editorial__marco-shape--relleno'
	: 'rotulo-editorial__marco-shape';
$viewbox_titulo     = $es_inverso_titulo ? '-6 0 106 100' : '0 0 106 100';
$puntos_titulo      = $es_inverso_titulo ? '-6,2 93,2 99,98 0,98' : '7,2 106,2 100,98 1,98';
$style_titulo       = array(
	'--rotulo-color:' . $color_borde_titulo_imagen,
);
if ( $es_relleno_titulo ) {
	$style_titulo[] = '--rotulo-bg:' . $color_solido_titulo_imagen;
	$style_titulo[] = '--rotulo-text-color:' . ( $color_letra_titulo_imagen ?: '#fcfcf8' );
} else {
	$style_titulo[] = '--rotulo-text-color:' . ( $color_letra_titulo_imagen ?: $color_borde_titulo_imagen );
}

$rotulo_classes = array(
	'rotulo-editorial',
	'rotulo-editorial--' . $variante_titulo_imagen,
	'rotulo-editorial--tamano-' . $tamano_titulo_imagen,
	'rotulo-editorial--imagen-tipografia-' . $tipografia_titulo_imagen,
);
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>" style="--ti-img-scale: <?php echo esc_attr( $ti_img_scale_css ); ?>;">

	<?php if ( 'cenido' === $disposicion_bloque ) : ?>
		<?php if ( '' !== $imagen_url ) : ?>
			<div class="<?php echo esc_attr( implode( ' ', $clases_col_imagen ) ); ?>">
				<figure class="texto-imagen__figure">
					<a href="<?php echo esc_url( $imagen_url ); ?>" class="lightbox-trigger" data-caption="<?php echo esc_attr( $caption ); ?>">
						<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $imagen_alt ); ?>">
					</a>

					<?php if ( '' !== $titulo_editorial_imagen ) : ?>
						<figcaption class="imagen-meta imagen-meta--disposicion-<?php echo esc_attr( $disposicion_titulo_imagen ); ?> imagen-meta--alineacion-<?php echo esc_attr( $alineacion_titulo_imagen ); ?> imagen-meta--ancho-<?php echo esc_attr( $ancho_titulo_imagen ); ?>">
							<div class="<?php echo esc_attr( implode( ' ', $rotulo_classes ) ); ?>" style="<?php echo esc_attr( implode( '; ', $style_titulo ) ); ?>">
								<div class="rotulo-editorial__franja rotulo-editorial__franja--principal">
									<svg class="rotulo-editorial__marco" viewBox="<?php echo esc_attr( $viewbox_titulo ); ?>" preserveAspectRatio="none" aria-hidden="true" focusable="false">
										<polygon class="<?php echo esc_attr( $clase_marco_titulo ); ?>" points="<?php echo esc_attr( $puntos_titulo ); ?>"></polygon>
									</svg>
									<p class="rotulo-editorial__texto rotulo-editorial__texto--principal"><?php echo esc_html( $titulo_editorial_imagen ); ?></p>
								</div>
							</div>

							<?php if ( '' !== $caption ) : ?>
								<p class="imagen-meta__caption imagen-meta__caption--tamano-<?php echo esc_attr( $tamano_pie_imagen ); ?> imagen-meta__caption--tipografia-<?php echo esc_attr( $tipografia_pie_imagen ); ?>">
									<?php echo esc_html( $caption ); ?>
								</p>
							<?php endif; ?>
						</figcaption>
					<?php elseif ( '' !== $caption ) : ?>
						<figcaption class="imagen-meta__caption imagen-meta__caption--tamano-<?php echo esc_attr( $tamano_pie_imagen ); ?> imagen-meta__caption--tipografia-<?php echo esc_attr( $tipografia_pie_imagen ); ?>">
							<?php echo esc_html( $caption ); ?>
						</figcaption>
					<?php endif; ?>
				</figure>
			</div>
		<?php endif; ?>

		<?php if ( $contenido ) : ?>
			<div class="col texto">
				<?php echo wp_kses_post( $contenido ); ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<?php if ( $contenido ) : ?>
			<div class="col texto">
				<?php echo wp_kses_post( $contenido ); ?>
			</div>
		<?php endif; ?>

		<?php if ( '' !== $imagen_url ) : ?>
			<div class="<?php echo esc_attr( implode( ' ', $clases_col_imagen ) ); ?>">
				<figure class="texto-imagen__figure">
					<a href="<?php echo esc_url( $imagen_url ); ?>" class="lightbox-trigger" data-caption="<?php echo esc_attr( $caption ); ?>">
						<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $imagen_alt ); ?>">
					</a>

					<?php if ( '' !== $titulo_editorial_imagen ) : ?>
						<figcaption class="imagen-meta imagen-meta--disposicion-<?php echo esc_attr( $disposicion_titulo_imagen ); ?> imagen-meta--alineacion-<?php echo esc_attr( $alineacion_titulo_imagen ); ?> imagen-meta--ancho-<?php echo esc_attr( $ancho_titulo_imagen ); ?>">
							<div class="<?php echo esc_attr( implode( ' ', $rotulo_classes ) ); ?>" style="<?php echo esc_attr( implode( '; ', $style_titulo ) ); ?>">
								<div class="rotulo-editorial__franja rotulo-editorial__franja--principal">
									<svg class="rotulo-editorial__marco" viewBox="<?php echo esc_attr( $viewbox_titulo ); ?>" preserveAspectRatio="none" aria-hidden="true" focusable="false">
										<polygon class="<?php echo esc_attr( $clase_marco_titulo ); ?>" points="<?php echo esc_attr( $puntos_titulo ); ?>"></polygon>
									</svg>
									<p class="rotulo-editorial__texto rotulo-editorial__texto--principal"><?php echo esc_html( $titulo_editorial_imagen ); ?></p>
								</div>
							</div>

							<?php if ( '' !== $caption ) : ?>
								<p class="imagen-meta__caption imagen-meta__caption--tamano-<?php echo esc_attr( $tamano_pie_imagen ); ?> imagen-meta__caption--tipografia-<?php echo esc_attr( $tipografia_pie_imagen ); ?>">
									<?php echo esc_html( $caption ); ?>
								</p>
							<?php endif; ?>
						</figcaption>
					<?php elseif ( '' !== $caption ) : ?>
						<figcaption class="imagen-meta__caption imagen-meta__caption--tamano-<?php echo esc_attr( $tamano_pie_imagen ); ?> imagen-meta__caption--tipografia-<?php echo esc_attr( $tipografia_pie_imagen ); ?>">
							<?php echo esc_html( $caption ); ?>
						</figcaption>
					<?php endif; ?>
				</figure>
			</div>
		<?php endif; ?>
	<?php endif; ?>

</section>
