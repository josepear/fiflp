<?php
/**
 * Shim legacy: todo el rótulo editorial usa rotulo-editorial-page.php (modelo Página).
 *
 * @package GeneratePressChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rotulo_args = isset( $args ) && is_array( $args ) ? $args : array();

get_template_part( 'template-parts/bloques/rotulo-editorial-page', null, $rotulo_args );
