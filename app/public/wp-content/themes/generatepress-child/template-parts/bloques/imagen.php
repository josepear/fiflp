<?php
/**
 * BLOQUE IMAGEN
 * - Soporta modo normal / full
 * - Añade lightbox (clic en imagen)
 */

$get_field = static function ( $name, $default = null ) use ( $args ) {
	if ( function_exists( 'fiflp_get_sub_field_compat' ) ) {
		return fiflp_get_sub_field_compat( $name, $args ?? array(), $default );
	}

	$value = get_sub_field( $name );
	return null !== $value ? $value : $default;
};

$imagen  = $get_field( 'imagen', null );
$caption = trim( (string) $get_field( 'caption', '' ) );
$full    = $get_field( 'full', false );

$titulo_editorial_imagen    = trim( (string) $get_field( 'titulo_editorial_imagen', '' ) );
$variante_titulo_imagen     = trim( (string) $get_field( 'variante_titulo_imagen', '' ) );
$tipografia_titulo_imagen   = trim( (string) $get_field( 'tipografia_titulo_imagen', '' ) );
$ancho_titulo_imagen        = trim( (string) $get_field( 'ancho_titulo_imagen', '' ) );
$alineacion_titulo_imagen   = trim( (string) $get_field( 'alineacion_titulo_imagen', '' ) );
$disposicion_titulo_imagen  = trim( (string) $get_field( 'disposicion_titulo_imagen', '' ) );
$tamano_titulo_imagen       = trim( (string) $get_field( 'tamano_titulo_imagen', '' ) );
$tamano_pie_imagen          = trim( (string) $get_field( 'tamano_pie_imagen', '' ) );
$tipografia_pie_imagen      = trim( (string) $get_field( 'tipografia_pie_imagen', '' ) );
$color_titulo_imagen        = sanitize_hex_color( (string) $get_field( 'color_titulo_imagen', '' ) );

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

if ( ! $color_titulo_imagen ) {
	$color_titulo_imagen = '#0f2d30';
}

$clases = array( 'bloque', 'imagen', 'fade-in' );

if ( $full ) {
	$clases[] = 'imagen-full';
}

$es_inverso_titulo  = in_array( $variante_titulo_imagen, array( 'linea_inversa', 'relleno_inverso' ), true );
$es_relleno_titulo  = in_array( $variante_titulo_imagen, array( 'relleno', 'relleno_inverso' ), true );
$clase_marco_titulo = $es_relleno_titulo
	? 'rotulo-editorial__marco-shape rotulo-editorial__marco-shape--relleno'
	: 'rotulo-editorial__marco-shape';
$viewbox_titulo     = $es_inverso_titulo ? '0 0 106 100' : '-6 0 106 100';
$puntos_titulo      = $es_inverso_titulo ? '7,2 106,2 100,98 1,98' : '-6,2 93,2 99,98 0,98';
$style_titulo       = array(
	'--rotulo-color:' . $color_titulo_imagen,
);
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">

	<figure>

		<a href="<?php echo esc_url( $imagen_url ); ?>" class="lightbox-trigger" data-caption="<?php echo esc_attr( $caption ?? '' ); ?>">
			<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $imagen_alt ); ?>">
		</a>

		<?php if ( '' !== $titulo_editorial_imagen ) : ?>
			<figcaption class="imagen-meta imagen-meta--disposicion-<?php echo esc_attr( $disposicion_titulo_imagen ); ?> imagen-meta--alineacion-<?php echo esc_attr( $alineacion_titulo_imagen ); ?> imagen-meta--ancho-<?php echo esc_attr( $ancho_titulo_imagen ); ?>">
				<div class="rotulo-editorial rotulo-editorial--<?php echo esc_attr( $variante_titulo_imagen ); ?> rotulo-editorial--tamano-<?php echo esc_attr( $tamano_titulo_imagen ); ?> rotulo-editorial--imagen-tipografia-<?php echo esc_attr( $tipografia_titulo_imagen ); ?>" style="<?php echo esc_attr( implode( '; ', $style_titulo ) ); ?>">
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
