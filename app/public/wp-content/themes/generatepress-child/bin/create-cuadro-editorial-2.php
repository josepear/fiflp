<?php
/**
 * Crea o actualiza el cuadro editorial «2» (requiere wp-config.php y ACF).
 *
 * Uso desde la carpeta de WordPress (donde está wp-load.php):
 *   php wp-content/themes/generatepress-child/bin/create-cuadro-editorial-2.php
 *
 * @package GeneratePress_Child
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'Solo CLI.' );
}

$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
$_SERVER['HTTPS']      = $_SERVER['HTTPS'] ?? 'off';

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "No encuentro wp-load.php en: {$wp_load}\n" );
	exit( 1 );
}

require $wp_load;

require_once dirname( __DIR__ ) . '/inc/fiflp-cuadro-seed-2.php';

$result = fiflp_seed_cuadro_editorial_2();

if ( is_wp_error( $result ) ) {
	fwrite( STDERR, $result->get_error_message() . "\n" );
	exit( 1 );
}

echo 'OK. ID: ' . (int) $result . "\n";
echo admin_url( 'post.php?post=' . (int) $result . '&action=edit' ) . "\n";
