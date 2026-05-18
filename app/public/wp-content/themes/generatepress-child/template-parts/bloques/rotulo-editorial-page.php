<?php
$get_field = static function ( $name, $default = null ) use ( $args ) {
	if ( function_exists( 'fiflp_get_sub_field_compat' ) ) {
		return fiflp_get_sub_field_compat( $name, $args ?? array(), $default );
	}

	$value = get_sub_field( $name );
	return null !== $value ? $value : $default;
};

$normalize_color = static function ( $raw, $default = '' ) {
	if ( is_array( $raw ) ) {
		if ( isset( $raw['color'] ) ) {
			$raw = (string) $raw['color'];
		} elseif ( isset( $raw['value'] ) ) {
			$raw = (string) $raw['value'];
		} else {
			$raw = '';
		}
	}

	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return $default;
	}

	$hex = sanitize_hex_color( $raw );
	if ( $hex ) {
		return $hex;
	}

	if ( 1 === preg_match( '/^rgba?\\([0-9\\s.,%]+\\)$/i', $raw ) ) {
		return $raw;
	}

	return $default;
};

$supertitulo = trim( (string) $get_field( 'supertitulo', '' ) );
$titulo      = trim( (string) $get_field( 'titulo', '' ) );
$subtitulo   = trim( (string) $get_field( 'subtitulo', '' ) );
$tamano_subtitulo = strtolower( trim( (string) $get_field( 'tamano_subtitulo', '' ) ) );
$color_subtitulo = $normalize_color( $get_field( 'color_subtitulo', '' ), '' );
$color_texto = $normalize_color( $get_field( 'color_texto', '' ), '' );
$interlineado_subtitulo_raw = $get_field( 'interlineado_subtitulo', null );
$espaciado_letras_subtitulo_raw = $get_field( 'espaciado_letras_subtitulo', null );
$variante_supertitulo = trim( (string) $get_field( 'variante_supertitulo', '' ) );
$variante_titulo      = trim( (string) $get_field( 'variante_titulo', '' ) );
$ancho_subtitulo = strtolower( trim( (string) $get_field( 'ancho_subtitulo', '' ) ) );
$alineacion_subtitulo = strtolower( trim( (string) $get_field( 'alineacion_subtitulo', '' ) ) );
$etiqueta    = trim( (string) $get_field( 'etiqueta_html', '' ) );
$tamano      = trim( (string) $get_field( 'tamano', '' ) );
$alineacion_rotulo = strtolower( trim( (string) $get_field( 'alineacion_rotulo', '' ) ) );
$color_trazo = $normalize_color( $get_field( 'color_trazo', '' ), '' );
$color_fondo = $normalize_color( $get_field( 'color_fondo', '' ), '' );

$interlineado_raw     = $get_field( 'interlineado', null );
$espaciado_letras_raw = $get_field( 'espaciado_letras', null );
$titulo_lineas_raw = $get_field( 'titulo_lineas', null );

// Compatibilidad entre contextos de rótulo editorial:
// - Normal/onepage: `titulo_lineas`
if ( ! is_array( $titulo_lineas_raw ) || empty( $titulo_lineas_raw ) ) {
	$titulo_lineas_raw = $get_field( 'rotulo_titulo_lineas', array() );
}

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
$alineaciones_rotulo_validas = array(
	'left',
	'center',
	'right',
);

$tamanos_subtitulo_validos = array(
	's',
	'm',
	'l',
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

if ( ! in_array( $variante_supertitulo, $variantes_validas, true ) ) {
	$variante_supertitulo = 'linea';
}

if ( ! in_array( $variante_titulo, $variantes_validas, true ) ) {
	$variante_titulo = 'linea';
}

if ( in_array( $alineacion_subtitulo, array( 'izquierda', 'izq' ), true ) ) {
	$alineacion_subtitulo = 'left';
} elseif ( in_array( $alineacion_subtitulo, array( 'centro', 'centrado' ), true ) ) {
	$alineacion_subtitulo = 'center';
} elseif ( in_array( $alineacion_subtitulo, array( 'derecha', 'der' ), true ) ) {
	$alineacion_subtitulo = 'right';
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
if ( ! in_array( $alineacion_rotulo, $alineaciones_rotulo_validas, true ) ) {
	$alineacion_rotulo = 'left';
}
if ( ! in_array( $tamano_subtitulo, $tamanos_subtitulo_validos, true ) ) {
	$tamano_subtitulo = 'm';
}

if ( ! $color_trazo ) {
	$color_trazo = '#0f2d30';
}

if ( ! $color_fondo ) {
	$color_fondo = '#fcfcf8';
}

$interlineado = is_numeric( $interlineado_raw ) ? (float) $interlineado_raw : 0.86;
$interlineado = max( 0.6, min( 2, $interlineado ) );

$espaciado_letras = is_numeric( $espaciado_letras_raw ) ? (float) $espaciado_letras_raw : 0.01;
$espaciado_letras = max( -0.05, min( 0.2, $espaciado_letras ) );

$interlineado_subtitulo = is_numeric( $interlineado_subtitulo_raw ) ? (float) $interlineado_subtitulo_raw : 1.6;
$interlineado_subtitulo = max( 1.0, min( 2.2, $interlineado_subtitulo ) );

$espaciado_letras_subtitulo = is_numeric( $espaciado_letras_subtitulo_raw ) ? (float) $espaciado_letras_subtitulo_raw : 0;
$espaciado_letras_subtitulo = max( -0.05, min( 0.2, $espaciado_letras_subtitulo ) );

$supertitulo_length = function_exists( 'mb_strlen' ) ? mb_strlen( $supertitulo ) : strlen( $supertitulo );
$titulo_length      = function_exists( 'mb_strlen' ) ? mb_strlen( $titulo ) : strlen( $titulo );

$es_inverso          = in_array( $variante_titulo, array( 'linea_inversa', 'relleno_inverso' ), true );
$es_relleno          = in_array( $variante_titulo, array( 'relleno', 'relleno_inverso' ), true );
$es_inverso_superior = in_array( $variante_supertitulo, array( 'linea_inversa', 'relleno_inverso' ), true );
$es_relleno_superior = in_array( $variante_supertitulo, array( 'relleno', 'relleno_inverso' ), true );
$clases_rotulo       = array(
	'rotulo-editorial',
	'rotulo-editorial--' . $variante_titulo,
	'rotulo-editorial--superior-' . $variante_supertitulo,
	'rotulo-editorial--tamano-' . $tamano,
	'rotulo-editorial--align-' . $alineacion_rotulo,
	'rotulo-editorial--tamano-subtitulo-' . $tamano_subtitulo,
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

$puntos_superior     = $es_inverso_superior ? '2,2 88,2 98,98 12,98' : '12,2 98,2 88,98 2,98';
$viewbox_principal   = $es_inverso ? '-6 0 106 100' : '0 0 106 100';
$puntos_principal    = $es_inverso ? '-6,2 93,2 99,98 0,98' : '7,2 106,2 100,98 1,98';
$clase_marco_lower   = $es_relleno ? 'rotulo-editorial__marco-shape rotulo-editorial__marco-shape--relleno' : 'rotulo-editorial__marco-shape';
$clase_marco_upper   = $es_relleno_superior ? 'rotulo-editorial__marco-shape rotulo-editorial__marco-shape--relleno' : 'rotulo-editorial__marco-shape';
$style_rules         = array(
	'--rotulo-color:' . $color_trazo,
	'--rotulo-bg:' . $color_fondo,
	'--rotulo-line-height:' . rtrim( rtrim( number_format( $interlineado, 2, '.', '' ), '0' ), '.' ),
	'--rotulo-letter-spacing:' . rtrim( rtrim( number_format( $espaciado_letras, 3, '.', '' ), '0' ), '.' ) . 'em',
	'--rotulo-subtitulo-line-height:' . rtrim( rtrim( number_format( $interlineado_subtitulo, 2, '.', '' ), '0' ), '.' ),
	'--rotulo-subtitulo-letter-spacing:' . rtrim( rtrim( number_format( $espaciado_letras_subtitulo, 3, '.', '' ), '0' ), '.' ) . 'em',
);
if ( $color_subtitulo ) {
	$style_rules[] = '--rotulo-subtitulo-color:' . $color_subtitulo;
}
if ( $color_texto ) {
	$style_rules[] = '--rotulo-text-color:' . $color_texto;
}

$titulo_lineas = array();
$bloques_rotulo = array();

if ( is_array( $titulo_lineas_raw ) ) {
	foreach ( $titulo_lineas_raw as $linea_raw ) {
		if ( ! is_array( $linea_raw ) ) {
			continue;
		}

		$linea_texto     = isset( $linea_raw['texto'] ) ? trim( (string) $linea_raw['texto'] ) : '';
		$linea_tipografia = isset( $linea_raw['tipografia'] ) ? trim( (string) $linea_raw['tipografia'] ) : 'backslanted';
		$linea_variante  = isset( $linea_raw['variante'] ) ? trim( (string) $linea_raw['variante'] ) : 'linea';
		$row_titulo      = isset( $linea_raw['titulo'] ) ? trim( (string) $linea_raw['titulo'] ) : '';
		$row_supertitulo = isset( $linea_raw['supertitulo'] ) ? trim( (string) $linea_raw['supertitulo'] ) : '';
		$row_subtitulo   = isset( $linea_raw['subtitulo'] ) ? trim( (string) $linea_raw['subtitulo'] ) : '';

		if ( '' === $linea_texto && ( '' !== $row_titulo || '' !== $row_supertitulo || '' !== $row_subtitulo ) ) {
			$row_var_titulo = isset( $linea_raw['variante_titulo'] ) ? trim( (string) $linea_raw['variante_titulo'] ) : $variante_titulo;
			$row_var_super  = isset( $linea_raw['variante_supertitulo'] ) ? trim( (string) $linea_raw['variante_supertitulo'] ) : $variante_supertitulo;
			$row_tamano     = isset( $linea_raw['tamano'] ) ? strtolower( trim( (string) $linea_raw['tamano'] ) ) : $tamano;
			$row_ancho_sub  = isset( $linea_raw['ancho_subtitulo'] ) ? trim( (string) $linea_raw['ancho_subtitulo'] ) : $ancho_subtitulo;
			$row_align_sub  = isset( $linea_raw['alineacion_subtitulo'] ) ? trim( (string) $linea_raw['alineacion_subtitulo'] ) : $alineacion_subtitulo;
			$row_tam_sub    = isset( $linea_raw['tamano_subtitulo'] ) ? strtolower( trim( (string) $linea_raw['tamano_subtitulo'] ) ) : $tamano_subtitulo;
			$row_color_sub  = $normalize_color( $linea_raw['color_subtitulo'] ?? '', '' );
			$row_color_text = $normalize_color( $linea_raw['color_texto'] ?? '', '' );
			$row_inter_sub  = isset( $linea_raw['interlineado_subtitulo'] ) && is_numeric( $linea_raw['interlineado_subtitulo'] ) ? (float) $linea_raw['interlineado_subtitulo'] : $interlineado_subtitulo;
			$row_esp_sub    = isset( $linea_raw['espaciado_letras_subtitulo'] ) && is_numeric( $linea_raw['espaciado_letras_subtitulo'] ) ? (float) $linea_raw['espaciado_letras_subtitulo'] : $espaciado_letras_subtitulo;
			$row_color_trazo = $normalize_color( $linea_raw['color_trazo'] ?? '', '' );
			$row_color_fondo = $normalize_color( $linea_raw['color_fondo'] ?? '', '' );
			$row_align_rotulo = isset( $linea_raw['alineacion_rotulo'] ) ? strtolower( trim( (string) $linea_raw['alineacion_rotulo'] ) ) : $alineacion_rotulo;

			if ( ! in_array( $row_var_titulo, $variantes_validas, true ) ) {
				$row_var_titulo = $variante_titulo;
			}
			if ( ! in_array( $row_var_super, $variantes_validas, true ) ) {
				$row_var_super = $variante_supertitulo;
			}
			if ( ! in_array( $row_tamano, $tamanos_validos, true ) ) {
				$row_tamano = $tamano;
			}
			if ( ! in_array( $row_ancho_sub, $anchos_subtitulo_validos, true ) ) {
				$row_ancho_sub = $ancho_subtitulo;
			}
			if ( ! in_array( $row_align_sub, $alineaciones_subtitulo_validas, true ) ) {
				$row_align_sub = $alineacion_subtitulo;
			}
			if ( ! in_array( $row_tam_sub, $tamanos_subtitulo_validos, true ) ) {
				$row_tam_sub = $tamano_subtitulo;
			}
			if ( ! in_array( $row_align_rotulo, $alineaciones_rotulo_validas, true ) ) {
				$row_align_rotulo = $alineacion_rotulo;
			}
			$row_inter_sub = max( 1.0, min( 2.2, $row_inter_sub ) );
			$row_esp_sub   = max( -0.05, min( 0.2, $row_esp_sub ) );

			$bloques_rotulo[] = array(
				'titulo' => $row_titulo,
				'supertitulo' => $row_supertitulo,
				'subtitulo' => $row_subtitulo,
				'variante_titulo' => $row_var_titulo,
				'variante_supertitulo' => $row_var_super,
				'tamano' => $row_tamano,
				'ancho_subtitulo' => $row_ancho_sub,
				'alineacion_subtitulo' => $row_align_sub,
				'tamano_subtitulo' => $row_tam_sub,
				'color_subtitulo' => $row_color_sub,
				'color_texto' => $row_color_text,
				'interlineado_subtitulo' => $row_inter_sub,
				'espaciado_letras_subtitulo' => $row_esp_sub,
				'color_trazo' => $row_color_trazo ?: $color_trazo,
				'color_fondo' => $row_color_fondo ?: $color_fondo,
				'alineacion_rotulo' => $row_align_rotulo,
			);
			continue;
		}

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

// Legacy (solo `texto` + tipografía): promover al modelo Página (titulo + variantes).
if ( ! empty( $titulo_lineas ) && empty( $bloques_rotulo ) ) {
	$first_linea_legacy = reset( $titulo_lineas );

	if ( is_array( $first_linea_legacy ) && '' !== trim( (string) ( $first_linea_legacy['texto'] ?? '' ) ) ) {
		if ( '' === $titulo ) {
			$titulo = trim( (string) $first_linea_legacy['texto'] );
		}

		if ( 'linea' === $variante_titulo && ! empty( $first_linea_legacy['variante'] ) ) {
			$variante_linea_legacy = trim( (string) $first_linea_legacy['variante'] );

			if ( isset( $mapa_variante_linea[ $variante_linea_legacy ] ) ) {
				$variante_titulo = $mapa_variante_linea[ $variante_linea_legacy ];
			}
		}
	}

	$titulo_lineas = array();
}

$usar_modelo_lineas = false;
$usar_bloques_rotulo = ! empty( $bloques_rotulo );

if ( ! $usar_modelo_lineas && ! $usar_bloques_rotulo && '' === $titulo && '' === $supertitulo && '' === $subtitulo ) {
	return;
}

$get_rotulo_geometry = static function ( $variante_titulo_value, $variante_supertitulo_value ) {
	$variante_titulo_svg = $variante_titulo_value;
	$variante_super_svg  = $variante_supertitulo_value;

	$es_inverso_local       = in_array( $variante_titulo_svg, array( 'linea_inversa', 'relleno_inverso' ), true );
	$es_relleno_local       = in_array( $variante_titulo_value, array( 'relleno', 'relleno_inverso' ), true );
	$es_inverso_super_local = in_array( $variante_super_svg, array( 'linea_inversa', 'relleno_inverso' ), true );
	$es_relleno_super_local = in_array( $variante_supertitulo_value, array( 'relleno', 'relleno_inverso' ), true );

	// Solo Página: el título replica la inclinación del supertítulo en cada variante.
	$principal_linea_inversa = ( 'linea_inversa' === $variante_titulo_svg );
	$principal_linea         = ( 'linea' === $variante_titulo_svg );
	$principal_relleno       = ( 'relleno' === $variante_titulo_svg );
	$principal_relleno_inv   = ( 'relleno_inverso' === $variante_titulo_svg );

	return array(
		'es_inverso'            => $es_inverso_local,
		'es_relleno'            => $es_relleno_local,
		'es_inverso_superior'   => $es_inverso_super_local,
		'es_relleno_superior'   => $es_relleno_super_local,
		'puntos_superior'       => $es_inverso_super_local ? '2,2 88,2 98,98 12,98' : '12,2 98,2 88,98 2,98',
		'viewbox_principal'     => $principal_linea_inversa
			? '0 0 100 100'
			: ( $principal_linea
				? '0 0 100 100'
				: ( $principal_relleno || $principal_relleno_inv
					? '0 0 100 100'
					: ( $es_inverso_local ? '-6 0 106 100' : '0 0 106 100' ) ) ),
		'puntos_principal'      => $principal_linea_inversa
			? '2,2 88,2 98,98 12,98'
			: ( $principal_linea
				? '12,2 98,2 88,98 2,98'
				: ( $principal_relleno
					? '12,2 98,2 88,98 2,98'
					: ( $principal_relleno_inv
						? '2,2 88,2 98,98 12,98'
						: ( $es_inverso_local ? '-6,2 93,2 99,98 0,98' : '7,2 106,2 100,98 1,98' ) ) ) ),
		'clase_marco_lower'     => $es_relleno_local ? 'rotulo-editorial__marco-shape rotulo-editorial__marco-shape--relleno' : 'rotulo-editorial__marco-shape',
		'clase_marco_upper'     => $es_relleno_super_local ? 'rotulo-editorial__marco-shape rotulo-editorial__marco-shape--relleno' : 'rotulo-editorial__marco-shape',
	);
};

$render_rotulo_full = static function ( $config ) {
	$geo = $config['geometry'];
	?>
	<div class="rotulo-editorial__cabecera">
		<?php if ( '' !== $config['titulo'] ) : ?>
			<div class="rotulo-editorial__franja rotulo-editorial__franja--principal<?php echo $geo['es_inverso'] ? ' is-inversa' : ''; ?><?php echo $geo['es_relleno'] ? ' is-relleno' : ''; ?>">
				<svg class="rotulo-editorial__marco" viewBox="<?php echo esc_attr( $geo['viewbox_principal'] ); ?>" preserveAspectRatio="none" aria-hidden="true" focusable="false">
					<polygon class="<?php echo esc_attr( $geo['clase_marco_lower'] ); ?>" points="<?php echo esc_attr( $geo['puntos_principal'] ); ?>"></polygon>
				</svg>
				<<?php echo esc_attr( $config['etiqueta'] ); ?> class="rotulo-editorial__texto rotulo-editorial__texto--principal"><?php echo esc_html( $config['titulo'] ); ?></<?php echo esc_attr( $config['etiqueta'] ); ?>>
			</div>
		<?php endif; ?>
		<?php if ( '' !== $config['supertitulo'] ) : ?>
			<div class="rotulo-editorial__franja rotulo-editorial__franja--superior<?php echo $geo['es_inverso_superior'] ? ' is-inversa' : ''; ?><?php echo $geo['es_relleno_superior'] ? ' is-relleno' : ''; ?>">
				<svg class="rotulo-editorial__marco" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
					<polygon class="<?php echo esc_attr( $geo['clase_marco_upper'] ); ?>" points="<?php echo esc_attr( $geo['puntos_superior'] ); ?>"></polygon>
				</svg>
				<span class="rotulo-editorial__union"><span class="rotulo-editorial__texto rotulo-editorial__texto--superior"><?php echo esc_html( $config['supertitulo'] ); ?></span></span>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( '' !== $config['subtitulo'] ) : ?>
		<p class="rotulo-editorial__subtitulo"><?php echo esc_html( $config['subtitulo'] ); ?></p>
	<?php endif;
};
?>

<section class="bloque rotulo-editorial-bloque rotulo-editorial-bloque--page fade-in">
	<?php if ( $usar_bloques_rotulo ) : ?>
		<div class="rotulo-editorial-lineas" style="<?php echo esc_attr( implode( '; ', $style_rules ) ); ?>">
			<?php foreach ( $bloques_rotulo as $row ) : ?>
				<?php
				$row_geometry = $get_rotulo_geometry( $row['variante_titulo'], $row['variante_supertitulo'] );
				$row_style = array(
					'--rotulo-color:' . $row['color_trazo'],
					'--rotulo-bg:' . $row['color_fondo'],
					'--rotulo-subtitulo-line-height:' . rtrim( rtrim( number_format( $row['interlineado_subtitulo'], 2, '.', '' ), '0' ), '.' ),
					'--rotulo-subtitulo-letter-spacing:' . rtrim( rtrim( number_format( $row['espaciado_letras_subtitulo'], 3, '.', '' ), '0' ), '.' ) . 'em',
				);
				if ( $row['color_subtitulo'] ) {
					$row_style[] = '--rotulo-subtitulo-color:' . $row['color_subtitulo'];
				}
				if ( ! empty( $row['color_texto'] ) ) {
					$row_style[] = '--rotulo-text-color:' . $row['color_texto'];
				}
				$row_clases = array(
					'rotulo-editorial',
					'rotulo-editorial--context-page',
					'rotulo-editorial--' . $row['variante_titulo'],
					'rotulo-editorial--superior-' . $row['variante_supertitulo'],
					'rotulo-editorial--tamano-' . $row['tamano'],
					'rotulo-editorial--align-' . $row['alineacion_rotulo'],
					'rotulo-editorial--tamano-subtitulo-' . $row['tamano_subtitulo'],
					'rotulo-editorial--subtitulo-' . $row['ancho_subtitulo'],
					'rotulo-editorial--subtitulo-align-' . $row['alineacion_subtitulo'],
				);
				?>
				<div class="<?php echo esc_attr( implode( ' ', $row_clases ) ); ?>" style="<?php echo esc_attr( implode( '; ', $row_style ) ); ?>">
					<?php $render_rotulo_full( array(
						'titulo' => $row['titulo'],
						'supertitulo' => $row['supertitulo'],
						'subtitulo' => $row['subtitulo'],
						'etiqueta' => $etiqueta,
						'geometry' => $row_geometry,
					) ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<?php $base_geometry = $get_rotulo_geometry( $variante_titulo, $variante_supertitulo ); ?>
		<div class="<?php echo esc_attr( implode( ' ', array_merge( $clases_rotulo, array( 'rotulo-editorial--context-page' ) ) ) ); ?>" style="<?php echo esc_attr( implode( '; ', $style_rules ) ); ?>">
			<?php $render_rotulo_full( array(
				'titulo' => $titulo,
				'supertitulo' => $supertitulo,
				'subtitulo' => $subtitulo,
				'etiqueta' => $etiqueta,
				'geometry' => $base_geometry,
			) ); ?>
		</div>
	<?php endif; ?>
</section>
