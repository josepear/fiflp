<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

if ( ! function_exists( 'fiflp_collect_prologo_items_from_blocks' ) ) {
	function fiflp_collect_prologo_items_from_blocks( $bloques ) {
		$items = array();

		if ( ! is_array( $bloques ) ) {
			return $items;
		}

		foreach ( $bloques as $bloque ) {
			$layout = isset( $bloque['acf_fc_layout'] ) ? (string) $bloque['acf_fc_layout'] : '';

			if ( 'prologo' === $layout ) {
				$nombre    = isset( $bloque['nombre'] ) ? trim( (string) $bloque['nombre'] ) : '';
				$cargo     = isset( $bloque['cargo'] ) ? trim( (string) $bloque['cargo'] ) : '';
				$contenido = $bloque['texto'] ?? ( $bloque['contenido'] ?? '' );
				$foto      = $bloque['foto'] ?? null;

				if ( '' === $nombre && '' === $cargo && empty( $contenido ) && empty( $foto ) ) {
					continue;
				}

				$items[] = array(
					'index'     => count( $items ),
					'label'     => '' !== $nombre ? $nombre : 'Prólogo ' . ( count( $items ) + 1 ),
					'nombre'    => $nombre,
					'cargo'     => $cargo,
					'contenido' => $contenido,
					'foto'      => $foto,
				);
			}

			if ( 'prologos' === $layout && ! empty( $bloque['prologos'] ) && is_array( $bloque['prologos'] ) ) {
				foreach ( $bloque['prologos'] as $prologo ) {
					$nombre    = isset( $prologo['nombre'] ) ? trim( (string) $prologo['nombre'] ) : '';
					$cargo     = isset( $prologo['cargo'] ) ? trim( (string) $prologo['cargo'] ) : '';
					$contenido = $prologo['texto'] ?? ( $prologo['contenido'] ?? '' );
					$foto      = $prologo['foto'] ?? null;

					if ( '' === $nombre && '' === $cargo && empty( $contenido ) && empty( $foto ) ) {
						continue;
					}

					$items[] = array(
						'index'     => count( $items ),
						'label'     => '' !== $nombre ? $nombre : 'Prólogo ' . ( count( $items ) + 1 ),
						'nombre'    => $nombre,
						'cargo'     => $cargo,
						'contenido' => $contenido,
						'foto'      => $foto,
					);
				}
			}
		}

		return array_values( $items );
	}
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
