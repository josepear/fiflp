<?php
/**
 * Textos en columnas (infografía / cifras).
 * Bloque editorial, onepage o hito de cronología (args explícitos).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = $args ?? array();

$get_field = static function ( $name, $default = null ) use ( $args ) {
	if ( function_exists( 'fiflp_get_sub_field_compat' ) ) {
		return fiflp_get_sub_field_compat( $name, $args, $default );
	}
	if ( function_exists( 'get_sub_field' ) ) {
		$v = get_sub_field( $name );
		return null !== $v ? $v : $default;
	}
	return $default;
};

$pass_through = isset( $args['textos_columnas_bypass'] ) && is_array( $args['textos_columnas_bypass'] );

if ( $pass_through ) {
	$b = $args['textos_columnas_bypass'];
	$intro                = isset( $b['intro'] ) ? (string) $b['intro'] : '';
	$columnas_raw         = isset( $b['columnas'] ) && is_array( $b['columnas'] ) ? $b['columnas'] : array();
	$tipografia_titulo    = isset( $b['tipografia_titulo'] ) ? (string) $b['tipografia_titulo'] : 'upright';
	$tamano_titulo        = isset( $b['tamano_titulo'] ) ? (float) $b['tamano_titulo'] : 40.0;
	$color_titulo         = isset( $b['color_titulo'] ) ? (string) $b['color_titulo'] : '#111111';
	$interlineado_titulo  = isset( $b['interlineado_titulo'] ) ? (float) $b['interlineado_titulo'] : 1.05;
	$interletraje_titulo  = isset( $b['interletraje_titulo'] ) ? trim( (string) $b['interletraje_titulo'] ) : '0';
	$tipografia_cuerpo    = isset( $b['tipografia_cuerpo'] ) ? (string) $b['tipografia_cuerpo'] : 'body';
	$tamano_cuerpo        = isset( $b['tamano_cuerpo'] ) ? (float) $b['tamano_cuerpo'] : 15.0;
	$color_cuerpo         = isset( $b['color_cuerpo'] ) ? (string) $b['color_cuerpo'] : '#111111';
	$interlineado_cuerpo  = isset( $b['interlineado_cuerpo'] ) ? (float) $b['interlineado_cuerpo'] : 1.45;
	$interletraje_cuerpo  = isset( $b['interletraje_cuerpo'] ) ? trim( (string) $b['interletraje_cuerpo'] ) : '0';
	$context              = isset( $args['context'] ) ? trim( (string) $args['context'] ) : '';
} else {
	$intro               = (string) $get_field( 'textos_col_intro', '' );
	$columnas_raw        = $get_field( 'textos_col_columnas', array() );
	$tipografia_titulo   = trim( (string) $get_field( 'textos_col_tipografia_titulo', 'upright' ) );
	$tamano_titulo       = (float) $get_field( 'textos_col_tamano_titulo', 40 );
	$color_titulo        = (string) $get_field( 'textos_col_color_titulo', '#111111' );
	$interlineado_titulo = (float) $get_field( 'textos_col_interlineado_titulo', 1.05 );
	$interletraje_titulo = trim( (string) $get_field( 'textos_col_interletraje_titulo', '0' ) );
	$tipografia_cuerpo   = trim( (string) $get_field( 'textos_col_tipografia_cuerpo', 'body' ) );
	$tamano_cuerpo       = (float) $get_field( 'textos_col_tamano_cuerpo', 15 );
	$color_cuerpo        = (string) $get_field( 'textos_col_color_cuerpo', '#111111' );
	$interlineado_cuerpo = (float) $get_field( 'textos_col_interlineado_cuerpo', 1.45 );
	$interletraje_cuerpo = trim( (string) $get_field( 'textos_col_interletraje_cuerpo', '0' ) );
	$context             = '';
}

if ( ! is_array( $columnas_raw ) ) {
	$columnas_raw = array();
}

$font_var = static function ( $key ) {
	$map = array(
		'upright'      => 'var(--fiflp-font-display)',
		'slanted'      => 'var(--fiflp-font-display-slanted)',
		'backslanted'  => 'var(--fiflp-font-display-backslanted)',
		'body'         => 'var(--fiflp-font-body)',
	);
	return $map[ $key ] ?? $map['body'];
};

if ( ! in_array( $tipografia_titulo, array( 'upright', 'slanted', 'backslanted', 'body' ), true ) ) {
	$tipografia_titulo = 'upright';
}
if ( ! in_array( $tipografia_cuerpo, array( 'upright', 'slanted', 'backslanted', 'body' ), true ) ) {
	$tipografia_cuerpo = 'body';
}

$tamano_titulo       = max( 10, min( 120, $tamano_titulo ) );
$tamano_cuerpo       = max( 10, min( 48, $tamano_cuerpo ) );
$interlineado_titulo = max( 0.8, min( 2.5, $interlineado_titulo ) );
$interlineado_cuerpo = max( 0.8, min( 2.5, $interlineado_cuerpo ) );

$color_titulo = sanitize_hex_color( $color_titulo );
if ( '' === $color_titulo ) {
	$color_titulo = '#111111';
}
$color_cuerpo = sanitize_hex_color( $color_cuerpo );
if ( '' === $color_cuerpo ) {
	$color_cuerpo = '#111111';
}

$sanitize_ls = static function ( $raw ) {
	$s = trim( (string) $raw );
	if ( '' === $s || 'normal' === strtolower( $s ) ) {
		return 'normal';
	}
	if ( preg_match( '/^-?[0-9]*\.?[0-9]+(em|rem|ex|ch|px)?$/', $s ) ) {
		return $s;
	}
	if ( preg_match( '/^-?[0-9]+$/', $s ) ) {
		return $s . 'px';
	}
	return 'normal';
};

$interletraje_titulo = $sanitize_ls( $interletraje_titulo );
$interletraje_cuerpo = $sanitize_ls( $interletraje_cuerpo );

$columnas = array();
foreach ( $columnas_raw as $col ) {
	if ( ! is_array( $col ) ) {
		continue;
	}
	$filas_raw = isset( $col['textos_col_filas'] ) && is_array( $col['textos_col_filas'] ) ? $col['textos_col_filas'] : array();
	$filas     = array();
	foreach ( $filas_raw as $fila ) {
		if ( ! is_array( $fila ) ) {
			continue;
		}
		$valor = trim( (string) ( $fila['textos_col_valor'] ?? '' ) );
		$desc  = trim( (string) ( $fila['textos_col_desc'] ?? '' ) );
		if ( '' === $valor && '' === $desc ) {
			continue;
		}
		$filas[] = array(
			'valor' => $valor,
			'desc'  => $desc,
		);
	}
	if ( ! empty( $filas ) ) {
		$columnas[] = $filas;
	}
}

$n_cols = count( $columnas );
if ( $n_cols < 2 ) {
	return;
}

$n_cols = min( 4, $n_cols );

$intro_trim = trim( wp_strip_all_tags( $intro, true ) );
$has_intro  = '' !== $intro_trim;

$is_cronologia = ( 'cronologia' === $context );
$tag           = $is_cronologia ? 'div' : 'section';

$clases = array(
	'textos-columnas',
	'textos-columnas--cols-' . $n_cols,
	'fade-in',
);
if ( ! $is_cronologia ) {
	$clases[] = 'bloque';
}
if ( $is_cronologia ) {
	$clases[] = 'cronologia-editorial__textos-columnas';
}
if ( $has_intro ) {
	$clases[] = 'textos-columnas--has-intro';
}
if ( ! empty( $args['onepage'] ) ) {
	$clases[] = 'textos-columnas--onepage';
}

$style_vars = array(
	'--tc-titulo-ff:' . $font_var( $tipografia_titulo ),
	'--tc-titulo-fs:' . $tamano_titulo . 'px',
	'--tc-titulo-c:' . $color_titulo,
	'--tc-titulo-lh:' . $interlineado_titulo,
	'--tc-titulo-ls:' . $interletraje_titulo,
	'--tc-cuerpo-ff:' . $font_var( $tipografia_cuerpo ),
	'--tc-cuerpo-fs:' . $tamano_cuerpo . 'px',
	'--tc-cuerpo-c:' . $color_cuerpo,
	'--tc-cuerpo-lh:' . $interlineado_cuerpo,
	'--tc-cuerpo-ls:' . $interletraje_cuerpo,
	'--tc-n:' . (int) $n_cols,
);
?>

<<?php echo esc_attr( $tag ); ?> class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>" style="<?php echo esc_attr( implode( ';', $style_vars ) ); ?>">
	<div class="textos-columnas__inner">
		<?php if ( $has_intro ) : ?>
			<div class="textos-columnas__intro">
				<?php echo wp_kses_post( nl2br( esc_html( $intro_trim ) ) ); ?>
			</div>
		<?php endif; ?>

		<div class="textos-columnas__grid" role="list">
			<?php foreach ( $columnas as $ci => $filas ) : ?>
				<div class="textos-columnas__col" role="listitem">
					<?php foreach ( $filas as $fila ) : ?>
						<div class="textos-columnas__fila">
							<?php if ( '' !== $fila['valor'] ) : ?>
								<p class="textos-columnas__valor"><?php echo esc_html( $fila['valor'] ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== $fila['desc'] ) : ?>
								<p class="textos-columnas__desc"><?php echo esc_html( $fila['desc'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</<?php echo esc_attr( $tag ); ?>>
