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

function generatepress_child_get_editorial_children( $page_id = 0 ) {
	$page_id = (int) $page_id;

	if ( $page_id <= 0 ) {
		return array();
	}

	return get_pages(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'parent'      => $page_id,
			'sort_column' => 'menu_order,post_title',
			'hierarchical'=> 0,
		)
	);
}

function generatepress_child_is_editorial_page( $page = null ) {
	$page = get_post( $page );

	if ( ! ( $page instanceof WP_Post ) || 'page' !== $page->post_type || 'publish' !== $page->post_status ) {
		return false;
	}

	if ( generatepress_child_get_editorial_children( $page->ID ) ) {
		return true;
	}

	if ( function_exists( 'get_field' ) ) {
		$bloques = get_field( 'bloques', $page->ID );

		return ! empty( $bloques );
	}

	return false;
}

function generatepress_child_get_editorial_menu_pages( $current_page_id = 0 ) {
	$current_page_id = (int) $current_page_id;
	$chapter_page_id = wp_get_post_parent_id( $current_page_id );

	if ( ! $chapter_page_id ) {
		$chapter_page_id = $current_page_id;
	}

	$chapter_parent_id = wp_get_post_parent_id( $chapter_page_id );
	$menu_parent_id    = $chapter_parent_id ? (int) $chapter_parent_id : 0;

	$pages = get_pages(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'parent'      => $menu_parent_id,
			'sort_column' => 'menu_order,post_title',
			'hierarchical'=> 0,
		)
	);

	$pages = array_values(
		array_filter(
			$pages,
			'generatepress_child_is_editorial_page'
		)
	);

	if ( empty( $pages ) ) {
		$current_page = get_post( $current_page_id );

		if ( $current_page instanceof WP_Post && 'page' === $current_page->post_type ) {
			return array( $current_page );
		}
	}

	return $pages;
}

add_filter( 'generate_load_child_theme_stylesheet', '__return_false' );

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
