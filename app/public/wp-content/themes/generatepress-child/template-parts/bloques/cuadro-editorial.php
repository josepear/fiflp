<?php
/**
 * Bloque flexible / módulo onepage: referencia a un Cuadro editorial (CPT).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cuadro_id = (int) fiflp_get_editorial_field( 'cuadro', fiflp_normalize_editorial_args( $args ), 0 );

if ( $cuadro_id <= 0 || ! function_exists( 'fiflp_render_cuadro' ) ) {
	return;
}

$render_args = $args;

if ( ! isset( $render_args['context'] ) ) {
	$render_args['context'] = ! empty( $args['onepage'] ) ? 'onepage' : 'editorial';
}

fiflp_render_cuadro( $cuadro_id, $render_args );
