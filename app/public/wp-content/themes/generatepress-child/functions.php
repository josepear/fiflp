<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'after_setup_theme',
	function() {
		register_nav_menus(
			array(
				'footer_legal' => 'Footer legal',
			)
		);

		add_editor_style( 'editor-style.css' );
	}
);

function fiflp_get_image_data( $image, $size = 'full', $fallback_alt = '' ) {
	$data = array(
		'id'  => 0,
		'url' => '',
		'alt' => (string) $fallback_alt,
	);

	if ( empty( $image ) ) {
		return $data;
	}

	if ( is_array( $image ) ) {
		$image_id = 0;

		if ( isset( $image['ID'] ) ) {
			$image_id = (int) $image['ID'];
		} elseif ( isset( $image['id'] ) ) {
			$image_id = (int) $image['id'];
		}

		if ( $image_id > 0 ) {
			$data['id'] = $image_id;
			$data['url'] = (string) wp_get_attachment_image_url( $image_id, $size );

			if ( '' === $data['url'] ) {
				$data['url'] = (string) wp_get_attachment_url( $image_id );
			}

			$data['alt'] = (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		}

		if ( isset( $image['url'] ) && '' === $data['url'] ) {
			$data['url'] = (string) $image['url'];
		}

		if ( isset( $image['alt'] ) && '' !== trim( (string) $image['alt'] ) ) {
			$data['alt'] = trim( (string) $image['alt'] );
		}

		if ( '' === $data['alt'] && $data['id'] > 0 ) {
			$data['alt'] = (string) get_the_title( $data['id'] );
		}

		if ( '' === $data['alt'] ) {
			$data['alt'] = (string) $fallback_alt;
		}

		return $data;
	}

	if ( is_numeric( $image ) ) {
		$image_id     = (int) $image;
		$data['id']   = $image_id;
		$data['url']  = (string) wp_get_attachment_image_url( $image_id, $size );

		if ( '' === $data['url'] ) {
			$data['url'] = (string) wp_get_attachment_url( $image_id );
		}

		$data['alt']  = (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true );

		if ( '' === $data['alt'] ) {
			$data['alt'] = (string) get_the_title( $image_id );
		}

		if ( '' === $data['alt'] ) {
			$data['alt'] = (string) $fallback_alt;
		}

		return $data;
	}

	if ( is_string( $image ) ) {
		$data['url'] = trim( $image );
		$data['alt'] = (string) $fallback_alt;
	}

	return $data;
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

function fiflp_get_global_index_roots() {
	$root_pages = get_pages(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'parent'      => 0,
			'sort_column' => 'menu_order,post_title',
			'hierarchical'=> 0,
		)
	);

	if ( function_exists( 'generatepress_child_is_editorial_page' ) ) {
		$root_pages = array_values(
			array_filter(
				$root_pages,
				static function( $page ) {
					return generatepress_child_is_editorial_page( $page );
				}
			)
		);
	}

	return $root_pages;
}

function fiflp_render_global_index_branch( $page, $current_id, $current_ancestors, $level = 0 ) {
	if ( ! ( $page instanceof WP_Post ) ) {
		return;
	}

	$page_id      = (int) $page->ID;
	$is_current   = $page_id === (int) $current_id;
	$is_ancestor  = in_array( $page_id, $current_ancestors, true );
	$children     = generatepress_child_get_editorial_children( $page_id );
	$has_children = ! empty( $children );
	$item_classes = array(
		'fiflp-global-index__item',
		'fiflp-global-index__item--level-' . (int) $level,
	);

	if ( $is_current ) {
		$item_classes[] = 'is-current';
	} elseif ( $is_ancestor ) {
		$item_classes[] = 'is-ancestor';
	}

	if ( $has_children ) {
		$item_classes[] = 'has-children';
	}

	$is_open = 0 === $level || $is_current || $is_ancestor;
	?>
	<li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
		<?php if ( $has_children ) : ?>
			<div class="fiflp-global-index__group<?php echo $is_open ? ' is-open' : ''; ?>">
				<div class="fiflp-global-index__summary">
					<a
						class="fiflp-global-index__link"
						href="<?php echo esc_url( get_permalink( $page ) ); ?>"
						<?php if ( $is_current ) : ?>
							aria-current="page"
						<?php endif; ?>
					>
						<?php echo esc_html( get_the_title( $page ) ); ?>
					</a>
					<button class="fiflp-global-index__toggle" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $is_open ? 'Recoger' : 'Desplegar' ); ?>" data-disclosure-toggle>
						<span aria-hidden="true"><?php echo $is_open ? '−' : '+'; ?></span>
					</button>
				</div>

				<ul class="fiflp-global-index__children">
					<?php foreach ( $children as $child_page ) : ?>
						<?php fiflp_render_global_index_branch( $child_page, $current_id, $current_ancestors, $level + 1 ); ?>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php else : ?>
			<a
				class="fiflp-global-index__link"
				href="<?php echo esc_url( get_permalink( $page ) ); ?>"
				<?php if ( $is_current ) : ?>
					aria-current="page"
				<?php endif; ?>
			>
				<?php echo esc_html( get_the_title( $page ) ); ?>
			</a>
		<?php endif; ?>
	</li>
	<?php
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

function fiflp_render_editorial_block_layout( $layout ) {
	$layout = (string) $layout;

	if ( '' === $layout ) {
		return false;
	}

	$template    = str_replace( '_', '-', $layout );
	$template_id = 'template-parts/bloques/' . $template;

	if ( locate_template( $template_id . '.php', false, false ) ) {
		get_template_part( $template_id );
		return true;
	}

	if ( locate_template( 'template-parts/bloques/' . $layout . '.php', false, false ) ) {
		get_template_part( 'template-parts/bloques/' . $layout );
		return true;
	}

	return false;
}

function fiflp_render_single_prologo( $prologo_item ) {
	$nombre    = isset( $prologo_item['nombre'] ) ? trim( (string) $prologo_item['nombre'] ) : '';
	$cargo     = isset( $prologo_item['cargo'] ) ? trim( (string) $prologo_item['cargo'] ) : '';
	$contenido = $prologo_item['contenido'] ?? '';
	$foto      = $prologo_item['foto'] ?? null;
	$foto_url  = is_array( $foto ) ? ( $foto['url'] ?? '' ) : (string) $foto;
	$foto_alt  = is_array( $foto ) ? ( $foto['alt'] ?? $nombre ) : $nombre;

	if ( '' === $nombre && '' === $cargo && '' === $contenido && '' === $foto_url ) {
		return;
	}
	?>
	<section class="bloque prologos fade-in">
		<article class="prologo">
			<?php if ( $foto_url ) : ?>
				<div class="prologo-img">
					<img src="<?php echo esc_url( $foto_url ); ?>" alt="<?php echo esc_attr( $foto_alt ); ?>">
				</div>
			<?php endif; ?>

			<div class="prologo-content">
				<?php if ( $nombre ) : ?>
					<h2 class="prologo-nombre"><?php echo esc_html( $nombre ); ?></h2>
				<?php endif; ?>

				<?php if ( $cargo ) : ?>
					<p class="prologo-cargo"><?php echo esc_html( $cargo ); ?></p>
				<?php endif; ?>

				<?php if ( $contenido ) : ?>
					<div class="prologo-texto">
						<?php echo wp_kses_post( $contenido ); ?>
					</div>
				<?php endif; ?>
			</div>
		</article>
	</section>
	<?php
}

function fiflp_collect_editorial_reading_nodes( $page ) {
	$page = get_post( $page );

	if ( ! ( $page instanceof WP_Post ) || 'page' !== $page->post_type || 'publish' !== $page->post_status ) {
		return array();
	}

	$page_id       = (int) $page->ID;
	$nodes         = array();
	$children      = generatepress_child_get_editorial_children( $page_id );
	$prologo_items = function_exists( 'get_field' ) ? fiflp_collect_prologo_items_from_blocks( get_field( 'bloques', $page_id ) ) : array();

	if ( ! empty( $prologo_items ) ) {
		foreach ( $prologo_items as $item ) {
			$index = isset( $item['index'] ) ? (int) $item['index'] : count( $nodes );
			$label = isset( $item['label'] ) ? (string) $item['label'] : 'Prólogo ' . ( $index + 1 );

			$nodes[] = array(
				'key'     => 'prologo:' . $page_id . ':' . $index,
				'type'    => 'prologo',
				'page_id' => $page_id,
				'index'   => $index,
				'title'   => $label,
				'url'     => add_query_arg( 'prologo', $index, get_permalink( $page ) ),
			);
		}
	} else {
		$nodes[] = array(
			'key'     => 'page:' . $page_id,
			'type'    => 'page',
			'page_id' => $page_id,
			'index'   => null,
			'title'   => get_the_title( $page ),
			'url'     => get_permalink( $page ),
		);
	}

	foreach ( $children as $child_page ) {
		$nodes = array_merge( $nodes, fiflp_collect_editorial_reading_nodes( $child_page ) );
	}

	return $nodes;
}

function fiflp_get_editorial_reading_order() {
	$nodes      = array();
	$root_pages = fiflp_get_global_index_roots();

	foreach ( $root_pages as $root_page ) {
		$nodes = array_merge( $nodes, fiflp_collect_editorial_reading_nodes( $root_page ) );
	}

	return array_values( $nodes );
}

function fiflp_get_editorial_pagination_context( $page_id, $selected_prologo = null ) {
	$page_id = (int) $page_id;

	if ( $page_id <= 0 ) {
		return array(
			'previous' => null,
			'next'     => null,
		);
	}

	$reading_order = fiflp_get_editorial_reading_order();

	if ( empty( $reading_order ) ) {
		return array(
			'previous' => null,
			'next'     => null,
		);
	}

	$current_key = null;

	if ( null !== $selected_prologo ) {
		$current_key = 'prologo:' . $page_id . ':' . (int) $selected_prologo;
	}

	if ( null === $current_key ) {
		$current_key = 'page:' . $page_id;
	}

	$current_index = null;

	foreach ( $reading_order as $index => $node ) {
		if ( isset( $node['key'] ) && $node['key'] === $current_key ) {
			$current_index = $index;
			break;
		}
	}

	if ( null === $current_index ) {
		$fallback_key = 'page:' . $page_id;

		foreach ( $reading_order as $index => $node ) {
			if ( isset( $node['key'] ) && $node['key'] === $fallback_key ) {
				$current_index = $index;
				break;
			}
		}
	}

	if ( null === $current_index ) {
		return array(
			'previous' => null,
			'next'     => null,
		);
	}

	return array(
		'previous' => $reading_order[ $current_index - 1 ] ?? null,
		'next'     => $reading_order[ $current_index + 1 ] ?? null,
	);
}

function fiflp_render_editorial_pagination( $page_id, $selected_prologo = null ) {
	$context  = fiflp_get_editorial_pagination_context( $page_id, $selected_prologo );
	$previous = $context['previous'];
	$next     = $context['next'];

	if ( empty( $previous ) && empty( $next ) ) {
		return;
	}
	?>
	<nav class="editorial-pagination fade-in" aria-label="Pases de página">
		<?php if ( ! empty( $previous['url'] ) ) : ?>
			<a class="editorial-pagination__item editorial-pagination__item--prev" href="<?php echo esc_url( $previous['url'] ); ?>">
				<span class="editorial-pagination__eyebrow">Anterior</span>
				<span class="editorial-pagination__title"><?php echo esc_html( $previous['title'] ?? '' ); ?></span>
			</a>
		<?php endif; ?>

		<?php if ( ! empty( $next['url'] ) ) : ?>
			<a class="editorial-pagination__item editorial-pagination__item--next" href="<?php echo esc_url( $next['url'] ); ?>">
				<span class="editorial-pagination__eyebrow">Siguiente</span>
				<span class="editorial-pagination__title"><?php echo esc_html( $next['title'] ?? '' ); ?></span>
			</a>
		<?php endif; ?>
	</nav>
	<?php
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
			'https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@300;400;500;600&family=Manrope:wght@400;500;600;700&display=swap',
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

add_action(
	'acf/init',
	function() {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => 'Apariencia editorial',
				'menu_title' => 'Apariencia editorial',
				'menu_slug'  => 'fiflp-apariencia',
				'capability' => 'edit_posts',
				'redirect'   => false,
				'position'   => 60,
			)
		);

		acf_add_options_page(
			array(
				'page_title' => 'Footer editorial',
				'menu_title' => 'Footer editorial',
				'menu_slug'  => 'fiflp-footer',
				'capability' => 'edit_posts',
				'redirect'   => false,
				'position'   => 61,
			)
		);
	}
);

function fiflp_get_editorial_theme_tokens() {
	$tokens = array(
		'bg'      => '#fcfcf8',
		'surface' => '#ffffff',
		'text'    => '#111111',
		'muted'   => '#666666',
		'accent'  => '#0f2d30',
		'border'  => '#d8d8d2',
		'image_radius'      => 18,
		'index_panel_width' => 380,
	);

	if ( ! function_exists( 'get_field' ) ) {
		return $tokens;
	}

	$fields = array(
		'bg'      => 'apariencia_color_fondo',
		'surface' => 'apariencia_color_superficie',
		'text'    => 'apariencia_color_texto',
		'muted'   => 'apariencia_color_texto_secundario',
		'accent'  => 'apariencia_color_acento',
		'border'  => 'apariencia_color_borde',
	);

	foreach ( $fields as $key => $field_name ) {
		$value = sanitize_hex_color( (string) get_field( $field_name, 'option' ) );

		if ( $value ) {
			$tokens[ $key ] = $value;
		}
	}

	$image_radius = absint( get_field( 'apariencia_radio_imagenes', 'option' ) );
	if ( $image_radius >= 0 && $image_radius <= 48 ) {
		$tokens['image_radius'] = $image_radius;
	}

	$index_panel_width = absint( get_field( 'apariencia_ancho_indice', 'option' ) );
	if ( $index_panel_width >= 280 && $index_panel_width <= 520 ) {
		$tokens['index_panel_width'] = $index_panel_width;
	}

	return $tokens;
}

function fiflp_get_editorial_theme_colors() {
	$tokens = fiflp_get_editorial_theme_tokens();

	return array(
		'bg'      => $tokens['bg'],
		'surface' => $tokens['surface'],
		'text'    => $tokens['text'],
		'muted'   => $tokens['muted'],
		'accent'  => $tokens['accent'],
		'border'  => $tokens['border'],
	);
}

add_action(
	'wp_head',
	function() {
		$tokens = fiflp_get_editorial_theme_tokens();
		?>
		<style id="fiflp-theme-colors">
			:root {
				--fiflp-bg: <?php echo esc_html( $tokens['bg'] ); ?>;
				--fiflp-surface: <?php echo esc_html( $tokens['surface'] ); ?>;
				--fiflp-text: <?php echo esc_html( $tokens['text'] ); ?>;
				--fiflp-muted: <?php echo esc_html( $tokens['muted'] ); ?>;
				--fiflp-accent: <?php echo esc_html( $tokens['accent'] ); ?>;
				--fiflp-border: <?php echo esc_html( $tokens['border'] ); ?>;
				--fiflp-image-radius: <?php echo (int) $tokens['image_radius']; ?>px;
				--fiflp-index-panel-width: <?php echo (int) $tokens['index_panel_width']; ?>px;
			}
		</style>
		<?php
	},
	99
);

add_action(
	'acf/input/admin_footer',
	function() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || false === strpos( $screen->id, 'fiflp-apariencia' ) ) {
			return;
		}
		?>
		<style>
			.fiflp-apariencia-preview {
				margin-top: 32px;
				padding: 24px;
				background: #f3f3ee;
				border: 1px solid #dcdcce;
				border-radius: 18px;
			}

			.fiflp-apariencia-preview__title {
				margin: 0 0 16px;
				font-size: 14px;
				font-weight: 600;
				color: #50575e;
			}

			.fiflp-apariencia-preview__canvas {
				--preview-bg: #fcfcf8;
				--preview-surface: #ffffff;
				--preview-text: #111111;
				--preview-muted: #666666;
				--preview-accent: #0f2d30;
				--preview-border: #d8d8d2;
				background: var(--preview-bg);
				color: var(--preview-text);
				border: 1px solid var(--preview-border);
				border-radius: 18px;
				overflow: hidden;
			}

			.fiflp-apariencia-preview__header,
			.fiflp-apariencia-preview__footer {
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 16px;
				padding: 18px 22px;
				background: var(--preview-surface);
			}

			.fiflp-apariencia-preview__logo,
			.fiflp-apariencia-preview__indice {
				font-family: 'Source Serif 4', serif;
				font-size: 18px;
			}

			.fiflp-apariencia-preview__body {
				padding: 26px 22px 30px;
			}

			.fiflp-apariencia-preview__rotulo {
				display: inline-flex;
				flex-direction: column;
				gap: 0;
				color: var(--preview-accent);
				margin-bottom: 22px;
			}

			.fiflp-apariencia-preview__rotulo-linea {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				padding: 10px 20px;
				background: var(--preview-surface);
				border: 3px solid var(--preview-accent);
				font-family: 'FKScreamer Slanted', sans-serif;
				font-size: 26px;
				line-height: 0.9;
				text-transform: uppercase;
			}

			.fiflp-apariencia-preview__rotulo-linea + .fiflp-apariencia-preview__rotulo-linea {
				margin-top: -3px;
				margin-left: 26px;
			}

			.fiflp-apariencia-preview__rotulo-linea--small {
				font-size: 22px;
				margin-left: 10px;
			}

			.fiflp-apariencia-preview__text {
				max-width: 42ch;
				margin: 0 0 24px;
				font-family: 'Source Serif 4', serif;
				font-size: 18px;
				line-height: 1.7;
			}

			.fiflp-apariencia-preview__menu,
			.fiflp-apariencia-preview__copy {
				font-size: 13px;
				color: var(--preview-muted);
			}
		</style>
		<div class="fiflp-apariencia-preview">
			<p class="fiflp-apariencia-preview__title">Vista previa rápida</p>
			<div class="fiflp-apariencia-preview__canvas" data-fiflp-color-preview>
				<div class="fiflp-apariencia-preview__header">
					<div class="fiflp-apariencia-preview__logo">fiflp</div>
					<div class="fiflp-apariencia-preview__indice">Índice</div>
				</div>
				<div class="fiflp-apariencia-preview__body">
					<div class="fiflp-apariencia-preview__rotulo">
						<div class="fiflp-apariencia-preview__rotulo-linea fiflp-apariencia-preview__rotulo-linea--small">1920-1930</div>
						<div class="fiflp-apariencia-preview__rotulo-linea">Estadio Pepe Gonçalves</div>
					</div>
					<p class="fiflp-apariencia-preview__text">Así se verán el fondo general, el texto, el tono editorial y las líneas del sistema visual cuando cambies los colores.</p>
				</div>
				<div class="fiflp-apariencia-preview__footer">
					<div class="fiflp-apariencia-preview__menu">Privacidad · Cookies · Términos</div>
					<div class="fiflp-apariencia-preview__copy">© FIFLP</div>
				</div>
			</div>
		</div>
		<script>
			(function () {
				const preview = document.querySelector('[data-fiflp-color-preview]');

				if (!preview) {
					return;
				}

				const map = {
					bg: 'apariencia_color_fondo',
					surface: 'apariencia_color_superficie',
					text: 'apariencia_color_texto',
					muted: 'apariencia_color_texto_secundario',
					accent: 'apariencia_color_acento',
					border: 'apariencia_color_borde'
				};

				const readFieldValue = (fieldName) => {
					const field = document.querySelector('.acf-field[data-name="' + fieldName + '"] input[type="text"]');
					return field ? field.value : '';
				};

				const updatePreview = () => {
					Object.keys(map).forEach((key) => {
						const value = readFieldValue(map[key]);

						if (value) {
							preview.style.setProperty('--preview-' + key, value);
						}
					});
				};

				document.addEventListener('input', updatePreview, true);
				document.addEventListener('change', updatePreview, true);
				updatePreview();
			}());
		</script>
		<?php
	}
);

add_filter(
	'generate_site_branding_output',
	function( $output ) {
		$extra = sprintf(
			'<div class="fiflp-centenario-logo" aria-hidden="true"><img src="%1$s" alt="%2$s"></div>',
			esc_url( get_stylesheet_directory_uri() . '/assets/logo-centenario.svg' ),
			esc_attr__( 'Logo centenario FIFLP', 'generatepress' )
		);

		return str_replace( '</div>', $extra . '</div>', $output );
	},
	20
);
