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

function fiflp_get_local_svg_path_from_url( $url ) {
	$url = trim( (string) $url );

	if ( '' === $url || '.svg' !== strtolower( substr( strtok( $url, '?' ), -4 ) ) ) {
		return '';
	}

	$uploads = wp_get_upload_dir();

	if ( ! empty( $uploads['baseurl'] ) && ! empty( $uploads['basedir'] ) && 0 === strpos( $url, $uploads['baseurl'] ) ) {
		$relative = ltrim( substr( $url, strlen( $uploads['baseurl'] ) ), '/' );
		$path     = trailingslashit( $uploads['basedir'] ) . $relative;

		if ( file_exists( $path ) ) {
			return $path;
		}
	}

	$stylesheet_url = get_stylesheet_directory_uri();

	if ( 0 === strpos( $url, $stylesheet_url ) ) {
		$relative = ltrim( substr( $url, strlen( $stylesheet_url ) ), '/' );
		$path     = trailingslashit( get_stylesheet_directory() ) . $relative;

		if ( file_exists( $path ) ) {
			return $path;
		}
	}

	return '';
}

function fiflp_get_svg_logo_markup( $image, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class'            => '',
			'alt'              => '',
			'decorative'       => false,
			'normalize_colors' => true,
		)
	);

	$image_data = fiflp_get_image_data( $image, 'full', (string) $args['alt'] );
	$url        = isset( $image_data['url'] ) ? (string) $image_data['url'] : '';

	if ( '' === $url ) {
		return '';
	}

	$svg_path = fiflp_get_local_svg_path_from_url( $url );

	if ( '' === $svg_path ) {
		return '';
	}

	$svg_markup = file_get_contents( $svg_path );

	if ( false === $svg_markup || '' === trim( $svg_markup ) ) {
		return '';
	}

	$svg_markup = preg_replace( '/<\?xml.*?\?>/i', '', $svg_markup );
	$svg_markup = preg_replace( '/<!DOCTYPE.*?>/i', '', $svg_markup );

	if ( ! empty( $args['normalize_colors'] ) ) {
		$svg_markup = preg_replace( '/fill="(?!none)[^"]*"/i', 'fill="currentColor"', $svg_markup );
		$svg_markup = preg_replace( '/stroke="(?!none)[^"]*"/i', 'stroke="currentColor"', $svg_markup );
	}

	$svg_class = trim( 'fiflp-inline-svg ' . (string) $args['class'] );
	$label     = trim( (string) ( $image_data['alt'] ?: $args['alt'] ) );

	$accessibility = ! empty( $args['decorative'] )
		? ' aria-hidden="true" focusable="false"'
		: ' role="img" aria-label="' . esc_attr( $label ) . '" focusable="false"';

	$svg_markup = preg_replace(
		'/<svg\b([^>]*)>/i',
		'<svg$1 class="' . esc_attr( $svg_class ) . '"' . $accessibility . '>',
		$svg_markup,
		1
	);

	return trim( $svg_markup );
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

function fiflp_normalize_home_hero_button_url( $value ) {
	$to_relative_url = static function ( $url ) {
		$url = trim( (string) $url );

		if ( '' === $url ) {
			return '';
		}

		$path  = wp_parse_url( $url, PHP_URL_PATH );
		$query = wp_parse_url( $url, PHP_URL_QUERY );
		$hash  = wp_parse_url( $url, PHP_URL_FRAGMENT );

		if ( ! is_string( $path ) || '' === $path ) {
			$path = '/';
		}

		$relative = $path;

		if ( is_string( $query ) && '' !== $query ) {
			$relative .= '?' . $query;
		}

		if ( is_string( $hash ) && '' !== $hash ) {
			$relative .= '#' . $hash;
		}

		return $relative;
	};

	if ( $value instanceof WP_Post ) {
		$permalink = get_permalink( $value );
		return $permalink ? $to_relative_url( $permalink ) : '';
	}

	if ( is_array( $value ) ) {
		if ( ! empty( $value['url'] ) ) {
			return fiflp_normalize_home_hero_button_url( $value['url'] );
		}

		if ( isset( $value['ID'] ) ) {
			$value = $value['ID'];
		} elseif ( isset( $value['id'] ) ) {
			$value = $value['id'];
		} elseif ( isset( $value[0] ) && ! is_array( $value[0] ) ) {
			$value = $value[0];
		}
	}

	if ( is_numeric( $value ) ) {
		$permalink = get_permalink( (int) $value );
		return $permalink ? $to_relative_url( $permalink ) : '';
	}

	if ( is_string( $value ) ) {
		$value = trim( $value );

		if ( '' === $value ) {
			return '';
		}

		if ( 0 === strpos( $value, '/' ) ) {
			return $value;
		}

		$post_id = url_to_postid( $value );
		if ( $post_id > 0 ) {
			$permalink = get_permalink( $post_id );
			if ( $permalink ) {
				return $to_relative_url( $permalink );
			}
		}

		if ( wp_http_validate_url( $value ) ) {
			$current_host = wp_parse_url( home_url( '/' ), PHP_URL_HOST );
			$value_host   = wp_parse_url( $value, PHP_URL_HOST );

			$is_same_host = is_string( $current_host )
				&& is_string( $value_host )
				&& 0 === strcasecmp( $current_host, $value_host );

			$is_local_link = in_array( $current_host, array( 'localhost', '127.0.0.1' ), true )
				&& in_array( $value_host, array( 'localhost', '127.0.0.1' ), true );

			if ( $is_same_host || $is_local_link ) {
				return $to_relative_url( $value );
			}
		}

		return $value;
	}

	return '';
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

if ( ! function_exists( 'fiflp_resolve_onepage_seccion_post_id' ) ) {
	/**
	 * Resuelve el ID del CPT de sección onepage desde el valor ACF del bloque.
	 *
	 * @param mixed $ref Valor del subcampo seccion_onepage.
	 * @return int
	 */
	function fiflp_resolve_onepage_seccion_post_id( $ref ) {
		if ( is_numeric( $ref ) ) {
			return (int) $ref;
		}

		if ( is_array( $ref ) ) {
			return (int) ( $ref['ID'] ?? $ref['id'] ?? 0 );
		}

		if ( $ref instanceof WP_Post ) {
			return (int) $ref->ID;
		}

		return 0;
	}
}

if ( ! function_exists( 'fiflp_collect_onepage_nav_sections' ) ) {
	/**
	 * Devuelve un anchor estable para un módulo dentro de una sección onepage.
	 *
	 * @param int $row_index Índice 1-based de la fila del flexible "bloques".
	 * @param int $module_index Índice 1-based del módulo dentro de "modulos_onepage".
	 * @return string
	 */
	function fiflp_onepage_module_anchor( $row_index, $module_index ) {
		$row_index    = max( 1, (int) $row_index );
		$module_index = max( 1, (int) $module_index );

		return 'fiflp-onepage-row-' . $row_index . '-mod-' . $module_index;
	}

	/**
	 * Obtiene etiqueta de submenú para módulo cronología onepage.
	 *
	 * @param array $modulo Módulo ACF.
	 * @return string
	 */
	function fiflp_onepage_cronologia_submenu_label( $modulo ) {
		if ( ! is_array( $modulo ) ) {
			return '';
		}

		$titulo_submenu = trim( (string) ( $modulo['titulo_submenu'] ?? '' ) );
		if ( '' !== $titulo_submenu ) {
			return $titulo_submenu;
		}

		$titulo_cronologia = trim( (string) ( $modulo['titulo_cronologia'] ?? '' ) );
		if ( '' !== $titulo_cronologia ) {
			return str_replace( array( "\r\n", "\r", "\n" ), ' ', $titulo_cronologia );
		}

		$cronologia_ref = $modulo['cronologia'] ?? 0;
		$cronologia_id  = is_numeric( $cronologia_ref ) ? (int) $cronologia_ref : 0;
		if ( $cronologia_id > 0 ) {
			return trim( (string) get_the_title( $cronologia_id ) );
		}

		return '';
	}

	/**
	 * Indica si una sección onepage tiene contenido renderizable (nuevo flexible o legacy).
	 *
	 * @param int $seccion_id ID del CPT onepage.
	 * @return bool
	 */
	function fiflp_onepage_section_has_renderable_content( $seccion_id ) {
		$seccion_id = (int) $seccion_id;

		if ( $seccion_id <= 0 || ! function_exists( 'get_field' ) ) {
			return false;
		}

		$modulos = get_field( 'modulos_onepage', $seccion_id );
		if ( is_array( $modulos ) && ! empty( $modulos ) ) {
			return true;
		}

		$items = get_field( 'items_contenido', $seccion_id );
		return is_array( $items ) && ! empty( $items );
	}

	/**
	 * Recorre los bloques guardados y devuelve metadatos para el índice lateral onepage.
	 * El ancla coincide con get_row_index() en plantilla (índice 1-based de la fila flexible).
	 *
	 * @param array $bloques Valor del campo flexible 'bloques'.
	 * @return array<int, array{id:int, anchor:string, numero:string, titulo:string, label:string, row_index:int, subitems:array<int,array{anchor:string,label:string}>}>
	 */
	function fiflp_collect_onepage_nav_sections( $bloques ) {
		$sections = array();

		if ( ! is_array( $bloques ) || ! function_exists( 'get_field' ) ) {
			return $sections;
		}

		foreach ( $bloques as $index => $bloque ) {
			$layout = isset( $bloque['acf_fc_layout'] ) ? (string) $bloque['acf_fc_layout'] : '';

			if ( 'seccion_onepage' !== $layout ) {
				continue;
			}

			$seccion_id = fiflp_resolve_onepage_seccion_post_id( $bloque['seccion_onepage'] ?? null );

			if ( $seccion_id <= 0 ) {
				continue;
			}

			if ( ! fiflp_onepage_section_has_renderable_content( $seccion_id ) ) {
				continue;
			}

			$numero = trim( (string) get_field( 'numero_seccion', $seccion_id ) );
			$titulo = trim( (string) get_field( 'titulo_seccion', $seccion_id ) );

			if ( '' === $titulo ) {
				$titulo = get_the_title( $seccion_id );
			}

			$row_index = (int) $index + 1;
			$label     = '' !== $numero ? $numero . '. ' . $titulo : $titulo;
			$subitems  = array();

			$modulos = get_field( 'modulos_onepage', $seccion_id );
			if ( is_array( $modulos ) && ! empty( $modulos ) ) {
				$module_index = 0;
				foreach ( $modulos as $modulo ) {
					$module_index++;
					$layout = isset( $modulo['acf_fc_layout'] ) ? (string) $modulo['acf_fc_layout'] : '';
					if ( 'cronologia_editorial' !== $layout ) {
						continue;
					}

					$mostrar = isset( $modulo['mostrar_en_submenu'] ) ? (bool) $modulo['mostrar_en_submenu'] : false;
					if ( ! $mostrar ) {
						continue;
					}

					$sub_label = fiflp_onepage_cronologia_submenu_label( $modulo );
					if ( '' === $sub_label ) {
						continue;
					}

					$subitems[] = array(
						'anchor' => fiflp_onepage_module_anchor( $row_index, $module_index ),
						'label'  => $sub_label,
					);
				}
			}

			$sections[] = array(
				'id'         => $seccion_id,
				'anchor'     => 'fiflp-onepage-row-' . $row_index,
				'numero'     => $numero,
				'titulo'     => $titulo,
				'label'      => $label,
				'row_index'  => $row_index,
				'subitems'   => $subitems,
			);
		}

		return $sections;
	}
}

if ( ! function_exists( 'fiflp_get_sub_field_compat' ) ) {
	/**
	 * Devuelve un subcampo desde ACF normal o desde $args['module'] al renderizar desde onepage.
	 *
	 * @param string $field_name Nombre del subcampo.
	 * @param array  $args       Args recibidos en get_template_part.
	 * @param mixed  $default    Valor por defecto.
	 * @return mixed
	 */
	function fiflp_get_sub_field_compat( $field_name, $args = array(), $default = null ) {
		$field_name = (string) $field_name;
		$args       = is_array( $args ) ? $args : array();

		if ( isset( $args['module'] ) && is_array( $args['module'] ) && array_key_exists( $field_name, $args['module'] ) ) {
			return $args['module'][ $field_name ];
		}

		if ( function_exists( 'get_sub_field' ) ) {
			$value = get_sub_field( $field_name );
			return null !== $value ? $value : $default;
		}

		return $default;
	}
}

/**
 * Marca el body cuando la página incluye navegación onepage (para CSS scoped).
 *
 * @param string[] $classes Clases del body.
 * @return string[]
 */
function fiflp_onepage_body_class( $classes ) {
	if ( ! is_singular( 'page' ) || ! function_exists( 'get_field' ) ) {
		return $classes;
	}

	$page_id = (int) get_queried_object_id();

	if ( $page_id <= 0 ) {
		return $classes;
	}

	$bloques = get_field( 'bloques', $page_id );

	if ( ! empty( fiflp_collect_onepage_nav_sections( is_array( $bloques ) ? $bloques : array() ) ) ) {
		$classes[] = 'fiflp-onepage';
	}

	return $classes;
}
add_filter( 'body_class', 'fiflp_onepage_body_class', 15 );

/**
 * Marca el body cuando la portada renderiza Home Hero como vista exclusiva.
 *
 * @param string[] $classes Clases del body.
 * @return string[]
 */
function fiflp_home_hero_body_class( $classes ) {
	if ( ! is_front_page() ) {
		return $classes;
	}

	$page_id = (int) get_option( 'page_on_front' );
	$hero    = function_exists( 'fiflp_get_home_hero_data' ) ? fiflp_get_home_hero_data( $page_id ) : array();

	$has_home_hero = ! empty( $hero['imagen'] )
		|| ! empty( $hero['video'] )
		|| ! empty( $hero['color_fondo'] )
		|| ! empty( $hero['logo_principal'] )
		|| ! empty( $hero['titulo'] )
		|| ! empty( $hero['texto'] )
		|| ! empty( $hero['boton_capitulos_texto'] )
		|| ! empty( $hero['boton_capitulos_url'] )
		|| ! empty( $hero['link_pdf'] )
		|| ! empty( $hero['link_epub'] )
		|| ! empty( $hero['logos'] );

	if ( $has_home_hero ) {
		$classes[] = 'fiflp-home-hero-active';
	}

	return $classes;
}
add_filter( 'body_class', 'fiflp_home_hero_body_class', 20 );

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

function fiflp_get_home_hero_data( $page_id = 0 ) {
	$page_id = (int) $page_id;

	if ( $page_id <= 0 ) {
		$page_id = (int) get_queried_object_id();
	}

	$data = array(
		'imagen'                => null,
		'video'                => '',
		'color_fondo'          => '',
		'logo_principal'        => null,
		'titulo'                => '',
		'texto'                 => '',
		'boton_capitulos_texto' => '',
		'boton_capitulos_url'   => '',
		'boton_capitulos_url_libre' => '',
		'rotulo_titulo_lineas'  => array(),
		'rotulo_etiqueta_html'  => 'h1',
		'rotulo_interlineado'   => 0.86,
		'rotulo_espaciado_letras' => 0.01,
		'link_pdf'              => '',
		'link_epub'             => '',
		'logos'                 => array(),
		'source'                => '',
	);

	if ( ! function_exists( 'get_field' ) ) {
		return $data;
	}

	$option_hero = array(
		'imagen'                => get_field( 'home_hero_imagen_fondo', 'option' ),
		'video'                 => (string) get_field( 'home_hero_video_fondo', 'option' ),
		'color_fondo'           => (string) get_field( 'home_hero_color_fondo', 'option' ),
		'logo_principal'        => get_field( 'home_hero_logo_principal', 'option' ),
		'titulo'                => (string) get_field( 'home_hero_titulo', 'option' ),
		'texto'                 => (string) get_field( 'home_hero_texto', 'option' ),
		'boton_capitulos_texto' => (string) get_field( 'home_hero_boton_capitulos_texto', 'option' ),
		'boton_capitulos_url'   => fiflp_normalize_home_hero_button_url( get_field( 'home_hero_boton_capitulos_url', 'option' ) ),
		'boton_capitulos_url_libre' => trim( (string) get_field( 'home_hero_boton_capitulos_url_libre', 'option' ) ),
		'rotulo_titulo_lineas'  => get_field( 'home_hero_rotulo_titulo_lineas', 'option' ),
		'rotulo_etiqueta_html'  => (string) get_field( 'home_hero_rotulo_etiqueta_html', 'option' ),
		'rotulo_interlineado'   => get_field( 'home_hero_rotulo_interlineado', 'option' ),
		'rotulo_espaciado_letras' => get_field( 'home_hero_rotulo_espaciado_letras', 'option' ),
		'link_pdf'              => get_field( 'home_hero_link_pdf', 'option' ),
		'link_epub'             => get_field( 'home_hero_link_epub', 'option' ),
		'logos'                 => get_field( 'home_hero_logos', 'option' ),
	);

	$has_option_content = ! empty( $option_hero['imagen'] )
		|| '' !== trim( (string) $option_hero['video'] )
		|| '' !== trim( (string) $option_hero['color_fondo'] )
		|| ! empty( $option_hero['logo_principal'] )
		|| '' !== trim( $option_hero['titulo'] )
		|| '' !== trim( $option_hero['texto'] )
		|| '' !== trim( $option_hero['boton_capitulos_texto'] )
		|| '' !== trim( $option_hero['boton_capitulos_url'] )
		|| '' !== trim( $option_hero['boton_capitulos_url_libre'] )
		|| ( is_array( $option_hero['rotulo_titulo_lineas'] ) && ! empty( $option_hero['rotulo_titulo_lineas'] ) )
		|| ! empty( $option_hero['link_pdf'] )
		|| ! empty( $option_hero['link_epub'] )
		|| ! empty( $option_hero['logos'] );

	if ( $has_option_content ) {
		$option_hero['source'] = 'option';
		return $option_hero;
	}

	if ( $page_id <= 0 ) {
		return $data;
	}

	$legacy_page_hero = array(
		'imagen'                => get_field( 'home_hero_imagen_fondo', $page_id ),
		'video'                 => (string) get_field( 'home_hero_video_fondo', $page_id ),
		'color_fondo'           => (string) get_field( 'home_hero_color_fondo', $page_id ),
		'logo_principal'        => get_field( 'home_hero_logo_principal', $page_id ),
		'titulo'                => (string) get_field( 'home_hero_titulo', $page_id ),
		'texto'                 => (string) get_field( 'home_hero_texto', $page_id ),
		'boton_capitulos_texto' => (string) get_field( 'home_hero_boton_capitulos_texto', $page_id ),
		'boton_capitulos_url'   => fiflp_normalize_home_hero_button_url( get_field( 'home_hero_boton_capitulos_url', $page_id ) ),
		'boton_capitulos_url_libre' => trim( (string) get_field( 'home_hero_boton_capitulos_url_libre', $page_id ) ),
		'rotulo_titulo_lineas'  => get_field( 'home_hero_rotulo_titulo_lineas', $page_id ),
		'rotulo_etiqueta_html'  => (string) get_field( 'home_hero_rotulo_etiqueta_html', $page_id ),
		'rotulo_interlineado'   => get_field( 'home_hero_rotulo_interlineado', $page_id ),
		'rotulo_espaciado_letras' => get_field( 'home_hero_rotulo_espaciado_letras', $page_id ),
		'link_pdf'              => get_field( 'home_hero_link_pdf', $page_id ),
		'link_epub'             => get_field( 'home_hero_link_epub', $page_id ),
		'logos'                 => get_field( 'home_hero_logos', $page_id ),
	);

	$has_legacy_page_content = ! empty( $legacy_page_hero['imagen'] )
		|| '' !== trim( (string) $legacy_page_hero['video'] )
		|| '' !== trim( (string) $legacy_page_hero['color_fondo'] )
		|| ! empty( $legacy_page_hero['logo_principal'] )
		|| '' !== trim( $legacy_page_hero['titulo'] )
		|| '' !== trim( $legacy_page_hero['texto'] )
		|| '' !== trim( $legacy_page_hero['boton_capitulos_texto'] )
		|| '' !== trim( $legacy_page_hero['boton_capitulos_url'] )
		|| '' !== trim( $legacy_page_hero['boton_capitulos_url_libre'] )
		|| ( is_array( $legacy_page_hero['rotulo_titulo_lineas'] ) && ! empty( $legacy_page_hero['rotulo_titulo_lineas'] ) )
		|| ! empty( $legacy_page_hero['link_pdf'] )
		|| ! empty( $legacy_page_hero['link_epub'] )
		|| ! empty( $legacy_page_hero['logos'] );

	if ( $has_legacy_page_content ) {
		$legacy_page_hero['source'] = 'page';
		return $legacy_page_hero;
	}

	$legacy_blocks = get_field( 'bloques', $page_id );

	if ( is_array( $legacy_blocks ) ) {
		foreach ( $legacy_blocks as $row ) {
			$layout = isset( $row['acf_fc_layout'] ) ? (string) $row['acf_fc_layout'] : '';

			if ( 'home_hero' !== $layout && 'home-hero' !== $layout ) {
				continue;
			}

			$legacy_flexible_hero = array(
				'imagen'                => $row['imagen_de_fondo'] ?? ( $row['imagen_fondo'] ?? null ),
				'video'                 => isset( $row['video_fondo'] ) ? (string) $row['video_fondo'] : '',
				'color_fondo'           => isset( $row['color_fondo'] ) ? (string) $row['color_fondo'] : '',
				'logo_principal'        => $row['logo_principal'] ?? null,
				'titulo'                => isset( $row['titulo'] ) ? (string) $row['titulo'] : '',
				'texto'                 => isset( $row['texto'] ) ? (string) $row['texto'] : '',
				'boton_capitulos_texto' => isset( $row['boton_capitulos_texto'] ) ? (string) $row['boton_capitulos_texto'] : '',
				'boton_capitulos_url'   => fiflp_normalize_home_hero_button_url( $row['boton_capitulos_url'] ?? '' ),
				'boton_capitulos_url_libre' => isset( $row['boton_capitulos_url_libre'] ) ? trim( (string) $row['boton_capitulos_url_libre'] ) : '',
				'rotulo_titulo_lineas'  => $row['rotulo_titulo_lineas'] ?? array(),
				'rotulo_etiqueta_html'  => isset( $row['rotulo_etiqueta_html'] ) ? (string) $row['rotulo_etiqueta_html'] : 'h1',
				'rotulo_interlineado'   => $row['rotulo_interlineado'] ?? 0.86,
				'rotulo_espaciado_letras' => $row['rotulo_espaciado_letras'] ?? 0.01,
				'link_pdf'              => $row['link_pdf'] ?? '',
				'link_epub'             => $row['link_epub'] ?? '',
				'logos'                 => isset( $row['logos'] ) && is_array( $row['logos'] ) ? $row['logos'] : array(),
			);

			$has_legacy_flexible_content = ! empty( $legacy_flexible_hero['imagen'] )
				|| '' !== trim( (string) $legacy_flexible_hero['video'] )
				|| '' !== trim( (string) $legacy_flexible_hero['color_fondo'] )
				|| ! empty( $legacy_flexible_hero['logo_principal'] )
				|| '' !== trim( $legacy_flexible_hero['titulo'] )
				|| '' !== trim( $legacy_flexible_hero['texto'] )
				|| '' !== trim( $legacy_flexible_hero['boton_capitulos_texto'] )
				|| '' !== trim( $legacy_flexible_hero['boton_capitulos_url'] )
				|| '' !== trim( $legacy_flexible_hero['boton_capitulos_url_libre'] )
				|| ( is_array( $legacy_flexible_hero['rotulo_titulo_lineas'] ) && ! empty( $legacy_flexible_hero['rotulo_titulo_lineas'] ) )
				|| ! empty( $legacy_flexible_hero['link_pdf'] )
				|| ! empty( $legacy_flexible_hero['link_epub'] )
				|| ! empty( $legacy_flexible_hero['logos'] );

			if ( $has_legacy_flexible_content ) {
				$legacy_flexible_hero['source'] = 'flexible_legacy';
				return $legacy_flexible_hero;
			}
		}
	}

	return $data;
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
	'init',
	function() {
		register_post_type(
			'fiflp_cronologia',
			array(
				'labels' => array(
					'name'          => 'Cronologías',
					'singular_name' => 'Cronología',
					'add_new_item'  => 'Añadir cronología',
					'edit_item'     => 'Editar cronología',
					'menu_name'     => 'Cronologías',
				),
				'public'             => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'menu_position'      => 62,
				'menu_icon'          => 'dashicons-clock',
				'supports'           => array( 'title' ),
				'publicly_queryable' => false,
				'has_archive'        => false,
				'rewrite'            => false,
				'show_in_rest'       => false,
			)
		);

		register_post_type(
			'fiflp_onepage_sec',
			array(
				'labels' => array(
					'name'          => 'Secciones Onepage',
					'singular_name' => 'Sección Onepage',
					'add_new_item'  => 'Añadir sección onepage',
					'edit_item'     => 'Editar sección onepage',
					'menu_name'     => 'Secciones Onepage',
				),
				'public'             => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'menu_position'      => 63,
				'menu_icon'          => 'dashicons-index-card',
				'supports'           => array( 'title' ),
				'publicly_queryable' => false,
				'has_archive'        => false,
				'rewrite'            => false,
				'show_in_rest'       => false,
			)
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

		acf_add_options_page(
			array(
				'page_title' => 'Home Hero',
				'menu_title' => 'Home Hero',
				'menu_slug'  => 'fiflp-home-hero',
				'capability' => 'edit_posts',
				'redirect'   => false,
				'position'   => 59,
			)
		);

		if ( function_exists( 'acf_add_local_field_group' ) ) {
			acf_add_local_field_group(
				array(
					'key' => 'group_home_hero_portada',
					'title' => 'Home Hero Portada',
					'fields' => array(
						array(
							'key' => 'field_home_hero_portada_imagen_fondo',
							'label' => 'Imagen de fondo',
							'name' => 'home_hero_imagen_fondo',
							'type' => 'image',
							'return_format' => 'array',
							'preview_size' => 'medium',
						),
						array(
							'key' => 'field_home_hero_portada_video_fondo',
							'label' => 'Vídeo de fondo',
							'name' => 'home_hero_video_fondo',
							'type' => 'file',
							'instructions' => 'Opcional. Si existe, se muestra como fondo del hero.',
							'return_format' => 'url',
							'mime_types' => 'mp4,webm,ogg',
						),
						array(
							'key' => 'field_home_hero_portada_color_fondo',
							'label' => 'Color de fondo',
							'name' => 'home_hero_color_fondo',
							'type' => 'color_picker',
							'instructions' => 'Se usa cuando no hay imagen, o como base visual del hero.',
							'default_value' => '#0f2d30',
						),
						array(
							'key' => 'field_home_hero_portada_logo_principal',
							'label' => 'Logo principal',
							'name' => 'home_hero_logo_principal',
							'type' => 'image',
							'return_format' => 'array',
							'preview_size' => 'medium',
						),
						array(
							'key' => 'field_home_hero_portada_titulo',
							'label' => 'Título',
							'name' => 'home_hero_titulo',
							'type' => 'text',
						),
						array(
							'key' => 'field_home_hero_portada_rotulo_titulo_lineas',
							'label' => 'Rótulo editorial del título',
							'name' => 'home_hero_rotulo_titulo_lineas',
							'type' => 'repeater',
							'instructions' => 'Si añades filas, sustituye el título simple por rótulo editorial.',
							'layout' => 'block',
							'button_label' => 'Añadir línea de rótulo',
							'sub_fields' => array(
								array(
									'key' => 'field_home_hero_rotulo_linea_titulo',
									'label' => 'Título',
									'name' => 'titulo',
									'type' => 'text',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_supertitulo',
									'label' => 'Supertítulo',
									'name' => 'supertitulo',
									'type' => 'text',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_variante_titulo',
									'label' => 'Variante título',
									'name' => 'variante_titulo',
									'type' => 'button_group',
									'choices' => array( 'linea' => 'Línea', 'relleno' => 'Relleno', 'linea_inversa' => 'Línea In', 'relleno_inverso' => 'Relleno In' ),
									'default_value' => 'linea',
									'layout' => 'horizontal',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_variante_supertitulo',
									'label' => 'Variante supertítulo',
									'name' => 'variante_supertitulo',
									'type' => 'button_group',
									'choices' => array( 'linea' => 'Línea', 'relleno' => 'Relleno', 'linea_inversa' => 'Línea In', 'relleno_inverso' => 'Relleno In' ),
									'default_value' => 'linea',
									'layout' => 'horizontal',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_tamano',
									'label' => 'Tamaño',
									'name' => 'tamano',
									'type' => 'button_group',
									'choices' => array( 's' => 'S', 'm' => 'M', 'l' => 'L', 'xl' => 'XL' ),
									'default_value' => 'm',
									'layout' => 'horizontal',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_color_trazo',
									'label' => 'Color del trazo',
									'name' => 'color_trazo',
									'type' => 'color_picker',
									'default_value' => '#0f2d30',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_color_fondo',
									'label' => 'Color de fondo',
									'name' => 'color_fondo',
									'type' => 'color_picker',
									'default_value' => '#fcfcf8',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_color_texto',
									'label' => 'Color del texto',
									'name' => 'color_texto',
									'type' => 'color_picker',
									'instructions' => 'Opcional. Cambia solo el texto del rótulo.',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_alineacion_rotulo',
									'label' => 'Alineación del rótulo',
									'name' => 'alineacion_rotulo',
									'type' => 'button_group',
									'choices' => array( 'left' => '←', 'center' => '↔', 'right' => '→' ),
									'default_value' => 'left',
									'layout' => 'horizontal',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_subtitulo',
									'label' => 'Subtítulo',
									'name' => 'subtitulo',
									'type' => 'text',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_tamano_subtitulo',
									'label' => 'Tamaño subtítulo',
									'name' => 'tamano_subtitulo',
									'type' => 'button_group',
									'choices' => array( 's' => 'S', 'm' => 'M', 'l' => 'L' ),
									'default_value' => 'm',
									'layout' => 'horizontal',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_color_subtitulo',
									'label' => 'Color subtítulo',
									'name' => 'color_subtitulo',
									'type' => 'color_picker',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_alineacion_subtitulo',
									'label' => 'Alineación subtítulo',
									'name' => 'alineacion_subtitulo',
									'type' => 'button_group',
									'choices' => array( 'left' => '←', 'center' => '↔', 'right' => '→' ),
									'default_value' => 'left',
									'layout' => 'horizontal',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_ancho_subtitulo',
									'label' => 'Ancho subtítulo',
									'name' => 'ancho_subtitulo',
									'type' => 'button_group',
									'choices' => array( 'igual_rotulo' => '=', 'estrecho' => '▭', 'ancho' => '▯' ),
									'default_value' => 'igual_rotulo',
									'layout' => 'horizontal',
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_interlineado_subtitulo',
									'label' => 'Interlineado subtítulo',
									'name' => 'interlineado_subtitulo',
									'type' => 'number',
									'default_value' => 1.6,
									'step' => 0.05,
									'min' => 1,
									'max' => 2.2,
								),
								array(
									'key' => 'field_home_hero_rotulo_linea_espaciado_letras_subtitulo',
									'label' => 'Espaciado letras subtítulo',
									'name' => 'espaciado_letras_subtitulo',
									'type' => 'number',
									'default_value' => 0,
									'step' => 0.005,
									'min' => -0.05,
									'max' => 0.2,
								),
							),
						),
						array(
							'key' => 'field_home_hero_rotulo_etiqueta_html',
							'label' => 'Etiqueta HTML del rótulo',
							'name' => 'home_hero_rotulo_etiqueta_html',
							'type' => 'select',
							'choices' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4' ),
							'default_value' => 'h1',
						),
						array(
							'key' => 'field_home_hero_rotulo_interlineado',
							'label' => 'Interlineado del rótulo',
							'name' => 'home_hero_rotulo_interlineado',
							'type' => 'number',
							'default_value' => 0.86,
							'step' => 0.01,
							'min' => 0.6,
							'max' => 2,
						),
						array(
							'key' => 'field_home_hero_rotulo_espaciado_letras',
							'label' => 'Espaciado de letras del rótulo',
							'name' => 'home_hero_rotulo_espaciado_letras',
							'type' => 'number',
							'default_value' => 0.01,
							'step' => 0.005,
							'min' => -0.05,
							'max' => 0.2,
						),
						array(
							'key' => 'field_home_hero_portada_texto',
							'label' => 'Texto',
							'name' => 'home_hero_texto',
							'type' => 'textarea',
							'new_lines' => 'br',
							'rows' => 3,
						),
						array(
							'key' => 'field_home_hero_portada_boton_texto',
							'label' => 'Texto botón capítulos',
							'name' => 'home_hero_boton_capitulos_texto',
							'type' => 'text',
							'default_value' => 'IR A LOS CAPÍTULOS',
						),
						array(
							'key' => 'field_home_hero_portada_boton_url',
							'label' => 'Página botón capítulos',
							'name' => 'home_hero_boton_capitulos_url',
							'type' => 'page_link',
							'post_type' => array(
								0 => 'page',
							),
							'taxonomy' => array(),
							'allow_archives' => 0,
							'multiple' => 0,
							'allow_null' => 1,
						),
						array(
							'key' => 'field_home_hero_portada_boton_url_libre',
							'label' => 'URL libre botón capítulos',
							'name' => 'home_hero_boton_capitulos_url_libre',
							'type' => 'url',
							'instructions' => 'Opcional. Si lo rellenas, esta URL tiene prioridad sobre la página seleccionada.',
						),
						array(
							'key' => 'field_home_hero_portada_link_pdf',
							'label' => 'Enlace PDF',
							'name' => 'home_hero_link_pdf',
							'type' => 'file',
							'return_format' => 'url',
						),
						array(
							'key' => 'field_home_hero_portada_link_epub',
							'label' => 'Enlace EPUB',
							'name' => 'home_hero_link_epub',
							'type' => 'file',
							'return_format' => 'url',
						),
						array(
							'key' => 'field_home_hero_portada_logos',
							'label' => 'Logos inferiores',
							'name' => 'home_hero_logos',
							'type' => 'repeater',
							'layout' => 'table',
							'button_label' => 'Añadir logo',
							'sub_fields' => array(
								array(
									'key' => 'field_home_hero_portada_logos_imagen',
									'label' => 'Logo',
									'name' => 'imagen',
									'type' => 'image',
									'return_format' => 'array',
									'preview_size' => 'thumbnail',
								),
							),
						),
					),
					'location' => array(
						array(
							array(
								'param' => 'options_page',
								'operator' => '==',
								'value' => 'fiflp-home-hero',
							),
						),
					),
					'position' => 'normal',
					'style' => 'default',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'active' => true,
				)
			);
		}
	}
);

/**
 * Oculta temporalmente la página "Apariencia editorial" del menú admin
 * sin eliminar sus campos ni datos.
 */
add_action(
	'admin_menu',
	function() {
		remove_menu_page( 'fiflp-apariencia' );
	},
	999
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
	'admin_enqueue_scripts',
	function () {
		$skin_path = get_stylesheet_directory() . '/assets/css/acf-admin-skin-divipixel.css';
		if ( ! is_readable( $skin_path ) ) {
			return;
		}
		wp_enqueue_style(
			'fiflp-acf-admin-skin-divipixel',
			get_stylesheet_directory_uri() . '/assets/css/acf-admin-skin-divipixel.css',
			array(),
			(string) filemtime( $skin_path )
		);
	},
	15
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook_suffix ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		if ( 'post.php' !== $hook_suffix && 'post-new.php' !== $hook_suffix ) {
			return;
		}
		if ( 'fiflp_cronologia' === $screen->post_type ) {
			$css_path = get_stylesheet_directory() . '/assets/css/acf-cronologia-editorial-admin.css';
			if ( is_readable( $css_path ) ) {
				wp_enqueue_style(
					'fiflp-acf-cronologia-editorial-admin',
					get_stylesheet_directory_uri() . '/assets/css/acf-cronologia-editorial-admin.css',
					array(),
					(string) filemtime( $css_path )
				);
			}

			$js_path = get_stylesheet_directory() . '/assets/js/acf-cronologia-editorial-admin.js';
			if ( is_readable( $js_path ) ) {
				wp_enqueue_script(
					'fiflp-acf-cronologia-editorial-admin',
					get_stylesheet_directory_uri() . '/assets/js/acf-cronologia-editorial-admin.js',
					array( 'jquery', 'acf-input' ),
					(string) filemtime( $js_path ),
					true
				);
			}
		}

		if ( 'page' === $screen->post_type ) {
			$prologos_css = get_stylesheet_directory() . '/assets/css/acf-prologos-admin.css';
			if ( is_readable( $prologos_css ) ) {
				wp_enqueue_style(
					'fiflp-acf-prologos-admin',
					get_stylesheet_directory_uri() . '/assets/css/acf-prologos-admin.css',
					array(),
					(string) filemtime( $prologos_css )
				);
			}

			$prologos_js = get_stylesheet_directory() . '/assets/js/acf-prologos-admin.js';
			if ( is_readable( $prologos_js ) ) {
				wp_enqueue_script(
					'fiflp-acf-prologos-admin',
					get_stylesheet_directory_uri() . '/assets/js/acf-prologos-admin.js',
					array( 'jquery', 'acf-input' ),
					(string) filemtime( $prologos_js ),
					true
				);
			}

			$rotulo_css = get_stylesheet_directory() . '/assets/css/acf-rotulo-editorial-admin.css';
			if ( is_readable( $rotulo_css ) ) {
				wp_enqueue_style(
					'fiflp-acf-rotulo-editorial-admin',
					get_stylesheet_directory_uri() . '/assets/css/acf-rotulo-editorial-admin.css',
					array(),
					(string) filemtime( $rotulo_css )
				);
			}

			$rotulo_js = get_stylesheet_directory() . '/assets/js/acf-rotulo-editorial-admin.js';
			if ( is_readable( $rotulo_js ) ) {
				wp_enqueue_script(
					'fiflp-acf-rotulo-editorial-admin',
					get_stylesheet_directory_uri() . '/assets/js/acf-rotulo-editorial-admin.js',
					array( 'jquery', 'acf-input' ),
					(string) filemtime( $rotulo_js ),
					true
				);
			}
		}
	},
	20
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
				box-sizing: border-box;
				width: auto;
				max-width: 100%;
				margin-left: 160px;
				margin-right: 0;
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
				width: 100%;
				max-width: 100%;
				box-sizing: border-box;
			}

			.folded .fiflp-apariencia-preview {
				margin-left: 36px;
			}

			@media screen and (max-width: 960px) {
				.fiflp-apariencia-preview {
					margin-left: 36px;
				}
			}

			@media screen and (max-width: 782px) {
				.fiflp-apariencia-preview {
					margin-left: 0;
				}
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
				max-width: 100%;
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
				max-width: 100%;
				box-sizing: border-box;
				white-space: normal;
				overflow-wrap: anywhere;
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

			@media (max-width: 1200px) {
				.fiflp-apariencia-preview {
					padding: 18px;
				}

				.fiflp-apariencia-preview__header,
				.fiflp-apariencia-preview__footer {
					padding: 14px 16px;
					gap: 10px;
				}

				.fiflp-apariencia-preview__body {
					padding: 18px 16px 22px;
				}

				.fiflp-apariencia-preview__rotulo-linea {
					font-size: 21px;
					padding: 8px 14px;
					width: 100%;
					justify-content: flex-start;
				}

				.fiflp-apariencia-preview__rotulo-linea--small {
					font-size: 17px;
					width: auto;
				}

				.fiflp-apariencia-preview__rotulo-linea + .fiflp-apariencia-preview__rotulo-linea {
					margin-left: 0;
				}

				.fiflp-apariencia-preview__text {
					max-width: 100%;
					font-size: 16px;
					line-height: 1.55;
				}

				.fiflp-apariencia-preview__menu,
				.fiflp-apariencia-preview__copy {
					font-size: 12px;
				}
			}

			/* Ajuste limpio: ambos rótulos en la misma línea de alineación */
			.fiflp-apariencia-preview__body {
				overflow: hidden !important;
			}

			.fiflp-apariencia-preview__rotulo {
				display: inline-flex !important;
				flex-direction: column !important;
				align-items: flex-start !important;
				max-width: min(100%, 760px) !important;
			}

			.fiflp-apariencia-preview__rotulo-linea {
				width: auto !important;
				max-width: 100% !important;
				box-sizing: border-box !important;
				justify-content: flex-start !important;
				font-size: clamp(14px, 1.7vw, 26px) !important;
				white-space: nowrap !important;
				overflow: hidden !important;
				text-overflow: ellipsis !important;
			}

			.fiflp-apariencia-preview__rotulo-linea--small {
				margin-left: 0 !important;
			}

			.fiflp-apariencia-preview__rotulo-linea + .fiflp-apariencia-preview__rotulo-linea {
				margin-left: 0 !important;
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
			'<a class="fiflp-centenario-logo" href="%3$s" aria-label="%2$s"><img src="%1$s" alt="%2$s"></a>',
			esc_url( get_stylesheet_directory_uri() . '/assets/logo-centenario.svg' ),
			esc_attr__( 'Logo centenario FIFLP', 'generatepress' ),
			esc_url( home_url( '/' ) )
		);

		return str_replace( '</div>', $extra . '</div>', $output );
	},
	20
);

add_action(
	'acf/input/admin_footer',
	function() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$is_post_editor = in_array( $screen->base, array( 'post', 'post-new' ), true );
		$is_fiflp_options = false !== strpos( (string) $screen->id, 'fiflp-' );

		if ( ! $is_post_editor && ! $is_fiflp_options ) {
			return;
		}
		?>
		<style>
			/* ===== Refuerzo visual global del constructor ACF (solo admin) ===== */
			.acf-flexible-content .layout {
				border: 2px solid #1e1e1e;
				margin-bottom: 14px;
				box-shadow: 0 0 0 1px rgba(30, 30, 30, 0.2);
			}

			.acf-flexible-content .layout .acf-fc-layout-handle {
				background: #f5f5f5;
				color: #111111;
				font-weight: 600;
				font-size: 22px;
				line-height: 1.2;
				letter-spacing: 0.02em;
				text-decoration: none !important;
				border-bottom: 0 !important;
			}

			.acf-flexible-content .layout .acf-fc-layout-handle *,
			.acf-flexible-content .layout .acf-fc-layout-handle a {
				text-decoration: none !important;
			}

			.acf-flexible-content .layout .acf-fc-layout-handle .acf-fc-layout-title,
			.acf-flexible-content .layout .acf-fc-layout-handle .acf-fc-layout-title strong,
			.acf-flexible-content .layout .acf-fc-layout-handle .acf-fc-layout-title span,
			.acf-flexible-content .layout .acf-fc-layout-handle .acf-fc-layout-title em {
				font-size: 22px !important;
				line-height: 1.2 !important;
				text-decoration: none !important;
				color: #1e1e1e !important;
			}

			.acf-flexible-content .layout .acf-fields {
				border-top: 2px solid #1e1e1e;
			}

			.acf-flexible-content .layout .acf-fields > .acf-field {
				border-top: 1px solid rgba(30, 30, 30, 0.6);
			}

			.acf-flexible-content .layout.-collapsed {
				border-style: dashed;
				background: #f5f5f5;
			}

			/*
			 * Layout admin ACF "imagen" fijado intencionalmente.
			 * NO modificar este grid sin diagnostico previo en wp-admin.
			 * ACF no mantiene de forma fiable grids complejos por campo (spans manuales,
			 * dependencia de wrapper.width, reflujo por orden DOM), y eso provoca solapes.
			 * Patron a mantener: columna izquierda fija para imagen + flujo vertical limpio a la derecha.
			 */
			/* ── layout imagen: imagen fija + panel controles 2+2+3 ── */
			.layout[data-layout="imagen"] > .acf-fields {
				display: grid;
				grid-template-columns: 280px repeat(6, 1fr);
				gap: 0 12px;
				align-items: start;
			}

			.layout[data-layout="imagen"] > .acf-fields > .acf-field {
				float: none !important;
				clear: none !important;
				width: auto !important;
				margin: 0 !important;
				padding: 8px 6px !important;
				box-sizing: border-box;
				border-top: 1px solid #f0f0f0 !important;
			}

			/* imagen: columna fija izquierda, ocupa todo el alto */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen"] {
				grid-column: 1;
				grid-row: 1 / span 20;
				border-top: none !important;
				border-right: 1px solid #e0e0e0;
				padding: 10px 16px 10px 6px !important;
			}

			/* caption: línea superior, mayor parte del ancho */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="caption"] {
				grid-column: 2 / span 4;
			}

			/* full: toggle a sangre, columna final */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="full"] {
				grid-column: 6 / span 2;
			}

			/* título editorial: ancho completo del lado derecho */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="titulo_editorial_imagen"] {
				grid-column: 2 / span 6;
			}

			/* === panel controles 2+2+3 via nth-child === */

			/* fila 1: variante_titulo_imagen + tipografia_titulo_imagen */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field:nth-child(5) { grid-column: 2 / span 3; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field:nth-child(6) { grid-column: 5 / span 3; }

			/* fila 2: tamano_titulo_imagen + ancho_titulo_imagen */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field:nth-child(7) { grid-column: 2 / span 3; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field:nth-child(8) { grid-column: 5 / span 3; }

			/* fila 3: alineacion_titulo_imagen + color_titulo_imagen + disposicion_titulo_imagen */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field:nth-child(9)  { grid-column: 2 / span 2; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field:nth-child(10) { grid-column: 4 / span 2; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field:nth-child(11) { grid-column: 6 / span 2; }

			/* tipografia_pie + tamano_pie: debajo del panel */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field:nth-child(12) { grid-column: 2 / span 3; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field:nth-child(13) { grid-column: 5 / span 3; }

			/* preview editorial */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="preview_editorial"] {
				grid-column: 2 / span 6;
				padding-top: 16px !important;
				border-top: 1px solid #e4e6e9 !important;
			}

			/* ocultar textos descriptivos */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field p.description {
				display: none !important;
			}

			@media (max-width: 960px) {
				.layout[data-layout="imagen"] > .acf-fields {
					grid-template-columns: 1fr;
				}

				.layout[data-layout="imagen"] > .acf-fields > .acf-field,
				.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen"] {
					grid-column: 1 !important;
					grid-row: auto !important;
					border-right: none !important;
				}
			}

			/* Repeater rotulo editorial: controles visibles y alto contraste */
			.layout[data-layout="rotulo_editorial"] .acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-actions .acf-button,
			.layout[data-layout="rotulo_editorial"] .acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-actions .button {
				background: #c71818 !important;
				border-color: #a51212 !important;
				color: #ffffff !important;
				font-weight: 700;
				opacity: 1 !important;
				visibility: visible !important;
			}

			.layout[data-layout="rotulo_editorial"] .acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-actions .acf-button:hover,
			.layout[data-layout="rotulo_editorial"] .acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-actions .button:hover {
				background: #a51212 !important;
				border-color: #8e1010 !important;
				color: #ffffff !important;
			}

			.layout[data-layout="rotulo_editorial"] .acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-table > tbody > tr.acf-row {
				background: #ffffff;
			}

			.layout[data-layout="rotulo_editorial"] .acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-table {
				border-collapse: separate !important;
				border-spacing: 0 10px !important;
			}

			.layout[data-layout="rotulo_editorial"] .acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-table > tbody > tr.acf-row > td {
				border-top: 0 !important;
				border-bottom: 0 !important;
			}

			/* Botones del constructor ACF (compactos, no gigantes) */
			/* TODOS los botones del constructor ACF (admin) en tamaño compacto */
			.acf-postbox .button,
			.acf-postbox .button-primary,
			.acf-postbox .button-secondary,
			.acf-postbox .acf-button,
			.acf-postbox .acf-actions .button,
			.acf-postbox .acf-actions .acf-button,
			.acf-postbox .acf-icon.-plus,
			.acf-postbox .acf-icon.-minus {
				font-size: 13px !important;
				line-height: 28px !important;
				padding: 0 10px !important;
				min-height: 30px !important;
				height: auto !important;
				border-radius: 3px !important;
			}

			/* Regla global: button_group ACF con tamaño compacto uniforme y esquinas cuadradas */
			.acf-postbox .acf-button-group {
				display: grid;
				grid-auto-flow: column;
				grid-auto-columns: minmax(0, 1fr);
				width: 100%;
				border-radius: 0 !important;
				overflow: hidden;
				box-sizing: border-box;
			}

			.acf-postbox .acf-button-group label {
				display: flex;
				align-items: center;
				justify-content: center;
				padding: 0 10px !important;
				min-height: 30px !important;
				line-height: 1.2 !important;
				height: auto !important;
				font-size: 13px !important;
				border-radius: 0 !important;
				white-space: nowrap;
				text-align: center;
				overflow: hidden;
				text-overflow: ellipsis;
				min-width: 0;
			}

			.acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields {
				display: grid;
				grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
				gap: 10px 12px;
			}

			.acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field {
				float: none !important;
				clear: none !important;
				width: auto !important;
				margin: 0 !important;
			}

			.acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="texto"] {
				grid-column: 1 / -1;
			}

			.acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="tipografia"] {
				grid-column: 1;
			}

			.acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="variante"] {
				grid-column: 2;
			}

			.acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-button-group {
				display: grid;
				grid-auto-flow: column;
				grid-auto-columns: minmax(0, 1fr);
				width: 100%;
				border-radius: 0 !important;
				overflow: hidden;
			}

			.acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-button-group label {
				display: flex;
				align-items: center;
				justify-content: center;
				padding: 0 8px !important;
				min-height: 30px !important;
				line-height: 1.2 !important;
				height: auto !important;
				font-size: 13px !important;
				border-radius: 0 !important;
				white-space: nowrap;
				text-align: center;
			}

			/* Onepage rótulo: tipografía + variante en la misma línea, ancho completo y botones cuadrados */
			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields {
				display: grid;
				grid-template-columns: repeat(3, minmax(0, 1fr));
				gap: 10px 12px;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field {
				float: none !important;
				clear: none !important;
				width: auto !important;
				margin: 0 !important;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="texto"] {
				grid-column: 1 / -1;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="tipografia"] {
				grid-column: 1;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="tamano"] {
				grid-column: 2;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="variante"] {
				grid-column: 3;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="color_trazo"] {
				grid-column: 1;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="color_fondo"] {
				grid-column: 2;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="color_texto"] {
				grid-column: 3;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-button-group {
				display: grid;
				grid-auto-flow: column;
				grid-auto-columns: minmax(0, 1fr);
				width: 100%;
				border-radius: 0 !important;
				overflow: hidden;
				box-sizing: border-box;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-button-group label {
				display: flex;
				align-items: center;
				justify-content: center;
				padding: 0 4px !important;
				min-height: 30px !important;
				line-height: 1.2 !important;
				height: auto !important;
				font-size: 11px !important;
				border-radius: 0 !important;
				white-space: nowrap;
				text-align: center;
				box-sizing: border-box;
				overflow: hidden;
				text-overflow: ellipsis;
				min-width: 0;
			}

			/* Variante tiene 4 opciones y nombres más largos: compactamos un poco más */
			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-field[data-name="variante"] .acf-button-group label {
				font-size: 10px !important;
				padding: 0 3px !important;
			}

			.layout[data-layout="rotulo_editorial"] .acf-field[data-key="field_rotulo_editorial_titulo_lineas"] .acf-row-handle .acf-icon.-plus {
				display: none !important;
			}

			.fiflp-imagen-preview {
				margin-top: 10px;
				padding: 12px;
				border: 1px dashed #c9cec9;
				border-radius: 10px;
				background: #f8f8f4;
			}

			.fiflp-imagen-preview__meta {
				display: flex;
				flex-wrap: wrap;
				align-items: flex-start;
				gap: 10px;
			}

			.fiflp-imagen-preview[data-preview-disposicion="stacked"] .fiflp-imagen-preview__meta {
				flex-direction: column;
				flex-wrap: nowrap;
			}

			.fiflp-imagen-preview[data-preview-alineacion="center"] .fiflp-imagen-preview__meta {
				justify-content: center;
				text-align: center;
			}

			.fiflp-imagen-preview[data-preview-alineacion="right"] .fiflp-imagen-preview__meta {
				justify-content: flex-end;
				text-align: right;
			}

			.fiflp-imagen-preview__titulo {
				display: inline-block;
				padding: 6px 14px;
				border: 2px solid #0f2d30;
				font-family: 'FKScreamer Backslanted', sans-serif;
				font-weight: 800;
				text-transform: uppercase;
				line-height: 0.95;
				font-size: 1.1rem;
				color: #0f2d30;
				background: transparent;
			}

			.fiflp-imagen-preview[data-preview-variante="relleno"] .fiflp-imagen-preview__titulo {
				background: #0f2d30;
				color: #fcfcf8;
			}

			.fiflp-imagen-preview[data-preview-tipografia-titulo="slanted"] .fiflp-imagen-preview__titulo {
				font-family: 'FKScreamer Slanted', sans-serif;
			}

			.fiflp-imagen-preview[data-preview-size="xs"] .fiflp-imagen-preview__titulo { font-size: 0.82rem; }
			.fiflp-imagen-preview[data-preview-size="s"] .fiflp-imagen-preview__titulo { font-size: 0.95rem; }
			.fiflp-imagen-preview[data-preview-size="m"] .fiflp-imagen-preview__titulo { font-size: 1.1rem; }
			.fiflp-imagen-preview[data-preview-size="l"] .fiflp-imagen-preview__titulo { font-size: 1.3rem; }
			.fiflp-imagen-preview[data-preview-size="xl"] .fiflp-imagen-preview__titulo { font-size: 1.55rem; }

			.fiflp-imagen-preview__pie {
				margin: 0;
				padding-top: 6px;
				font-family: 'Source Serif 4', serif;
				font-size: 14px;
				line-height: 1.45;
				font-style: italic;
				color: rgba(17, 17, 17, 0.72);
				flex: 1 1 14rem;
				min-width: min(100%, 14rem);
			}

			.fiflp-imagen-preview[data-preview-disposicion="stacked"] .fiflp-imagen-preview__pie {
				padding-top: 0;
				min-width: 0;
				width: 100%;
			}

			.fiflp-imagen-preview[data-preview-tipografia-pie="meta"] .fiflp-imagen-preview__pie {
				font-family: 'Manrope', sans-serif;
			}

			.fiflp-imagen-preview[data-preview-pie-size="s"] .fiflp-imagen-preview__pie { font-size: 12px; }
			.fiflp-imagen-preview[data-preview-pie-size="m"] .fiflp-imagen-preview__pie { font-size: 14px; }
			.fiflp-imagen-preview[data-preview-pie-size="l"] .fiflp-imagen-preview__pie { font-size: 16px; }

			@media (max-width: 782px) {
				.fiflp-imagen-preview__meta {
					gap: 8px;
				}

				.fiflp-imagen-preview__pie {
					flex-basis: 100%;
					min-width: 0;
					padding-top: 0;
				}
			}

			/*
			 * Repeaters ACF (todas las pantallas post): columna del orden / −
			 * sin recuadro en la celda del asa, número sin círculo rojo, iconos por defecto.
			 */
			.acf-repeater .acf-row-handle {
				background: transparent !important;
				border: none !important;
				box-shadow: none !important;
			}

			.acf-repeater .acf-row-handle .acf-row-number {
				background: transparent !important;
				color: #646970 !important;
				border-radius: 0 !important;
				box-shadow: none !important;
			}

			.acf-repeater .acf-row-handle .acf-icon.-minus,
			.acf-repeater .acf-row-handle .acf-icon.-plus {
				background: transparent !important;
				border: none !important;
				border-radius: 0 !important;
				transform: none !important;
			}

			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater.-block > .acf-table > tbody > tr.acf-row > .acf-row-handle.order,
			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater.-block > .acf-table > tbody > tr.acf-row > .acf-row-handle.remove {
				width: 36px !important;
				min-width: 36px !important;
				max-width: 36px !important;
				vertical-align: middle !important;
				text-align: center;
			}

			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater.-block > .acf-table > tbody > tr.acf-row > .acf-row-handle.remove {
				padding-top: 0 !important;
				padding-bottom: 0 !important;
			}

			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater .acf-row-handle.remove .acf-icon.-minus {
				display: inline-flex !important;
				align-items: center;
				justify-content: center;
				width: 36px !important;
				height: 36px !important;
				border-radius: 50% !important;
				background: var(--fiflp-acf-skin-accent, #ff4d00) !important;
				border: 0 !important;
				color: #fff !important;
				font-size: 18px !important;
				line-height: 1 !important;
				margin: 0 auto !important;
				position: relative;
				top: 50%;
				transform: translateY(-50%);
			}

			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater tr.acf-row:not(.-collapsed) > td.acf-fields {
				display: grid !important;
				grid-template-columns: minmax(220px, 0.9fr) 236px minmax(260px, 1.1fr) !important;
				grid-template-areas:
					"nombre foto contenido"
					"cargo foto contenido";
				column-gap: 14px;
				row-gap: 10px;
				align-items: stretch;
			}

			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater tr.acf-row:not(.-collapsed) > td.acf-fields > .acf-field[data-name="nombre"] { grid-area: nombre; }
			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater tr.acf-row:not(.-collapsed) > td.acf-fields > .acf-field[data-name="cargo"] { grid-area: cargo; }
			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater tr.acf-row:not(.-collapsed) > td.acf-fields > .acf-field[data-name="foto"] {
				grid-area: foto;
				align-self: stretch;
			}
			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater tr.acf-row:not(.-collapsed) > td.acf-fields > .acf-field[data-name="contenido"] {
				grid-area: contenido;
				align-self: stretch;
			}
			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater tr.acf-row:not(.-collapsed) > td.acf-fields > .acf-field[data-name="contenido"] textarea {
				min-height: 48px !important;
				max-height: 85px !important;
				line-height: 1.35 !important;
				font-size: 14px !important;
				resize: vertical;
			}

			/* Evita que la capa del título plegado bloquee clic/edición en abierto */
			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater .acf-row > td.acf-fields::before {
				pointer-events: none !important;
			}
		</style>
		<script>
			(function () {
				const readValue = (layout, name) => {
					const field = layout.querySelector('.acf-field[data-name="' + name + '"]');

					if (!field) {
						return '';
					}

					const hidden = field.querySelector('input[type="hidden"]');
					if (hidden) {
						return (hidden.value || '').trim();
					}

					const text = field.querySelector('input[type="text"], textarea');
					if (text) {
						return (text.value || '').trim();
					}

					return '';
				};

				const updatePreview = (layout) => {
					const preview = layout.querySelector('[data-fiflp-imagen-preview]');
					if (!preview) {
						return;
					}

					const title = readValue(layout, 'titulo_editorial_imagen') || 'Tu título editorial';
					const caption = readValue(layout, 'caption') || 'Tu pie de foto aparecerá aquí.';
					const variante = readValue(layout, 'variante_titulo_imagen') || 'linea';
					const tipografiaTitulo = readValue(layout, 'tipografia_titulo_imagen') || 'backslanted';
					const tipografiaPie = readValue(layout, 'tipografia_pie_imagen') || 'body';
					const alineacion = readValue(layout, 'alineacion_titulo_imagen') || 'left';
					const disposicion = readValue(layout, 'disposicion_titulo_imagen') || 'inline';
					const size = readValue(layout, 'tamano_titulo_imagen') || 'm';
					const pieSize = readValue(layout, 'tamano_pie_imagen') || 'm';
					const color = readValue(layout, 'color_titulo_imagen') || '#0f2d30';

					const titleNode = preview.querySelector('[data-preview-title]');
					const captionNode = preview.querySelector('[data-preview-caption]');

					if (titleNode) {
						titleNode.textContent = title;
						titleNode.style.borderColor = color;
						titleNode.style.color = 'relleno' === variante ? '#fcfcf8' : color;
						titleNode.style.background = 'relleno' === variante ? color : 'transparent';
					}

					if (captionNode) {
						captionNode.textContent = caption;
					}

					preview.setAttribute('data-preview-variante', variante);
					preview.setAttribute('data-preview-tipografia-titulo', tipografiaTitulo);
					preview.setAttribute('data-preview-tipografia-pie', tipografiaPie);
					preview.setAttribute('data-preview-alineacion', alineacion);
					preview.setAttribute('data-preview-disposicion', disposicion);
					preview.setAttribute('data-preview-size', size);
					preview.setAttribute('data-preview-pie-size', pieSize);
				};

				const updateAll = () => {
					document.querySelectorAll('.layout[data-layout="imagen"]').forEach(updatePreview);
				};

				document.addEventListener('input', updateAll, true);
				document.addEventListener('change', updateAll, true);

				if (window.acf && typeof window.acf.add_action === 'function') {
					window.acf.add_action('ready append', function () {
						updateAll();
					});
				}

				updateAll();
			}());
		</script>
		<?php
	}
);
