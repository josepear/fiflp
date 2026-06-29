<?php
/**
 * BLOQUE IMAGEN
 * - Soporta modo normal / full
 * - Añade lightbox (clic en imagen)
 */

$get_field = static function ( $name, $default = null ) use ( $args ) {
	return fiflp_get_editorial_field( $name, fiflp_normalize_editorial_args( $args ), $default );
};

$imagen  = $get_field( 'imagen', null );
$caption = trim( (string) $get_field( 'caption', '' ) );
$full    = $get_field( 'full', false );
$full_page_imagen = (bool) $get_field( 'full_page_imagen', false );
$sin_redondeo = (bool) $get_field( 'sin_redondeo', false );
$imagen_multiplicar = (bool) $get_field( 'imagen_multiplicar', false );
$escala_visual_imagen = trim( (string) $get_field( 'escala_visual_imagen', '100' ) );
$alineacion_visual_imagen = trim( (string) $get_field( 'alineacion_visual_imagen', 'center' ) );
$ajuste_sombras_imagen = (float) $get_field( 'ajuste_sombras_imagen', 0 );
$ajuste_medios_imagen = (float) $get_field( 'ajuste_medios_imagen', 0 );
$ajuste_luces_imagen  = (float) $get_field( 'ajuste_luces_imagen', 0 );

$titulo_editorial_imagen    = trim( (string) $get_field( 'titulo_editorial_imagen', '' ) );
$variante_titulo_imagen     = trim( (string) $get_field( 'variante_titulo_imagen', '' ) );
$tipografia_titulo_imagen   = trim( (string) $get_field( 'tipografia_titulo_imagen', '' ) );
$ancho_titulo_imagen        = trim( (string) $get_field( 'ancho_titulo_imagen', '' ) );
$alineacion_titulo_imagen   = trim( (string) $get_field( 'alineacion_titulo_imagen', '' ) );
$disposicion_titulo_imagen  = trim( (string) $get_field( 'disposicion_titulo_imagen', '' ) );
$tamano_titulo_imagen       = trim( (string) $get_field( 'tamano_titulo_imagen', '' ) );
$tamano_pie_imagen          = trim( (string) $get_field( 'tamano_pie_imagen', '' ) );
$tipografia_pie_imagen      = trim( (string) $get_field( 'tipografia_pie_imagen', '' ) );
$color_caption_imagen       = sanitize_hex_color( (string) $get_field( 'color_caption_imagen', '' ) );
$color_titulo_imagen        = sanitize_hex_color( (string) $get_field( 'color_titulo_imagen', '' ) );
$color_borde_titulo_imagen  = sanitize_hex_color( (string) $get_field( 'color_borde_titulo_imagen', '' ) );
$color_solido_titulo_imagen = sanitize_hex_color( (string) $get_field( 'color_solido_titulo_imagen', '' ) );
$color_letra_titulo_imagen  = sanitize_hex_color( (string) $get_field( 'color_letra_titulo_imagen', '' ) );

if ( ! $imagen ) {
	return;
}

$imagen_url = is_array( $imagen ) ? ( $imagen['url'] ?? '' ) : $imagen;
$imagen_alt = is_array( $imagen ) ? ( $imagen['alt'] ?? '' ) : '';

if ( ! $imagen_url ) {
	return;
}

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

if ( ! $color_caption_imagen ) {
	$color_caption_imagen = '';
}

if ( ! in_array( $escala_visual_imagen, array( '100', '75', '50', '33' ), true ) ) {
	$escala_visual_imagen = '100';
}

if ( ! in_array( $alineacion_visual_imagen, array( 'left', 'center', 'right' ), true ) ) {
	$alineacion_visual_imagen = 'center';
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

$clases = array( 'bloque', 'imagen', 'fade-in' );

if ( $full ) {
	$clases[] = 'imagen-full';
}


if ( $sin_redondeo ) {
	$clases[] = 'imagen--sin-redondeo';
}

if ( $imagen_multiplicar ) {
	$clases[] = 'imagen--multiply';
}

$clases[] = 'imagen--escala-' . $escala_visual_imagen;
$clases[] = 'imagen--alineacion-' . $alineacion_visual_imagen;

if ( 0.0 !== $ajuste_sombras_imagen || 0.0 !== $ajuste_medios_imagen || 0.0 !== $ajuste_luces_imagen ) {
	$clases[] = 'imagen--ajustes-tonales';
}

$ajuste_sombras_imagen = max( -100, min( 100, $ajuste_sombras_imagen ) );
$ajuste_medios_imagen  = max( -100, min( 100, $ajuste_medios_imagen ) );
$ajuste_luces_imagen   = max( -100, min( 100, $ajuste_luces_imagen ) );

$image_filter_vars = sprintf(
	'--img-ajuste-sombras:%s; --img-ajuste-medios:%s; --img-ajuste-luces:%s;',
	rtrim( rtrim( number_format( $ajuste_sombras_imagen, 2, '.', '' ), '0' ), '.' ),
	rtrim( rtrim( number_format( $ajuste_medios_imagen, 2, '.', '' ), '0' ), '.' ),
	rtrim( rtrim( number_format( $ajuste_luces_imagen, 2, '.', '' ), '0' ), '.' )
);

$figure_style = $image_filter_vars;
if ( '' !== $color_caption_imagen ) {
	$figure_style .= '--imagen-caption-color:' . $color_caption_imagen . ';';
}

$lightbox_classes = array( 'lightbox-trigger' );
$image_classes    = array();

if ( $imagen_multiplicar ) {
	$lightbox_classes[] = 'is-multiply';
	$image_classes[]    = 'is-multiply';
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
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">

	<figure class="<?php echo $full_page_imagen ? 'imagen-full-page-figure' : ''; ?>" style="<?php echo esc_attr( $figure_style ); ?>">

		<a href="<?php echo esc_url( $imagen_url ); ?>" class="<?php echo esc_attr( implode( ' ', $lightbox_classes ) ); ?>" data-caption="<?php echo esc_attr( $caption ?? '' ); ?>">
			<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $imagen_alt ); ?>"<?php echo $image_classes ? ' class="' . esc_attr( implode( ' ', $image_classes ) ) . '"' : ''; ?>>
		</a>

		<?php if ( '' !== $titulo_editorial_imagen ) : ?>
			<figcaption class="imagen-meta imagen-meta--disposicion-<?php echo esc_attr( $disposicion_titulo_imagen ); ?> imagen-meta--alineacion-<?php echo esc_attr( $alineacion_titulo_imagen ); ?> imagen-meta--ancho-<?php echo esc_attr( $ancho_titulo_imagen ); ?>">
				<div class="rotulo-editorial rotulo-editorial--<?php echo esc_attr( $variante_titulo_imagen ); ?> rotulo-editorial--tamano-<?php echo esc_attr( $tamano_titulo_imagen ); ?>" style="<?php echo esc_attr( implode( '; ', $style_titulo ) ); ?>">
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

</section>
