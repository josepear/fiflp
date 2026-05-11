<?php
/**
 * Bloque flexible / módulo onepage: referencia a un Cuadro editorial (CPT).
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
		$value = get_sub_field( $name );
		return null !== $value ? $value : $default;
	}

	return $default;
};

$cuadro_id = (int) $get_field( 'cuadro', 0 );

if ( $cuadro_id <= 0 || ! function_exists( 'fiflp_render_cuadro' ) ) {
	return;
}

$render_args = $args;

if ( ! isset( $render_args['context'] ) ) {
	$render_args['context'] = ! empty( $args['onepage'] ) ? 'onepage' : 'editorial';
}

fiflp_render_cuadro( $cuadro_id, $render_args );
