<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$get_field = static function ( $name, $default = null ) use ( $args ) {
	if ( function_exists( 'fiflp_get_sub_field_compat' ) ) {
		return fiflp_get_sub_field_compat( $name, $args ?? array(), $default );
	}

	$value = get_sub_field( $name );
	return null !== $value ? $value : $default;
};

$alto_raw      = $get_field( 'alto', 80 );
$mostrar_linea = (bool) $get_field( 'mostrar_linea', false );
$color_linea   = sanitize_hex_color( (string) $get_field( 'color_linea', '' ) );
$grosor_raw    = $get_field( 'grosor_linea', 1 );

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
