<?php
/**
 * Marcado único del cuadro editorial (CPT fiflp_cuadro).
 * Invocado desde fiflp_render_cuadro(); admite context editorial | onepage | cronologia.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = $args ?? array();

$cuadro_id = isset( $args['cuadro_id'] ) ? (int) $args['cuadro_id'] : 0;

if ( $cuadro_id <= 0 || ! function_exists( 'get_fields' ) ) {
	return;
}

$context = isset( $args['context'] ) ? (string) $args['context'] : '';

if ( '' === $context ) {
	$context = ! empty( $args['onepage'] ) ? 'onepage' : 'editorial';
}

$fields = get_fields( $cuadro_id );

if ( ! is_array( $fields ) ) {
	$fields = array();
}

$mensaje = isset( $fields['mensaje_superior'] ) ? trim( (string) $fields['mensaje_superior'] ) : '';
$intro   = isset( $fields['intro'] ) ? trim( (string) $fields['intro'] ) : '';

$nc_raw = isset( $fields['num_columnas'] ) ? (string) $fields['num_columnas'] : '2';
$nc     = in_array( $nc_raw, array( '2', '3', '4' ), true ) ? $nc_raw : '2';
$n_cols = (int) $nc;

$filas_raw = isset( $fields['filas'] ) && is_array( $fields['filas'] ) ? $fields['filas'] : array();

$tipografia = isset( $fields['tipografia_cifras'] ) ? (string) $fields['tipografia_cifras'] : 'editorial';
if ( ! in_array( $tipografia, array( 'editorial', 'manrope' ), true ) ) {
	$tipografia = 'editorial';
}

list( $cmin, $cmax ) = fiflp_cuadro_normalize_px_pair(
	$fields['cifra_min_px'] ?? 28,
	$fields['cifra_max_px'] ?? 72,
	28,
	72
);
list( $tmin, $tmax ) = fiflp_cuadro_normalize_px_pair(
	$fields['texto_min_px'] ?? 14,
	$fields['texto_max_px'] ?? 20,
	14,
	20
);

$cifra_clamp = fiflp_cuadro_clamp_font_size( $cmin, $cmax, 4.0 );
$texto_clamp = fiflp_cuadro_clamp_font_size( $tmin, $tmax, 2.75 );

$color_cifra = isset( $fields['color_cifra'] ) ? sanitize_hex_color( (string) $fields['color_cifra'] ) : '';
$color_texto = isset( $fields['color_texto'] ) ? sanitize_hex_color( (string) $fields['color_texto'] ) : '';

if ( '' === $color_cifra ) {
	$color_cifra = '#1e1e1e';
}
if ( '' === $color_texto ) {
	$color_texto = '#1e1e1e';
}

$filas_out = array();

foreach ( $filas_raw as $fila ) {
	if ( ! is_array( $fila ) ) {
		continue;
	}

	$cells = array();

	for ( $c = 1; $c <= $n_cols; $c++ ) {
		$ck = 'cifra_' . $c;
		$tk = 'texto_' . $c;
		$cifra = isset( $fila[ $ck ] ) ? trim( (string) $fila[ $ck ] ) : '';
		$texto = isset( $fila[ $tk ] ) ? trim( (string) $fila[ $tk ] ) : '';
		$cells[] = array(
			'cifra' => $cifra,
			'texto' => $texto,
		);
	}

	$row_has = false;
	foreach ( $cells as $cell ) {
		if ( '' !== $cell['cifra'] || '' !== $cell['texto'] ) {
			$row_has = true;
			break;
		}
	}

	if ( $row_has ) {
		$filas_out[] = $cells;
	}
}

$has_intro   = '' !== trim( wp_strip_all_tags( $intro ) );
$has_mensaje = '' !== $mensaje;
$has_grid    = ! empty( $filas_out );

if ( ! $has_intro && ! $has_mensaje && ! $has_grid ) {
	return;
}

$is_cronologia = ( 'cronologia' === $context );
$tag           = $is_cronologia ? 'div' : 'section';

$classes = array(
	'fiflp-cuadro',
	'fiflp-cuadro--cols-' . $nc,
	'fade-in',
);

if ( ! $is_cronologia ) {
	$classes[] = 'bloque';
}

if ( 'onepage' === $context ) {
	$classes[] = 'fiflp-cuadro--onepage';
}

if ( $is_cronologia ) {
	$classes[] = 'cronologia-editorial__cuadro';
}

if ( 'manrope' === $tipografia ) {
	$classes[] = 'fiflp-cuadro--cifra-manrope';
}

$aria_label = get_the_title( $cuadro_id );
if ( '' === trim( $aria_label ) ) {
	$aria_label = 'Cuadro de datos';
}

$style_vars = sprintf(
	'--fiflp-cuadro-cifra-size:%1$s;--fiflp-cuadro-texto-size:%2$s;--fiflp-cuadro-color-cifra:%3$s;--fiflp-cuadro-color-texto:%4$s;',
	$cifra_clamp,
	$texto_clamp,
	$color_cifra,
	$color_texto
);
?>

<<?php echo esc_attr( $tag ); ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $style_vars ); ?>"<?php echo $has_grid ? ' role="region" aria-label="' . esc_attr( $aria_label ) . '"' : ''; ?>>
	<?php if ( $has_mensaje ) : ?>
		<p class="fiflp-cuadro__mensaje"><?php echo wp_kses_post( nl2br( esc_html( $mensaje ) ) ); ?></p>
	<?php endif; ?>

	<?php if ( $has_intro ) : ?>
		<div class="fiflp-cuadro__intro">
			<?php echo wp_kses_post( $intro ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $has_grid ) : ?>
		<div class="fiflp-cuadro__grid">
			<?php foreach ( $filas_out as $cells ) : ?>
				<div class="fiflp-cuadro__fila">
					<?php foreach ( $cells as $cell ) : ?>
						<div class="fiflp-cuadro__celda">
							<?php if ( '' !== $cell['cifra'] ) : ?>
								<span class="fiflp-cuadro__cifra"><?php echo esc_html( $cell['cifra'] ); ?></span>
							<?php endif; ?>
							<?php if ( '' !== $cell['texto'] ) : ?>
								<p class="fiflp-cuadro__texto"><?php echo wp_kses_post( nl2br( esc_html( $cell['texto'] ) ) ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</<?php echo esc_attr( $tag ); ?>>
