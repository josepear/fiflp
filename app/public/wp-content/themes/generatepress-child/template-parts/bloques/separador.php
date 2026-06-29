<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$normalized_args = fiflp_normalize_editorial_args( $args );
$alto_raw        = fiflp_get_editorial_field( 'alto', $normalized_args, 80 );
$mostrar_linea   = (bool) fiflp_get_editorial_field( 'mostrar_linea', $normalized_args, false );
$color_linea     = sanitize_hex_color( (string) fiflp_get_editorial_field( 'color_linea', $normalized_args, '' ) );
$grosor_raw      = fiflp_get_editorial_field( 'grosor_linea', $normalized_args, 1 );

$alto   = is_numeric( $alto_raw ) ? (int) $alto_raw : 80;
$grosor = is_numeric( $grosor_raw ) ? (int) $grosor_raw : 1;

$alto   = max( 0, min( 100, $alto ) );
$grosor = max( 1, min( 12, $grosor ) );

if ( '' === $color_linea ) {
	$color_linea = '#0f2d30';
}
?>

<section class="bloque separador" style="--separador-alto: <?php echo esc_attr( (string) $alto ); ?>px; --separador-color: <?php echo esc_attr( $color_linea ); ?>; --separador-grosor: <?php echo esc_attr( (string) $grosor ); ?>px;" aria-hidden="true">
	<?php if ( $mostrar_linea ) : ?>
		<span class="separador__linea"></span>
	<?php endif; ?>
</section>
