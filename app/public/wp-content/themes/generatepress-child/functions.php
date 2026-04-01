<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function generatepress_child_editorial_anchor( $title = '' ) {
	$row_index = function_exists( 'get_row_index' ) ? (int) get_row_index() : 0;
	$slug      = sanitize_title( wp_strip_all_tags( (string) $title ) );

	if ( '' === $slug ) {
		$slug = 'seccion';
	}

	if ( $row_index > 0 ) {
		return 'capitulo-' . $row_index . '-' . $slug;
	}

	return 'capitulo-' . $slug;
}

add_action(
	'wp_enqueue_scripts',
	function() {
		$style_path  = get_stylesheet_directory() . '/style.css';
		$script_path = get_stylesheet_directory() . '/assets/js/editorial.js';

		wp_enqueue_style(
			'parent-style',
			get_template_directory_uri() . '/style.css',
			array(),
			wp_get_theme( get_template() )->get( 'Version' )
		);

		wp_enqueue_style(
			'editorial-fonts',
			'https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@300;400;500;600&family=Oswald:wght@400;500;600;700&display=swap',
			array(),
			null
		);

		wp_enqueue_style(
			'child-style',
			get_stylesheet_uri(),
			array( 'parent-style', 'editorial-fonts' ),
			file_exists( $style_path ) ? filemtime( $style_path ) : null
		);

		wp_enqueue_script(
			'editorial-js',
			get_stylesheet_directory_uri() . '/assets/js/editorial.js',
			array(),
			file_exists( $script_path ) ? filemtime( $script_path ) : null,
			true
		);
	}
);
