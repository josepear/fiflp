<?php
$supertitulo = trim( (string) get_sub_field( 'supertitulo' ) );
$titulo      = trim( (string) get_sub_field( 'titulo' ) );
$subtitulo   = trim( (string) get_sub_field( 'subtitulo' ) );
$ancho_subtitulo = trim( (string) get_sub_field( 'ancho_subtitulo' ) );
$alineacion_subtitulo = trim( (string) get_sub_field( 'alineacion_subtitulo' ) );
$variante    = trim( (string) get_sub_field( 'variante' ) );
$etiqueta    = trim( (string) get_sub_field( 'etiqueta_html' ) );
$tamano      = trim( (string) get_sub_field( 'tamano' ) );
$color_trazo = sanitize_hex_color( (string) get_sub_field( 'color_trazo' ) );
$color_fondo = sanitize_hex_color( (string) get_sub_field( 'color_fondo' ) );

$interlineado_raw     = get_sub_field( 'interlineado' );
$espaciado_letras_raw = get_sub_field( 'espaciado_letras' );
$titulo_lineas_raw    = get_sub_field( 'titulo_lineas' );

$variantes_validas = array(
	'linea',
	'linea_inversa',
	'relleno',
	'relleno_inverso',
);

$etiquetas_validas = array(
	'h1',
	'h2',
	'h3',
	'h4',
	'h5',
	'h6',
);

$tamanos_validos = array(
	's',
	'm',
	'l',
	'xl',
);

$anchos_subtitulo_validos = array(
	'igual_rotulo',
	'estrecho',
	'ancho',
);

$alineaciones_subtitulo_validas = array(
	'left',
	'center',
	'right',
);

$tipografias_linea_validas = array(
	'slanted',
	'backslanted',
);

$variantes_linea_validas = array(
	'linea',
	'relleno',
	'linea_in',
	'relleno_in',
);

$mapa_variante_linea = array(
	'linea'      => 'linea',
	'relleno'    => 'relleno',
	'linea_in'   => 'linea_inversa',
	'relleno_in' => 'relleno_inverso',
);

if ( ! in_array( $variante, $variantes_validas, true ) ) {
	$variante = 'linea';
}

if ( ! in_array( $etiqueta, $etiquetas_validas, true ) ) {
	$etiqueta = 'h2';
}

if ( ! in_array( $tamano, $tamanos_validos, true ) ) {
	$tamano = 'm';
}

if ( ! in_array( $ancho_subtitulo, $anchos_subtitulo_validos, true ) ) {
	$ancho_subtitulo = 'igual_rotulo';
}

if ( ! in_array( $alineacion_subtitulo, $alineaciones_subtitulo_validas, true ) ) {
	$alineacion_subtitulo = 'left';
}

if ( ! $color_trazo ) {
	$color_trazo = '#0f2d30';
}

if ( ! $color_fondo ) {
	$color_fondo = '#fcfcf8';
}

$interlineado = is_numeric( $interlineado_raw ) ? (float) $interlineado_raw : 0.86;
$interlineado = max( 0.6, min( 1.4, $interlineado ) );

$espaciado_letras = is_numeric( $espaciado_letras_raw ) ? (float) $espaciado_letras_raw : 0.01;
$espaciado_letras = max( -0.05, min( 0.2, $espaciado_letras ) );

$supertitulo_length = function_exists( 'mb_strlen' ) ? mb_strlen( $supertitulo ) : strlen( $supertitulo );
$titulo_length      = function_exists( 'mb_strlen' ) ? mb_strlen( $titulo ) : strlen( $titulo );

$es_inverso          = in_array( $variante, array( 'linea_inversa', 'relleno_inverso' ), true );
$es_relleno          = in_array( $variante, array( 'relleno', 'relleno_inverso' ), true );
$clases_rotulo       = array(
	'rotulo-editorial',
	'rotulo-editorial--' . $variante,
	'rotulo-editorial--tamano-' . $tamano,
	'rotulo-editorial--subtitulo-' . $ancho_subtitulo,
	'rotulo-editorial--subtitulo-align-' . $alineacion_subtitulo,
);

if ( $supertitulo_length >= 20 ) {
	$clases_rotulo[] = 'rotulo-editorial--superior-largo';
}

if ( $titulo_length >= 26 ) {
	$clases_rotulo[] = 'rotulo-editorial--principal-largo';
} elseif ( $titulo_length > 0 && $titulo_length <= 18 ) {
	$clases_rotulo[] = 'rotulo-editorial--principal-corto';
}

$puntos_superior     = $es_inverso ? '2,2 88,2 98,98 12,98' : '12,2 98,2 88,98 2,98';
$viewbox_principal   = $es_inverso ? '0 0 106 100' : '-6 0 106 100';
$puntos_principal    = $es_inverso ? '7,2 106,2 100,98 1,98' : '-6,2 93,2 99,98 0,98';
$clase_marco_lower   = $es_relleno ? 'rotulo-editorial__marco-shape rotulo-editorial__marco-shape--relleno' : 'rotulo-editorial__marco-shape';
$clase_marco_upper   = $es_relleno ? 'rotulo-editorial__marco-shape rotulo-editorial__marco-shape--relleno' : 'rotulo-editorial__marco-shape';
$style_rules         = array(
	'--rotulo-color:' . $color_trazo,
	'--rotulo-bg:' . $color_fondo,
	'--rotulo-line-height:' . rtrim( rtrim( number_format( $interlineado, 2, '.', '' ), '0' ), '.' ),
	'--rotulo-letter-spacing:' . rtrim( rtrim( number_format( $espaciado_letras, 3, '.', '' ), '0' ), '.' ) . 'em',
);

$titulo_lineas = array();

if ( is_array( $titulo_lineas_raw ) ) {
	foreach ( $titulo_lineas_raw as $linea_raw ) {
		if ( ! is_array( $linea_raw ) ) {
			continue;
		}

		$linea_texto     = isset( $linea_raw['texto'] ) ? trim( (string) $linea_raw['texto'] ) : '';
		$linea_tipografia = isset( $linea_raw['tipografia'] ) ? trim( (string) $linea_raw['tipografia'] ) : 'backslanted';
		$linea_variante  = isset( $linea_raw['variante'] ) ? trim( (string) $linea_raw['variante'] ) : 'linea';

		if ( '' === $linea_texto ) {
			continue;
		}

		if ( ! in_array( $linea_tipografia, $tipografias_linea_validas, true ) ) {
			$linea_tipografia = 'backslanted';
		}

		if ( ! in_array( $linea_variante, $variantes_linea_validas, true ) ) {
			$linea_variante = 'linea';
		}

		$titulo_lineas[] = array(
			'texto'      => $linea_texto,
			'tipografia' => $linea_tipografia,
			'variante'   => $linea_variante,
		);

		if ( count( $titulo_lineas ) >= 3 ) {
			break;
		}
	}
}

$usar_modelo_lineas = ! empty( $titulo_lineas );

if ( ! $usar_modelo_lineas && '' === $titulo && '' === $supertitulo && '' === $subtitulo ) {
	return;
}
?>

<section class="bloque rotulo-editorial-bloque fade-in">
	<?php if ( $usar_modelo_lineas ) : ?>
		<div class="rotulo-editorial-lineas" style="<?php echo esc_attr( implode( '; ', $style_rules ) ); ?>">
			<?php foreach ( $titulo_lineas as $linea ) : ?>
				<?php
				$variante_render = $mapa_variante_linea[ $linea['variante'] ];
				$linea_es_inverso = in_array( $variante_render, array( 'linea_inversa', 'relleno_inverso' ), true );
				$linea_es_relleno = in_array( $variante_render, array( 'relleno', 'relleno_inverso' ), true );
				$linea_viewbox_principal = $linea_es_inverso ? '0 0 106 100' : '-6 0 106 100';
				$linea_puntos_principal  = $linea_es_inverso ? '7,2 106,2 100,98 1,98' : '-6,2 93,2 99,98 0,98';
				$linea_clase_marco       = $linea_es_relleno ? 'rotulo-editorial__marco-shape rotulo-editorial__marco-shape--relleno' : 'rotulo-editorial__marco-shape';
				$linea_clases            = array(
					'rotulo-editorial',
					'rotulo-editorial--' . $variante_render,
					'rotulo-editorial--tamano-' . $tamano,
					'rotulo-editorial--line-tipografia-' . $linea['tipografia'],
				);
				?>
				<div class="<?php echo esc_attr( implode( ' ', $linea_clases ) ); ?>">
					<div class="rotulo-editorial__franja rotulo-editorial__franja--principal">
						<svg class="rotulo-editorial__marco" viewBox="<?php echo esc_attr( $linea_viewbox_principal ); ?>" preserveAspectRatio="none" aria-hidden="true" focusable="false">
							<polygon class="<?php echo esc_attr( $linea_clase_marco ); ?>" points="<?php echo esc_attr( $linea_puntos_principal ); ?>"></polygon>
						</svg>
						<p class="rotulo-editorial__texto rotulo-editorial__texto--principal"><?php echo esc_html( $linea['texto'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="<?php echo esc_attr( implode( ' ', $clases_rotulo ) ); ?>" style="<?php echo esc_attr( implode( '; ', $style_rules ) ); ?>">
			<?php if ( '' !== $supertitulo ) : ?>
				<div class="rotulo-editorial__franja rotulo-editorial__franja--superior">
					<svg class="rotulo-editorial__marco" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
						<polygon class="<?php echo esc_attr( $clase_marco_upper ); ?>" points="<?php echo esc_attr( $puntos_superior ); ?>"></polygon>
					</svg>
					<span class="rotulo-editorial__union">
						<span class="rotulo-editorial__texto rotulo-editorial__texto--superior"><?php echo esc_html( $supertitulo ); ?></span>
					</span>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $titulo ) : ?>
				<div class="rotulo-editorial__franja rotulo-editorial__franja--principal">
					<svg class="rotulo-editorial__marco" viewBox="<?php echo esc_attr( $viewbox_principal ); ?>" preserveAspectRatio="none" aria-hidden="true" focusable="false">
						<polygon class="<?php echo esc_attr( $clase_marco_lower ); ?>" points="<?php echo esc_attr( $puntos_principal ); ?>"></polygon>
					</svg>
					<<?php echo esc_attr( $etiqueta ); ?> class="rotulo-editorial__texto rotulo-editorial__texto--principal"><?php echo esc_html( $titulo ); ?></<?php echo esc_attr( $etiqueta ); ?>>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $subtitulo ) : ?>
				<p class="rotulo-editorial__subtitulo"><?php echo esc_html( $subtitulo ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>
