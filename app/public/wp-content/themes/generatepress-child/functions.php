<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modo estabilidad editor:
 * evita que notices/warnings se impriman en respuestas admin/AJAX/REST.
 */
add_action(
	'init',
	function () {
		$is_admin_like = is_admin()
			|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST );

		if ( ! $is_admin_like ) {
			return;
		}

		@ini_set( 'display_errors', '0' );
		@ini_set( 'html_errors', '0' );
	},
	1
);

/**
 * ACF: asegura `_name` en subcampos de flexible/repeater/grupo (JSON local a veces no lo trae).
 * Evita notices en `acf-field-flexible-content.php` format_value (usa $sub_field['_name']).
 *
 * @param array<string, mixed> $field Campo ACF.
 * @return array<string, mixed>
 */
function fiflp_acf_backfill_field_internal_names_recursive( array &$field ) {
	if ( ! empty( $field['name'] ) && ! isset( $field['_name'] ) ) {
		$field['_name'] = $field['name'];
	}

	if ( ! empty( $field['layouts'] ) && is_array( $field['layouts'] ) ) {
		foreach ( $field['layouts'] as &$layout ) {
			if ( ! is_array( $layout ) || empty( $layout['sub_fields'] ) || ! is_array( $layout['sub_fields'] ) ) {
				continue;
			}
			foreach ( $layout['sub_fields'] as &$sub_field ) {
				if ( is_array( $sub_field ) ) {
					fiflp_acf_backfill_field_internal_names_recursive( $sub_field );
				}
			}
		}
		unset( $layout, $sub_field );
	}

	if ( ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
		foreach ( $field['sub_fields'] as &$sub_field ) {
			if ( is_array( $sub_field ) ) {
				fiflp_acf_backfill_field_internal_names_recursive( $sub_field );
			}
		}
		unset( $sub_field );
	}

	return $field;
}

/**
 * Campos flexible con muchos layouts exportados a JSON; refuerzo quirúrgico.
 */
function fiflp_acf_load_field_backfill_underscore_name( $field ) {
	if ( empty( $field['key'] ) || ! is_array( $field ) ) {
		return $field;
	}

	$target_keys = array(
		'field_bloques',
		'field_seccion_onepage_modulos',
		'field_69c69652256ef',
	);

	if ( ! in_array( (string) $field['key'], $target_keys, true ) ) {
		return $field;
	}

	fiflp_acf_backfill_field_internal_names_recursive( $field );

	return $field;
}

add_filter( 'acf/load_field', 'fiflp_acf_load_field_backfill_underscore_name', 1 );

/**
 * Ruta canónica de ACF Local JSON: guardado y sincronización apuntan aquí.
 *
 * - Tema hijo activo: `acf-json` dentro del stylesheet.
 * - Solo GeneratePress padre: carpeta `generatepress-child/acf-json` en discos del repo.
 *
 * @return string Ruta absoluta sin barra final.
 */
function fiflp_acf_local_json_dir() {
	$stylesheet = (string) get_stylesheet();
	if ( 'generatepress' !== $stylesheet ) {
		return wp_normalize_path( untrailingslashit( get_stylesheet_directory() . '/acf-json' ) );
	}

	return wp_normalize_path(
		untrailingslashit( trailingslashit( get_theme_root() ) . 'generatepress-child/acf-json' )
	);
}

add_filter(
	'acf/settings/save_json',
	function () {
		return fiflp_acf_local_json_dir();
	},
	25
);

add_filter(
	'acf/json/load_paths',
	function ( $paths ) {
		$paths     = is_array( $paths ) ? array_values( array_filter( $paths ) ) : array();
		$canonical = fiflp_acf_local_json_dir();
		if ( '' === $canonical ) {
			return $paths;
		}

		$n_canonical = wp_normalize_path( $canonical );
		$out         = array();
		foreach ( $paths as $p ) {
			if ( wp_normalize_path( untrailingslashit( (string) $p ) ) === $n_canonical ) {
				continue;
			}
			$out[] = $p;
		}
		if ( is_dir( $canonical ) ) {
			$out[] = $canonical;
		}

		return $out;
	},
	25
);

add_action(
	'admin_notices',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'acf_get_setting' ) ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return;
		}

		$acf_screen = ( isset( $screen->post_type ) && false !== strpos( (string) $screen->post_type, 'acf' ) )
			|| ( isset( $screen->id ) && false !== strpos( (string) $screen->id, 'acf' ) );
		if ( ! $acf_screen ) {
			return;
		}

		$dir = fiflp_acf_local_json_dir();
		if ( is_dir( $dir ) && is_writable( $dir ) ) {
			return;
		}

		$msg = is_dir( $dir )
			? sprintf(
				__( 'FIFLP: la carpeta ACF JSON no es escribible (%s). El guardado en disco y la sincronización pueden fallar.', 'generatepress-child' ),
				$dir
			)
			: sprintf(
				__( 'FIFLP: no existe la carpeta ACF JSON (%s). Créala en el servidor con permisos de escritura.', 'generatepress-child' ),
				$dir
			);

		echo '<div class="notice notice-warning"><p>' . esc_html( $msg ) . '</p></div>';
	},
	11
);

/**
 * Desactiva por código el grupo duplicado "Contenido Capítulo" (mismo meta `bloques` que el grupo global).
 * Evita dos flexibles `bloques` en una misma pantalla (p. ej. página ID 35), que rompe el guardado ACF.
 * Reversible: quitar este filtro y fusionar layouts en `group_bloques_editoriales` si hiciera falta.
 *
 * @param bool                 $match       Resultado de la regla.
 * @param array<string, mixed> $rule        Regla de ubicación.
 * @param array<string, mixed> $screen      Pantalla ACF.
 * @param array<string, mixed> $field_group Definición del grupo.
 * @return bool
 */
function fiflp_acf_location_disable_duplicate_capitulo_group( $match, $rule, $screen, $field_group ) {
	if ( empty( $field_group['key'] ) || 'group_69c6962c3d4c9' !== $field_group['key'] ) {
		return $match;
	}

	return false;
}

add_filter( 'acf/location/rule_match', 'fiflp_acf_location_disable_duplicate_capitulo_group', 10, 4 );

/**
 * Cinturón y tirantes: impedir carga del grupo duplicado "Contenido Capítulo".
 * Así ACF no procesa dos flexibles "bloques" en la misma petición.
 */
function fiflp_acf_disable_duplicate_capitulo_group_load( $field_group ) {
	if ( is_array( $field_group ) && ! empty( $field_group['key'] ) && 'group_69c6962c3d4c9' === $field_group['key'] ) {
		return false;
	}

	return $field_group;
}

add_filter( 'acf/load_field_group', 'fiflp_acf_disable_duplicate_capitulo_group_load', 1 );

/**
 * Fuerza pasos de 0.1 en offsets numéricos del onepage en el admin ACF.
 *
 * Evita desajustes cuando el JSON y la base de datos no están sincronizados.
 */
function fiflp_acf_force_tenth_step_onepage_offsets( $field ) {
	$field_name = isset( $field['name'] ) ? (string) $field['name'] : '';
	if ( ! in_array( $field_name, array( 'numero_offset_x', 'numero_top_vh' ), true ) ) {
		return $field;
	}

	$field['step'] = 0.1;

	return $field;
}
add_filter( 'acf/load_field/key=field_seccion_onepage_numero_offset_x', 'fiflp_acf_force_tenth_step_onepage_offsets', 20 );
add_filter( 'acf/load_field/key=field_seccion_onepage_numero_top', 'fiflp_acf_force_tenth_step_onepage_offsets', 20 );

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

/**
 * Admin AJAX: devuelve URL original (full) de un adjunto para previsualización de edición.
 */
function fiflp_ajax_get_original_image_url() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
	}

	$id  = isset( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : 0;
	$url = isset( $_REQUEST['url'] ) ? esc_url_raw( wp_unslash( (string) $_REQUEST['url'] ) ) : '';

	$attachment_id = 0;

	if ( $id > 0 ) {
		$attachment_id = $id;
	} elseif ( '' !== $url ) {
		$attachment_id = attachment_url_to_postid( $url );

		if ( 0 === $attachment_id ) {
			$parsed = wp_parse_url( $url, PHP_URL_PATH );
			if ( is_string( $parsed ) && '' !== $parsed ) {
				$normalized = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $parsed );
				if ( is_string( $normalized ) && '' !== $normalized ) {
					$base = wp_get_upload_dir();
					if ( ! empty( $base['baseurl'] ) ) {
						$candidate = rtrim( (string) $base['baseurl'], '/' ) . $normalized;
						$attachment_id = attachment_url_to_postid( $candidate );
					}
				}
			}
		}
	}

	if ( $attachment_id > 0 ) {
		$full = wp_get_attachment_url( $attachment_id );
		if ( $full ) {
			wp_send_json_success(
				array(
					'url' => esc_url_raw( $full ),
				)
			);
		}
	}

	if ( '' !== $url ) {
		wp_send_json_success(
			array(
				'url' => esc_url_raw( $url ),
			)
		);
	}

	wp_send_json_error( array( 'message' => 'not_found' ), 404 );
}
add_action( 'wp_ajax_fiflp_get_original_image_url', 'fiflp_ajax_get_original_image_url' );

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
	 * Normaliza el número de sección onepage preservando ceros a la izquierda.
	 * Ejemplos: "00" => "00", "0" => "00", "1" => "01", "01" => "01".
	 *
	 * @param mixed $raw Valor original de ACF.
	 * @return string
	 */
	function fiflp_format_onepage_section_number( $raw ) {
		$value = trim( (string) $raw );
		if ( '' === $value ) {
			return '';
		}

		if ( ctype_digit( $value ) ) {
			$int_value = (int) $value;
			if ( $int_value >= 0 && $int_value < 100 ) {
				return str_pad( (string) $int_value, 2, '0', STR_PAD_LEFT );
			}
		}

		return $value;
	}

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
	 * Obtiene etiqueta de submenú para cualquier módulo onepage.
	 *
	 * @param array $modulo Módulo ACF.
	 * @param int   $module_index Índice 1-based del módulo.
	 * @return string
	 */
	function fiflp_onepage_module_submenu_label( $modulo, $module_index = 0 ) {
		if ( ! is_array( $modulo ) ) {
			return '';
		}

		$titulo_submenu = trim( (string) ( $modulo['titulo_submenu'] ?? '' ) );
		if ( '' !== $titulo_submenu ) {
			return $titulo_submenu;
		}

		$layout_mod = isset( $modulo['acf_fc_layout'] ) ? (string) $modulo['acf_fc_layout'] : '';
		if ( 'cuadro_editorial' === $layout_mod ) {
			$cuadro_ref = $modulo['cuadro'] ?? 0;
			$cuadro_id  = is_numeric( $cuadro_ref ) ? (int) $cuadro_ref : 0;
			if ( $cuadro_id > 0 ) {
				$titulo_cuadro = trim( (string) get_the_title( $cuadro_id ) );
				if ( '' !== $titulo_cuadro ) {
					return $titulo_cuadro;
				}
			}
		}

		$titulo_crono = fiflp_onepage_cronologia_submenu_label( $modulo );
		if ( '' !== $titulo_crono ) {
			return $titulo_crono;
		}

		$candidate_fields = array(
			'titulo',
			'titulo_modulo',
			'titulo_editorial_imagen',
			'supertitulo',
		);

		foreach ( $candidate_fields as $field_name ) {
			if ( ! array_key_exists( $field_name, $modulo ) ) {
				continue;
			}
			$value = trim( (string) $modulo[ $field_name ] );
			if ( '' !== $value ) {
				return str_replace( array( "\r\n", "\r", "\n" ), ' ', $value );
			}
		}

		$layout = isset( $modulo['acf_fc_layout'] ) ? (string) $modulo['acf_fc_layout'] : '';
		if ( '' !== $layout ) {
			return ucwords( str_replace( array( '_', '-' ), ' ', $layout ) );
		}

		return $module_index > 0 ? 'Módulo ' . $module_index : '';
	}

	/**
	 * Define si un módulo debe entrar en el submenú onepage.
	 *
	 * @param array $modulo Módulo ACF.
	 * @return bool
	 */
	function fiflp_onepage_module_in_submenu( $modulo ) {
		if ( ! is_array( $modulo ) ) {
			return false;
		}

		$mostrar = isset( $modulo['mostrar_en_submenu'] ) ? (bool) $modulo['mostrar_en_submenu'] : false;
		if ( $mostrar ) {
			return true;
		}

		$titulo_submenu = trim( (string) ( $modulo['titulo_submenu'] ?? '' ) );
		return '' !== $titulo_submenu;
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

			$numero = function_exists( 'fiflp_format_onepage_section_number' )
				? fiflp_format_onepage_section_number( get_field( 'numero_seccion', $seccion_id ) )
				: trim( (string) get_field( 'numero_seccion', $seccion_id ) );
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
					if ( ! fiflp_onepage_module_in_submenu( $modulo ) ) {
						continue;
					}

					$sub_label = fiflp_onepage_module_submenu_label( $modulo, $module_index );
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

if ( ! function_exists( 'fiflp_extend_onepage_module_submenu_fields' ) ) {
	/**
	 * Añade controles de submenú a todos los layouts del flexible "modulos_onepage".
	 * Permite activar cualquier módulo en el submenú lateral onepage.
	 *
	 * @param array $field Campo ACF flexible content.
	 * @return array
	 */
	function fiflp_extend_onepage_module_submenu_fields( $field ) {
		// Desactivado temporalmente: inyección dinámica de subcampos rompía el guardado en editor ACF.
		return $field;
	}
}

// add_filter( 'acf/load_field/key=field_seccion_onepage_modulos', 'fiflp_extend_onepage_module_submenu_fields', 20 );

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

if ( ! function_exists( 'fiflp_cuadro_normalize_px_pair' ) ) {
	/**
	 * Garantiza min <= max para escalados tipográficos (evita clamp() CSS inválido).
	 *
	 * @param mixed $min_raw Valor mínimo desde ACF.
	 * @param mixed $max_raw Valor máximo desde ACF.
	 * @param float $fallback_min Valor por defecto mínimo.
	 * @param float $fallback_max Valor por defecto máximo.
	 * @return float[] {0}=min, {1}=max.
	 */
	function fiflp_cuadro_normalize_px_pair( $min_raw, $max_raw, $fallback_min, $fallback_max ) {
		$min = (float) $min_raw;
		$max = (float) $max_raw;

		if ( $min <= 0 ) {
			$min = (float) $fallback_min;
		}
		if ( $max <= 0 ) {
			$max = (float) $fallback_max;
		}
		if ( $min > $max ) {
			$tmp = $min;
			$min = $max;
			$max = $tmp;
		}

		return array( $min, $max );
	}
}

if ( ! function_exists( 'fiflp_cuadro_clamp_font_size' ) ) {
	/**
	 * Genera un clamp() CSS seguro (extremos ordenados).
	 *
	 * @param float $min_px Mínimo en px.
	 * @param float $max_px Máximo en px.
	 * @param float $vw_mid Punto medio en vw (entre ~2 y 12).
	 * @return string
	 */
	function fiflp_cuadro_clamp_font_size( $min_px, $max_px, $vw_mid = 4.0 ) {
		list( $a, $b ) = fiflp_cuadro_normalize_px_pair( $min_px, $max_px, 12, 16 );
		$vw_mid = max( 2.0, min( 12.0, (float) $vw_mid ) );

		return sprintf( 'clamp(%1$.0fpx, %2$.2fvw, %3$.0fpx)', $a, $vw_mid, $b );
	}
}

if ( ! function_exists( 'fiflp_cuadro_clamp_font_size_fluid' ) ) {
	/**
	 * clamp() lineal entre min y max según el ancho del viewport.
	 * Evita que `clamp(min, pocos vw, max)` deje el tamaño casi siempre por debajo del máximo
	 * (subir "máx. px" en ACF no se notaba en titular/intro del cuadro).
	 *
	 * @param float $min_px            Tamaño mínimo (px).
	 * @param float $max_px            Tamaño máximo (px).
	 * @param int   $viewport_min_px   Vw donde aplica el mínimo (px).
	 * @param int   $viewport_max_px   Vw donde aplica el máximo (px).
	 * @return string
	 */
	function fiflp_cuadro_clamp_font_size_fluid( $min_px, $max_px, $viewport_min_px = 360, $viewport_max_px = 1200 ) {
		list( $a, $b ) = fiflp_cuadro_normalize_px_pair( $min_px, $max_px, 12, 16 );
		$viewport_min_px = (int) max( 280, min( 800, (int) $viewport_min_px ) );
		$viewport_max_px = (int) max( $viewport_min_px + 160, min( 2400, (int) $viewport_max_px ) );
		$span            = max( 1, $viewport_max_px - $viewport_min_px );
		$delta           = $b - $a;

		return sprintf(
			'clamp(%1$.0fpx, calc(%1$.0fpx + %2$.5f * ((100vw - %3$dpx) / %4$d)), %5$.0fpx)',
			$a,
			$delta,
			$viewport_min_px,
			$span,
			$b
		);
	}
}

if ( ! function_exists( 'fiflp_render_cuadro' ) ) {
	/**
	 * Renderiza un cuadro editorial (CPT fiflp_cuadro) desde una única plantilla.
	 *
	 * @param int   $cuadro_id ID del post tipo fiflp_cuadro.
	 * @param array $args      context: editorial|onepage|cronologia; onepage: bool passthrough.
	 * @return void
	 */
	function fiflp_render_cuadro( $cuadro_id, $args = array() ) {
		$cuadro_id = (int) $cuadro_id;

		if ( $cuadro_id <= 0 ) {
			return;
		}

		if ( 'fiflp_cuadro' !== get_post_type( $cuadro_id ) ) {
			return;
		}

		$args                  = is_array( $args ) ? $args : array();
		$args['cuadro_id']     = $cuadro_id;
		$args['render_source'] = 'fiflp_render_cuadro';

		get_template_part( 'template-parts/bloques/cuadro-markup', null, $args );
	}
}

add_action(
	'admin_post_fiflp_seed_cuadro_editorial_2',
	function() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tienes permisos para esta acción.', 'generatepress-child' ), 403 );
		}
		check_admin_referer( 'fiflp_seed_cuadro_editorial_2' );

		require_once get_stylesheet_directory() . '/inc/fiflp-cuadro-seed-2.php';

		$result = fiflp_seed_cuadro_editorial_2();

		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ), esc_html__( 'Cuadro editorial', 'generatepress-child' ), 500 );
		}

		wp_safe_redirect(
			admin_url( 'post.php?post=' . (int) $result . '&action=edit&fiflp_cuadro_2_seeded=1' )
		);
		exit;
	}
);

add_action(
	'admin_notices',
	function() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'edit-fiflp_cuadro' !== $screen->id ) {
			return;
		}

		require_once get_stylesheet_directory() . '/inc/fiflp-cuadro-seed-2.php';

		if ( fiflp_cuadro_editorial_2_post_id() > 0 ) {
			return;
		}

		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=fiflp_seed_cuadro_editorial_2' ),
			'fiflp_seed_cuadro_editorial_2'
		);

		echo '<div class="notice notice-info is-dismissible"><p>';
		echo esc_html__( '¿Añadir el cuadro editorial «2» con los datos de obra (3 columnas, 4 filas)?', 'generatepress-child' );
		echo ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Crear cuadro 2', 'generatepress-child' ) . '</a>';
		echo '</p></div>';
	}
);

add_action(
	'admin_notices',
	function() {
		if ( empty( $_GET['fiflp_cuadro_2_seeded'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'fiflp_cuadro' !== $screen->id || 'post' !== $screen->base ) {
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html__( 'Cuadro «2» creado o actualizado. Revisa los campos y enlázalo donde corresponda.', 'generatepress-child' );
		echo '</p></div>';
	}
);

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
	return;

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

		register_post_type(
			'fiflp_cuadro',
			array(
				'labels' => array(
					'name'          => 'Cuadros editoriales',
					'singular_name' => 'Cuadro editorial',
					'add_new_item'  => 'Añadir cuadro editorial',
					'edit_item'     => 'Editar cuadro editorial',
					'menu_name'     => 'Cuadros editoriales',
				),
				'public'             => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'menu_position'      => 64,
				'menu_icon'          => 'dashicons-editor-table',
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

		if ( 'fiflp_cuadro' === $screen->post_type ) {
			$cuadro_css = get_stylesheet_directory() . '/assets/css/acf-cuadro-editorial-admin.css';
			if ( is_readable( $cuadro_css ) ) {
				wp_enqueue_style(
					'fiflp-acf-cuadro-editorial-admin',
					get_stylesheet_directory_uri() . '/assets/css/acf-cuadro-editorial-admin.css',
					array(),
					(string) filemtime( $cuadro_css )
				);
			}
		}

		if ( in_array( $screen->post_type, array( 'page', 'fiflp_onepage_sec' ), true ) ) {
			$prologos_css = get_stylesheet_directory() . '/assets/css/acf-prologos-admin.css';
			if ( 'page' === $screen->post_type && is_readable( $prologos_css ) ) {
				wp_enqueue_style(
					'fiflp-acf-prologos-admin',
					get_stylesheet_directory_uri() . '/assets/css/acf-prologos-admin.css',
					array(),
					(string) filemtime( $prologos_css )
				);
			}

			// Modo estable: desactivamos JS de admin en page para evitar bloqueos de guardado ACF.

			$rotulo_css = get_stylesheet_directory() . '/assets/css/acf-rotulo-editorial-admin.css';
			if ( is_readable( $rotulo_css ) ) {
				wp_enqueue_style(
					'fiflp-acf-rotulo-editorial-admin',
					get_stylesheet_directory_uri() . '/assets/css/acf-rotulo-editorial-admin.css',
					array(),
					(string) filemtime( $rotulo_css )
				);
			}

			// $rotulo_js desactivado temporalmente por estabilidad de guardado en editor.
		}
	},
	20
);

add_filter(
	'acf/load_field',
	function ( $field ) {
		if ( ! is_array( $field ) || empty( $field['key'] ) ) {
			return $field;
		}

		$rotulo_color_keys = array(
			'field_rotulo_editorial_color_trazo',
			'field_rotulo_editorial_color_fondo',
			'field_rotulo_editorial_color_texto',
			'field_onepage_mod_rotulo_titulo_lineas_color_trazo',
			'field_onepage_mod_rotulo_titulo_lineas_color_fondo',
			'field_onepage_mod_rotulo_titulo_lineas_color_texto',
		);

		if ( ! in_array( $field['key'], $rotulo_color_keys, true ) ) {
			return $field;
		}

		if ( ! isset( $field['wrapper'] ) || ! is_array( $field['wrapper'] ) ) {
			$field['wrapper'] = array();
		}
		$field['wrapper']['width'] = '33';

		if ( 'field_rotulo_editorial_color_texto' === $field['key'] || 'field_onepage_mod_rotulo_titulo_lineas_color_texto' === $field['key'] ) {
			$field['label']        = 'Color de tipografía';
			$field['instructions'] = 'Solo afecta al texto del rótulo, no al trazo ni al relleno.';
		}

		if ( 'field_onepage_mod_rotulo_titulo_lineas_color_trazo' === $field['key'] ) {
			$field['label'] = 'Color del trazo';
		}

		if ( 'field_onepage_mod_rotulo_titulo_lineas_color_fondo' === $field['key'] ) {
			$field['label'] = 'Color de fondo';
		}

		return $field;
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
			/* ── layout imagen: preview grande + panel compacto con fila de colores ── */
			.layout[data-layout="imagen"] > .acf-fields {
				display: grid;
				grid-template-columns: 340px repeat(6, minmax(0, 1fr));
				gap: 0 10px;
				align-items: start;
			}

			.layout[data-layout="imagen"] > .acf-fields > .acf-field {
				float: none !important;
				clear: none !important;
				width: auto !important;
				margin: 0 !important;
				padding: 6px 6px !important;
				box-sizing: border-box;
				border-top: 1px solid #f0f0f0 !important;
			}

			/* imagen: columna fija izquierda, ocupa todo el alto */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen"] {
				grid-column: 1;
				grid-row: 1 / span 20;
				border-top: none !important;
				border-right: 1px solid #e0e0e0;
				padding: 8px 12px 8px 4px !important;
			}
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen"] .acf-image-uploader img {
				width: 100% !important;
				max-width: none !important;
				height: auto !important;
			}

			/* cabecera lado derecho */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="caption"] {
				grid-column: 2 / 8;
				grid-row: 2;
			}

			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="full"] {
				grid-column: 2 / span 3;
				grid-row: 3;
			}

			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="sin_redondeo"] {
				grid-column: 5 / span 3;
				grid-row: 3;
			}

			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="escala_visual_imagen"] {
				grid-column: 2 / 8;
				grid-row: 4;
			}

			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="alineacion_visual_imagen"] {
				grid-column: 2 / 8;
				grid-row: 5;
			}
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="ajuste_sombras_imagen"] {
				display: none !important;
			}
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="ajuste_medios_imagen"] {
				display: none !important;
			}
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="ajuste_luces_imagen"] {
				display: none !important;
			}

			.fiflp-imagen-tono-launch {
				margin-top: 10px !important;
				width: 100%;
				min-height: 34px !important;
				font-size: 13px !important;
			}

			.fiflp-btn-unificado {
				display: inline-flex !important;
				align-items: center;
				justify-content: center;
				min-height: 36px !important;
				padding: 0 14px !important;
				border-radius: 999px !important;
				border: 1px solid #ff4d00 !important;
				background: #ff4d00 !important;
				color: #ffffff !important;
				font-weight: 700 !important;
				font-size: 13px !important;
				line-height: 1 !important;
				box-shadow: none !important;
				text-decoration: none !important;
			}

			.fiflp-btn-unificado:hover,
			.fiflp-btn-unificado:focus {
				background: #e44700 !important;
				border-color: #e44700 !important;
				color: #ffffff !important;
			}

			.fiflp-imagen-tono-modal {
				position: fixed;
				inset: 0;
				background: rgba(17, 17, 17, 0.62);
				z-index: 99999;
				display: none;
				align-items: center;
				justify-content: center;
				padding: 28px;
			}

			.fiflp-imagen-tono-modal.is-open {
				display: flex;
			}

			.fiflp-imagen-tono-modal__dialog {
				width: min(1120px, 94vw);
				max-height: 90vh;
				overflow: auto;
				background: #ffffff;
				border-radius: 10px;
				padding: 18px;
				display: grid;
				grid-template-columns: minmax(0, 2.15fr) minmax(320px, 1fr);
				gap: 16px;
			}

			.fiflp-imagen-tono-modal__preview-wrap {
				background: #f6f6f2;
				border: 1px solid #d8d8d2;
				border-radius: 8px;
				padding: 12px;
				display: flex;
				align-items: center;
				justify-content: center;
				min-height: 560px;
			}

			.fiflp-imagen-tono-modal__preview {
				width: auto;
				height: auto;
				max-width: 100%;
				max-height: 76vh;
				object-fit: contain;
				image-rendering: auto;
				display: block;
			}

			.fiflp-imagen-tono-modal__controls {
				display: grid;
				grid-template-columns: 1fr;
				gap: 12px;
				align-content: start;
			}

			.fiflp-imagen-tono-modal__control label {
				display: block;
				font-weight: 600;
				margin-bottom: 6px;
			}

			.fiflp-imagen-tono-modal__control input[type="range"] {
				width: 100%;
			}

			.fiflp-imagen-tono-modal__actions {
				margin-top: 6px;
				display: flex;
				justify-content: flex-end;
				gap: 8px;
			}

			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="titulo_editorial_imagen"] {
				grid-column: 2 / 8;
				grid-row: 1;
				padding-bottom: 0 !important;
			}

			/* pares compactos */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="variante_titulo_imagen"] { grid-column: 2 / span 3; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tipografia_titulo_imagen"] { grid-column: 5 / span 3; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tamano_titulo_imagen"] { grid-column: 2 / span 3; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="ancho_titulo_imagen"] { grid-column: 5 / span 3; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="alineacion_titulo_imagen"] { grid-column: 2 / span 3; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="disposicion_titulo_imagen"] { grid-column: 5 / span 3; }
			/* tres colores en una sola fila */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_borde_titulo_imagen"] { grid-column: 2 / span 2; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_solido_titulo_imagen"] { grid-column: 4 / span 2; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_letra_titulo_imagen"] { grid-column: 6 / span 2; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tipografia_pie_imagen"] { grid-column: 2 / span 3; }
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tamano_pie_imagen"] { grid-column: 5 / span 3; }
			/* color picker compacto */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_borde_titulo_imagen"] .wp-picker-container .button,
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_solido_titulo_imagen"] .wp-picker-container .button,
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_letra_titulo_imagen"] .wp-picker-container .button {
				font-size: 12px !important;
				padding: 0 8px !important;
			}

			/* preview editorial */
			.layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="preview_editorial"] {
				grid-column: 2 / 8;
				padding-top: 10px !important;
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

			/* Imagen (bloques editoriales + onepage): grid robusto y consistente */
			.post-type-page .layout[data-layout="imagen"] > .acf-fields,
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields {
				display: grid;
				grid-template-columns: 340px repeat(6, minmax(0, 1fr));
				gap: 0;
				align-items: start;
			}

			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field,
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field {
				float: none !important;
				clear: none !important;
				width: auto !important;
				margin: 0 !important;
				padding: 8px 10px !important;
				box-sizing: border-box;
				border-top: 1px solid #e7e9ee !important;
			}

			@media (max-width: 1240px) {
				.post-type-page .layout[data-layout="imagen"] > .acf-fields,
				.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields {
					grid-template-columns: 280px repeat(6, minmax(0, 1fr));
				}
			}

			@media (max-width: 960px) {
				.post-type-page .layout[data-layout="imagen"] > .acf-fields,
				.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields {
					grid-template-columns: 1fr;
				}

				.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field,
				.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen"],
				.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field,
				.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen"] {
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

			/* Sistema de controles global ACF: tamaño homogéneo + esquinas casi cuadradas */
			.acf-postbox .acf-input input[type="text"],
			.acf-postbox .acf-input input[type="number"],
			.acf-postbox .acf-input input[type="url"],
			.acf-postbox .acf-input input[type="email"],
			.acf-postbox .acf-input input[type="password"],
			.acf-postbox .acf-input select,
			.acf-postbox .acf-input textarea,
			.acf-postbox .acf-input .select2-container .select2-selection,
			.acf-postbox .acf-button-group label,
			.acf-postbox .button,
			.acf-postbox .acf-button,
			.acf-postbox .wp-color-result.button {
				min-height: 40px !important;
				height: 40px !important;
				border-radius: 6px !important;
				box-sizing: border-box !important;
			}

			.acf-postbox .acf-input textarea {
				height: auto !important;
				min-height: 96px !important;
				border-radius: 6px !important;
			}

			.acf-postbox .acf-input .wp-picker-container .wp-color-result-text,
			.acf-postbox .acf-input .wp-picker-container .wp-color-result {
				line-height: 38px !important;
			}

			.acf-postbox .acf-input .select2-container .select2-selection__rendered {
				line-height: 38px !important;
			}

			.acf-postbox .acf-input .select2-container .select2-selection__arrow {
				height: 38px !important;
			}

			/* True/False ACF: M en formularios principales, S en formularios densos. */
			.acf-postbox .acf-switch {
				--fiflp-switch-w: 92px;
				--fiflp-switch-h: 34px;
				--fiflp-switch-pad-x: 9px;
				--fiflp-switch-font: 12px;
				--fiflp-switch-knob-w: 34px;
				--fiflp-switch-knob-h: 28px;
				--fiflp-switch-knob-left: 2px;
				display: inline-block !important;
				width: var(--fiflp-switch-w) !important;
				min-height: var(--fiflp-switch-h) !important;
				height: var(--fiflp-switch-h) !important;
				vertical-align: middle !important;
				border-radius: calc(var(--fiflp-switch-h) / 2) !important;
				border: 2px solid #7d8a99 !important;
				overflow: hidden !important;
				position: relative !important;
				padding: 0 !important;
				box-sizing: border-box !important;
			}

			/* Formularios densos: repeaters y zonas internas repetitivas */
			.acf-postbox .acf-repeater .acf-switch,
			.acf-postbox .acf-flexible-content .layout .acf-repeater .acf-switch {
				--fiflp-switch-w: 84px;
				--fiflp-switch-h: 30px;
				--fiflp-switch-pad-x: 8px;
				--fiflp-switch-font: 11px;
				--fiflp-switch-knob-w: 32px;
				--fiflp-switch-knob-h: 26px;
				--fiflp-switch-knob-left: 2px;
			}

			.acf-postbox .acf-switch .acf-switch-on,
			.acf-postbox .acf-switch .acf-switch-off {
				position: absolute !important;
				top: 0 !important;
				width: 50% !important;
				height: var(--fiflp-switch-h) !important;
				line-height: var(--fiflp-switch-h) !important;
				font-size: var(--fiflp-switch-font) !important;
				display: flex !important;
				align-items: center !important;
				padding: 0 !important;
				opacity: 1 !important;
				visibility: visible !important;
				z-index: 1 !important;
			}

			.acf-postbox .acf-switch .acf-switch-on {
				left: 0 !important;
				justify-content: flex-start !important;
				padding-left: var(--fiflp-switch-pad-x) !important;
			}

			.acf-postbox .acf-switch .acf-switch-off {
				right: 0 !important;
				justify-content: flex-end !important;
				padding-right: var(--fiflp-switch-pad-x) !important;
			}

			.acf-postbox .acf-switch .acf-switch-slider {
				width: var(--fiflp-switch-knob-w) !important;
				height: var(--fiflp-switch-knob-h) !important;
				margin: 0 !important;
				left: var(--fiflp-switch-knob-left) !important;
				border-radius: calc(var(--fiflp-switch-knob-h) / 2) !important;
				top: 50% !important;
				transform: translateY(-50%) !important;
				z-index: 2 !important;
			}

			.acf-postbox .acf-switch.-on .acf-switch-slider {
				left: calc(100% - var(--fiflp-switch-knob-w) - var(--fiflp-switch-knob-left)) !important;
			}

			.acf-postbox .acf-field[data-type="true_false"] .acf-input {
				display: flex;
				align-items: center;
				min-height: 40px;
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
				grid-template-columns: repeat(2, minmax(0, 1fr));
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
				grid-column: 1;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="color_trazo"] {
				grid-column: 1;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="color_fondo"] {
				grid-column: 2;
			}

			.acf-field[data-key="field_onepage_mod_rotulo_titulo_lineas"] .acf-table > tbody > tr.acf-row .acf-fields > .acf-field[data-name="color_texto"] {
				grid-column: 1 / -1;
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

			/* ===== Imagen: grid robusto final (Page + Onepage) ===== */
			.post-type-page .layout[data-layout="imagen"] > .acf-fields,
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields {
				display: grid !important;
				grid-template-columns: 300px repeat(6, minmax(0, 1fr)) !important;
				grid-auto-rows: auto !important;
				gap: 0 !important;
				align-items: start !important;
			}

			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field,
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field {
				float: none !important;
				clear: none !important;
				width: auto !important;
				margin: 0 !important;
				padding: 10px 12px !important;
				box-sizing: border-box !important;
				border-top: 1px solid #e4e7ec !important;
				min-width: 0 !important;
			}

			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen"] {
				grid-column: 1 !important;
				grid-row: 1 / span 16 !important;
				border-right: 1px solid #e4e7ec !important;
				padding: 10px 12px 10px 8px !important;
			}

			/* Fila 1-2: campos largos */
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="titulo_editorial_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="titulo_editorial_imagen"] { grid-column: 2 / 8 !important; grid-row: 1 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="caption"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="caption"] { grid-column: 2 / 8 !important; grid-row: 2 !important; }

			/* Fila 3: tres cortos */
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="full"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="full"] { grid-column: 2 / 4 !important; grid-row: 3 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="sin_redondeo"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="sin_redondeo"] { grid-column: 4 / 6 !important; grid-row: 3 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen_multiplicar"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="imagen_multiplicar"] { grid-column: 6 / 8 !important; grid-row: 3 !important; }

			/* Fila 4-5: grupos largos */
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="escala_visual_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="escala_visual_imagen"] { grid-column: 2 / 8 !important; grid-row: 4 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="alineacion_visual_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="alineacion_visual_imagen"] { grid-column: 2 / 8 !important; grid-row: 5 !important; }

			/* Fila 6-7: triples medianos */
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="variante_titulo_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="variante_titulo_imagen"] { grid-column: 2 / 4 !important; grid-row: 6 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tipografia_titulo_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tipografia_titulo_imagen"] { grid-column: 4 / 6 !important; grid-row: 6 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tamano_titulo_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tamano_titulo_imagen"] { grid-column: 6 / 8 !important; grid-row: 6 !important; }

			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="ancho_titulo_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="ancho_titulo_imagen"] { grid-column: 2 / 4 !important; grid-row: 7 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="alineacion_titulo_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="alineacion_titulo_imagen"] { grid-column: 4 / 6 !important; grid-row: 7 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="disposicion_titulo_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="disposicion_titulo_imagen"] { grid-column: 6 / 8 !important; grid-row: 7 !important; }

			/* Fila 8: colores */
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_borde_titulo_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_borde_titulo_imagen"] { grid-column: 2 / 4 !important; grid-row: 8 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_solido_titulo_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_solido_titulo_imagen"] { grid-column: 4 / 6 !important; grid-row: 8 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_letra_titulo_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="color_letra_titulo_imagen"] { grid-column: 6 / 8 !important; grid-row: 8 !important; }

			/* Fila 9: pie */
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tipografia_pie_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tipografia_pie_imagen"] { grid-column: 2 / 5 !important; grid-row: 9 !important; }
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tamano_pie_imagen"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="tamano_pie_imagen"] { grid-column: 5 / 8 !important; grid-row: 9 !important; }

			/* Preview al final, a ancho completo de la zona derecha */
			.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="preview_editorial"],
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field[data-name="preview_editorial"] {
				grid-column: 2 / 8 !important;
				grid-row: 10 !important;
			}

			/* Switches de imagen: mismo tamaño y centrado visual */
			.post-type-page .layout[data-layout="imagen"] .acf-field[data-type="true_false"] .acf-input,
			.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] .acf-field[data-type="true_false"] .acf-input {
				display: flex !important;
				align-items: center !important;
				min-height: 40px !important;
			}


			@media (max-width: 960px) {
				.post-type-page .layout[data-layout="imagen"] > .acf-fields,
				.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields {
					grid-template-columns: 1fr !important;
				}

				.post-type-page .layout[data-layout="imagen"] > .acf-fields > .acf-field,
				.post-type-fiflp_onepage_sec .layout[data-layout="imagen"] > .acf-fields > .acf-field {
					grid-column: 1 !important;
					grid-row: auto !important;
					border-right: none !important;
				}
			}

			/* Evita que la capa del título plegado bloquee clic/edición en abierto */
			.post-type-page .acf-field[data-key="field_prologos_prologos"] .acf-repeater .acf-row > td.acf-fields::before {
				pointer-events: none !important;
			}

			/* Onepage (admin): todos los títulos/labels del formulario en cuerpo 9 */
			.post-type-fiflp_onepage_sec .acf-field .acf-label label,
			.post-type-fiflp_onepage_sec .acf-field .acf-label .acf-required {
				font-size: 9px !important;
				line-height: 1.25 !important;
			}

			/* Onepage (admin): formulario más compacto en 3 columnas */
			.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields {
				display: grid;
				grid-template-columns: repeat(3, minmax(0, 1fr));
				gap: 0;
			}

			.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields > .acf-field {
				float: none !important;
				clear: none !important;
				width: auto !important;
				margin: 0 !important;
				padding: 8px 10px !important;
				box-sizing: border-box;
			}

			/* Campos de contenido largo a ancho completo */
			.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields > .acf-field[data-type="wysiwyg"],
			.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields > .acf-field[data-type="textarea"],
			.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields > .acf-field[data-type="gallery"],
			.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields > .acf-field[data-type="repeater"],
			.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields > .acf-field[data-type="flexible_content"],
			.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields > .acf-field[data-type="image"] {
				grid-column: 1 / -1;
			}

			@media (max-width: 1200px) {
				.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields {
					grid-template-columns: repeat(2, minmax(0, 1fr));
				}
			}

			@media (max-width: 782px) {
				.post-type-fiflp_onepage_sec .acf-flexible-content .layout > .acf-fields {
					grid-template-columns: 1fr;
				}
			}

			/* Regla global ACF admin: ocultar descripciones y mostrarlas como tooltip */
			.acf-field .description {
				display: none !important;
			}

			.acf-field .acf-label label[data-fiflp-help],
			.acf-field .acf-label .acf-label-with-help[data-fiflp-help] {
				position: relative;
				cursor: help;
			}

			.acf-field .acf-label label[data-fiflp-help]::after,
			.acf-field .acf-label .acf-label-with-help[data-fiflp-help]::after {
				content: attr(data-fiflp-help);
				position: absolute;
				left: 0;
				top: calc(100% + 8px);
				min-width: 220px;
				max-width: min(420px, 80vw);
				padding: 8px 10px;
				border-radius: 8px;
				background: rgba(15, 45, 48, 0.96);
				color: #fcfcf8;
				font-size: 12px;
				line-height: 1.35;
				font-weight: 500;
				box-shadow: 0 8px 22px rgba(0, 0, 0, 0.22);
				opacity: 0;
				visibility: hidden;
				transform: translateY(-2px);
				transition: opacity 140ms ease, transform 140ms ease, visibility 140ms ease;
				pointer-events: none;
				z-index: 999999;
				white-space: normal;
			}

			.acf-field .acf-label label[data-fiflp-help]:hover::after,
			.acf-field .acf-label label[data-fiflp-help]:focus-visible::after,
			.acf-field .acf-label .acf-label-with-help[data-fiflp-help]:hover::after,
			.acf-field .acf-label .acf-label-with-help[data-fiflp-help]:focus-visible::after {
				opacity: 1;
				visibility: visible;
				transform: translateY(0);
			}

			/* Alineaciones en button_group: iconos para ahorrar ancho */
			.acf-postbox .acf-button-group label.fiflp-align-iconized {
				font-size: 0 !important;
				position: relative;
			}

			.acf-postbox .acf-button-group label.fiflp-align-iconized .fiflp-align-icon {
				font-size: 16px !important;
				line-height: 1 !important;
				display: inline-flex;
				align-items: center;
				justify-content: center;
				width: 100%;
				color: currentColor;
				letter-spacing: 0;
			}
		</style>
		<script>
			(function () {
				const normalizeAlignToken = (text) => {
					const t = (text || '').toString().trim().toLowerCase();
					if (['left', 'izquierda', 'l'].includes(t)) return 'left';
					if (['center', 'centro', 'c'].includes(t)) return 'center';
					if (['right', 'derecha', 'r'].includes(t)) return 'right';
					return '';
				};

				const alignIconFor = (token) => {
					if (token === 'left') return '≡';
					if (token === 'center') return '≣';
					if (token === 'right') return '≡';
					return '';
				};

				const decorateAlignmentButtonGroups = () => {
					document.querySelectorAll('.acf-button-group label').forEach((label) => {
						if (!label) return;

						const input = label.querySelector('input[type="radio"]');
						const textNode = Array.from(label.childNodes).find((n) => n.nodeType === Node.TEXT_NODE);
						const rawText = textNode ? textNode.textContent : label.textContent;
						const token = normalizeAlignToken(rawText);

						/* Solo iconizar grupos de alineación (left/center/right o L/C/R) */
						if (!token) return;
						if (!input) return;

						label.classList.add('fiflp-align-iconized');
						label.setAttribute('title', token === 'left' ? 'Izquierda' : (token === 'center' ? 'Centro' : 'Derecha'));
						label.setAttribute('aria-label', label.getAttribute('title'));

						let icon = label.querySelector('.fiflp-align-icon');
						if (!icon) {
							icon = document.createElement('span');
							icon.className = 'fiflp-align-icon';
							label.appendChild(icon);
						}

						/* Usamos glifos simples y robustos en admin */
						icon.textContent = alignIconFor(token);

						/* Ajuste visual: izquierda/derecha con marca lateral */
						if (token === 'left') icon.style.justifyContent = 'flex-start';
						if (token === 'center') icon.style.justifyContent = 'center';
						if (token === 'right') icon.style.justifyContent = 'flex-end';
						icon.style.padding = '0 10px';
					});
				};

				const wireAcfHelpTooltips = () => {
					document.querySelectorAll('.acf-field').forEach((field) => {
						const desc = field.querySelector('.acf-label .description');
						const label = field.querySelector('.acf-label label');

						if (!desc || !label) {
							return;
						}

						const text = (desc.textContent || '').replace(/\s+/g, ' ').trim();
						if (!text) {
							return;
						}

						label.setAttribute('data-fiflp-help', text);
						label.setAttribute('tabindex', '0');

						if (!label.querySelector('.acf-label-with-help')) {
							const hint = document.createElement('span');
							hint.className = 'acf-label-with-help';
							hint.setAttribute('data-fiflp-help', text);
							hint.setAttribute('aria-hidden', 'true');
							hint.textContent = ' ?';
							hint.style.fontWeight = '700';
							hint.style.opacity = '0.72';
							hint.style.marginLeft = '4px';
							label.appendChild(hint);
						}
					});
				};

				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', function () {
						wireAcfHelpTooltips();
						decorateAlignmentButtonGroups();
					});
				} else {
					wireAcfHelpTooltips();
					decorateAlignmentButtonGroups();
				}

				document.addEventListener('acf/setup_fields', wireAcfHelpTooltips);
				document.addEventListener('acf/setup_fields', decorateAlignmentButtonGroups);

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

				const readRangeValue = (layout, name) => {
					const field = layout.querySelector('.acf-field[data-name="' + name + '"]');
					if (!field) {
						return 0;
					}

					const rangeInput = field.querySelector('input[type="range"]');
					const numberInput = field.querySelector('input[type="number"]');
					const raw = rangeInput ? rangeInput.value : (numberInput ? numberInput.value : '0');
					const value = Number(raw);

					if (!Number.isFinite(value)) {
						return 0;
					}

					return Math.max(-100, Math.min(100, value));
				};

				const buildToneFilter = (shadows, mids, highlights) => {
					const brightness = 1 + ((mids + (shadows * 0.35) + (highlights * 0.25)) * 0.003);
					const contrast = 1 + ((highlights - shadows) * 0.002);
					return 'brightness(' + brightness.toFixed(4) + ') contrast(' + contrast.toFixed(4) + ')';
				};

				const getRangeField = (layout, name) => layout.querySelector('.acf-field[data-name="' + name + '"]');

				const setRangeFieldValue = (field, value) => {
					if (!field) {
						return;
					}

					const safeValue = Math.max(-100, Math.min(100, Number(value) || 0));
					const rangeInput = field.querySelector('input[type="range"]');
					const numberInput = field.querySelector('input[type="number"]');

					if (rangeInput) {
						rangeInput.value = String(safeValue);
						rangeInput.dispatchEvent(new Event('input', { bubbles: true }));
						rangeInput.dispatchEvent(new Event('change', { bubbles: true }));
					}

					if (numberInput) {
						numberInput.value = String(safeValue);
						numberInput.dispatchEvent(new Event('input', { bubbles: true }));
						numberInput.dispatchEvent(new Event('change', { bubbles: true }));
					}
				};

				const toneModal = (() => {
					let currentLayout = null;
					let modal = null;
					let previewImg = null;
					let inputShadows = null;
					let inputMids = null;
					let inputHighlights = null;
					let outShadows = null;
					let outMids = null;
					let outHighlights = null;
					const mediaUrlCache = new Map();

					const getLargestFromSrcset = (srcset) => {
						if (!srcset || 'string' !== typeof srcset) {
							return '';
						}

						const candidates = srcset
							.split(',')
							.map((item) => item.trim())
							.filter(Boolean)
							.map((item) => {
								const parts = item.split(/\s+/);
								const url = parts[0] || '';
								const widthToken = parts.find((part) => /w$/.test(part)) || '';
								const width = parseInt(widthToken.replace('w', ''), 10);
								return {
									url,
									width: Number.isFinite(width) ? width : 0,
								};
							})
							.filter((item) => item.url);

						if (!candidates.length) {
							return '';
						}

						candidates.sort((a, b) => b.width - a.width);
						return candidates[0].url || '';
					};

					const getFieldImageMeta = (layout) => {
						const imageField = layout.querySelector('.acf-field[data-name="imagen"]');
						if (!imageField) {
							return { id: '', previewSrc: '', fullSrcCandidate: '', directUrl: '', alt: '' };
						}

						const hidden = imageField.querySelector('input[type="hidden"]');
						const img = imageField.querySelector('.acf-image-uploader .image-wrap img');
						const uploader = imageField.querySelector('.acf-image-uploader');
						const largestSrcset = img ? getLargestFromSrcset(img.getAttribute('srcset') || '') : '';
						const dataId = uploader && uploader.getAttribute('data-id') ? String(uploader.getAttribute('data-id')).trim() : '';
						const hiddenValue = hidden ? String(hidden.value || '').trim() : '';
						const directUrl = /^(https?:)?\/\//.test(hiddenValue) || hiddenValue.startsWith('/')
							? hiddenValue
							: '';

						return {
							id: dataId || (hidden ? String(hidden.value || '').trim() : ''),
							previewSrc: img ? (img.currentSrc || img.src || '') : '',
							fullSrcCandidate: largestSrcset,
							directUrl,
							alt: img ? (img.alt || '') : '',
						};
					};

					const resolveFullImageUrl = async (attachmentId, fallbackUrl) => {
						if (!attachmentId || !/^\d+$/.test(attachmentId)) {
							try {
								const params = new URLSearchParams({
									action: 'fiflp_get_original_image_url',
									url: fallbackUrl || '',
								});
								const response = await fetch('/wp-admin/admin-ajax.php?' + params.toString(), { credentials: 'same-origin' });
								if (!response.ok) {
									return fallbackUrl;
								}
								const data = await response.json();
								if (data && data.success && data.data && data.data.url) {
									return String(data.data.url);
								}
							} catch (error) {}
							return fallbackUrl;
						}

						if (mediaUrlCache.has(attachmentId)) {
							return mediaUrlCache.get(attachmentId) || fallbackUrl;
						}

						try {
							const params = new URLSearchParams({
								action: 'fiflp_get_original_image_url',
								id: attachmentId,
								url: fallbackUrl || '',
							});
							const response = await fetch('/wp-admin/admin-ajax.php?' + params.toString(), { credentials: 'same-origin' });
							if (!response.ok) {
								mediaUrlCache.set(attachmentId, fallbackUrl);
								return fallbackUrl;
							}

							const data = await response.json();
							const fullUrl = (data && data.success && data.data && data.data.url) ? String(data.data.url) : fallbackUrl;
							mediaUrlCache.set(attachmentId, fullUrl);
							return fullUrl || fallbackUrl;
						} catch (error) {
							mediaUrlCache.set(attachmentId, fallbackUrl);
							return fallbackUrl;
						}
					};

					const syncOutputs = () => {
						const shadows = Number(inputShadows.value || 0);
						const mids = Number(inputMids.value || 0);
						const highlights = Number(inputHighlights.value || 0);

						outShadows.textContent = String(shadows);
						outMids.textContent = String(mids);
						outHighlights.textContent = String(highlights);
						previewImg.style.filter = buildToneFilter(shadows, mids, highlights);
					};

					const close = () => {
						if (modal) {
							modal.classList.remove('is-open');
						}
						currentLayout = null;
					};

					const ensure = () => {
						if (modal) {
							return;
						}

						const wrapper = document.createElement('div');
						wrapper.className = 'fiflp-imagen-tono-modal';
						wrapper.innerHTML = `
							<div class="fiflp-imagen-tono-modal__dialog" role="dialog" aria-modal="true" aria-label="Ajustar imagen">
								<div class="fiflp-imagen-tono-modal__preview-wrap">
									<img class="fiflp-imagen-tono-modal__preview" src="" alt="">
								</div>
								<div class="fiflp-imagen-tono-modal__controls">
									<div class="fiflp-imagen-tono-modal__control">
										<label>Sombras: <strong data-tono-out="sombras">0</strong></label>
										<input type="range" min="-100" max="100" step="1" value="0" data-tono-input="sombras">
									</div>
									<div class="fiflp-imagen-tono-modal__control">
										<label>Medios tonos: <strong data-tono-out="medios">0</strong></label>
										<input type="range" min="-100" max="100" step="1" value="0" data-tono-input="medios">
									</div>
									<div class="fiflp-imagen-tono-modal__control">
										<label>Altas luces: <strong data-tono-out="luces">0</strong></label>
										<input type="range" min="-100" max="100" step="1" value="0" data-tono-input="luces">
									</div>
									<div class="fiflp-imagen-tono-modal__actions">
										<button type="button" class="button fiflp-btn-unificado" data-tono-action="cancelar">Cancelar</button>
										<button type="button" class="button fiflp-btn-unificado" data-tono-action="guardar">Guardar</button>
									</div>
								</div>
							</div>
						`;

						document.body.appendChild(wrapper);
						modal = wrapper;
						previewImg = modal.querySelector('.fiflp-imagen-tono-modal__preview');
						inputShadows = modal.querySelector('[data-tono-input="sombras"]');
						inputMids = modal.querySelector('[data-tono-input="medios"]');
						inputHighlights = modal.querySelector('[data-tono-input="luces"]');
						outShadows = modal.querySelector('[data-tono-out="sombras"]');
						outMids = modal.querySelector('[data-tono-out="medios"]');
						outHighlights = modal.querySelector('[data-tono-out="luces"]');

						[inputShadows, inputMids, inputHighlights].forEach((input) => {
							input.addEventListener('input', syncOutputs);
						});

						modal.addEventListener('click', (event) => {
							if (event.target === modal || event.target.closest('[data-tono-action="cancelar"]')) {
								close();
								return;
							}

							if (event.target.closest('[data-tono-action="guardar"]')) {
								if (!currentLayout) {
									close();
									return;
								}

								setRangeFieldValue(getRangeField(currentLayout, 'ajuste_sombras_imagen'), inputShadows.value);
								setRangeFieldValue(getRangeField(currentLayout, 'ajuste_medios_imagen'), inputMids.value);
								setRangeFieldValue(getRangeField(currentLayout, 'ajuste_luces_imagen'), inputHighlights.value);
								updatePreview(currentLayout);
								close();
							}
						});
					};

					return {
						async open(layout) {
							ensure();
							currentLayout = layout;

							const imageMeta = getFieldImageMeta(layout);
							previewImg.src = imageMeta.directUrl || imageMeta.fullSrcCandidate || imageMeta.previewSrc || '';
							previewImg.alt = imageMeta.alt || '';

							inputShadows.value = String(readRangeValue(layout, 'ajuste_sombras_imagen'));
							inputMids.value = String(readRangeValue(layout, 'ajuste_medios_imagen'));
							inputHighlights.value = String(readRangeValue(layout, 'ajuste_luces_imagen'));
							syncOutputs();

							modal.classList.add('is-open');

							const fullUrl = await resolveFullImageUrl(
								imageMeta.id,
								imageMeta.directUrl || imageMeta.fullSrcCandidate || imageMeta.previewSrc
							);
							if (currentLayout === layout && fullUrl) {
								previewImg.src = fullUrl;
							}
						}
					};
				})();

				const ensureToneButton = (layout) => {
					const imageField = layout.querySelector('.acf-field[data-name="imagen"]');
					if (!imageField || imageField.querySelector('.fiflp-imagen-tono-launch')) {
						return;
					}

					const button = document.createElement('button');
					button.type = 'button';
					button.className = 'button fiflp-imagen-tono-launch fiflp-btn-unificado';
					button.textContent = 'Ajustar imagen';
					button.addEventListener('click', () => toneModal.open(layout));
					imageField.appendChild(button);
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
					const shadows = readRangeValue(layout, 'ajuste_sombras_imagen');
					const mids = readRangeValue(layout, 'ajuste_medios_imagen');
					const highlights = readRangeValue(layout, 'ajuste_luces_imagen');

					const titleNode = preview.querySelector('[data-preview-title]');
					const captionNode = preview.querySelector('[data-preview-caption]');
					const imagePreview = layout.querySelector('.acf-field[data-name="imagen"] .acf-image-uploader .image-wrap img');

					if (titleNode) {
						titleNode.textContent = title;
						titleNode.style.borderColor = color;
						titleNode.style.color = 'relleno' === variante ? '#fcfcf8' : color;
						titleNode.style.background = 'relleno' === variante ? color : 'transparent';
					}

					if (captionNode) {
						captionNode.textContent = caption;
					}

					if (imagePreview) {
						if (0 === shadows && 0 === mids && 0 === highlights) {
							imagePreview.style.filter = '';
						} else {
							imagePreview.style.filter = buildToneFilter(shadows, mids, highlights);
						}
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
					document.querySelectorAll('.layout[data-layout="imagen"]').forEach((layout) => {
						ensureToneButton(layout);
						updatePreview(layout);
					});
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
