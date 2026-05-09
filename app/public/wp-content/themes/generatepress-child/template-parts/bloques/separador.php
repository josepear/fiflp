<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$alto_raw      = get_sub_field( 'alto' );
$mostrar_linea = (bool) get_sub_field( 'mostrar_linea' );
$color_linea   = sanitize_hex_color( (string) get_sub_field( 'color_linea' ) );
$grosor_raw    = get_sub_field( 'grosor_linea' );

$alto   = is_numeric( $alto_raw ) ? (int) $alto_raw : 80;
$grosor = is_numeric( $grosor_raw ) ? (int) $grosor_raw : 1;

$alto   = max( 0, min( 600, $alto ) );
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
