# Documentación Técnica FIFLP

## 1. Resumen general del proyecto

La web de FIFLP está construida como una web editorial en WordPress con una capa visual y estructural personalizada sobre un tema hijo de GeneratePress. El objetivo del sistema no es usar un constructor visual ni una plantilla genérica de blog, sino montar una experiencia tipo libro o publicación editorial, con páginas que se comportan como capítulos o secciones, bloques de contenido controlados por ACF y una interfaz propia con menú lateral, lightbox y animaciones suaves.

La idea central del proyecto es esta:

- WordPress gestiona las páginas.
- GeneratePress aporta la base del tema padre y su compatibilidad general.
- El tema hijo `generatepress-child` define la lógica editorial real.
- ACF Flexible Content permite construir cada página mediante bloques.
- El CSS define un lenguaje visual editorial propio.
- `editorial.js` añade interacción ligera: lightbox, fades y animaciones del hero.

No hay builder visual, ni compilación con Vite/Webpack, ni framework JS. Todo está resuelto con PHP de tema, CSS editorial y JavaScript vanilla.

## 2. Infraestructura que sigue la web

### Stack principal

- CMS: WordPress
- Tema padre: GeneratePress
- Tema hijo: `generatepress-child`
- Gestión de contenido estructurado: ACF Flexible Content
- Frontend: PHP + HTML + CSS + JavaScript vanilla
- Tipografías externas: Google Fonts
- Entorno de trabajo previsto: LocalWP en macOS

### Infraestructura funcional

La infraestructura actual es sencilla y directa:

- WordPress renderiza el árbol de páginas y el contenido general.
- El tema hijo carga su CSS y JS sin build step.
- ACF expone el campo flexible `bloques` en páginas.
- `page.php` decide cómo recorrer esos bloques y qué partial PHP cargar.
- Cada layout de ACF se convierte en un archivo dentro de `template-parts/bloques/`.
- El menú lateral se genera desde las páginas WordPress y, en el caso de prólogos, también desde bloques ACF.
- El footer inyecta la estructura HTML del lightbox global.

### Infraestructura de assets

No hay pipeline de compilación. Los assets se sirven directamente desde el tema:

- CSS principal:
  `/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child/style.css`
- JS principal:
  `/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`

El versionado de CSS y JS se hace con `filemtime()`, así que al tocar archivos en local el navegador recibe versiones actualizadas sin depender de bundlers.

## 3. Cómo está construida técnicamente

## 3.1 Estructura del theme hijo

Ruta base:

`/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child`

Archivos principales:

- `functions.php`
- `page.php`
- `footer.php`
- `style.css`
- `assets/js/editorial.js`
- `template-parts/menu-lateral.php`
- `template-parts/bloques/*.php`
- `acf-json/group_bloques_editoriales.json`

## 3.2 Flujo de render

El flujo principal del contenido funciona así:

1. WordPress resuelve una página.
2. El tema hijo entra por `page.php`.
3. `page.php` obtiene el campo ACF `bloques`.
4. Si hay flexible content, recorre cada layout con `have_rows('bloques')`.
5. Según el layout, carga el partial correspondiente desde `template-parts/bloques/`.
6. Si la página contiene prólogos, `page.php` puede mostrar un solo prólogo según query param.
7. `template-parts/menu-lateral.php` genera la navegación lateral.
8. `footer.php` añade el marcado del lightbox.
9. `style.css` resuelve layout, responsive y lenguaje visual.
10. `editorial.js` añade interacciones progresivas.

## 3.3 Modelo editorial actual

El sistema mezcla dos estrategias editoriales:

- Páginas WordPress reales para la estructura general.
- Bloques ACF para contenido interno de cada página.

En el caso concreto de los prólogos, el sistema actual permite que el contenido esté dentro de ACF en una única página y se visualice uno a uno mediante query param, en vez de usar páginas hijas independientes.

Esto significa que ahora mismo la web tiene dos capas editoriales:

- Capa de páginas: capítulos, secciones, páginas top-level o hijas.
- Capa de bloques internos: texto, imagen, texto-imagen, hero, prólogos, etc.

## 4. Cómo se cargan los assets

La carga se hace en `functions.php`.

Qué se carga:

- stylesheet del tema padre (`parent-style`)
- Google Fonts (`editorial-fonts`)
- stylesheet del hijo (`child-style`)
- JavaScript principal (`editorial-js`)

Detalles técnicos:

- El CSS del padre se mantiene para no romper compatibilidad con GeneratePress.
- El CSS del hijo depende del padre y de las fuentes.
- El JS se carga en footer (`true`).
- Las versiones se calculan con `filemtime()` cuando el archivo existe.

## 5. Qué hace cada archivo importante

## 5.1 `functions.php`

Responsabilidades:

- definir utilidades para detectar páginas editoriales
- obtener hijos editoriales
- recoger prólogos desde ACF
- encolar CSS y JS

## 5.2 `page.php`

Responsabilidades:

- controlar el layout editorial principal
- cargar el menú lateral
- renderizar bloques ACF
- tratar el caso especial de `home_hero`
- tratar el caso especial de prólogos individualizados
- mostrar índice de hijos cuando corresponde

## 5.3 `template-parts/menu-lateral.php`

Responsabilidades:

- generar navegación lateral jerárquica de páginas
- detectar la rama actual
- abrir grupos activos
- inyectar hijos ACF cuando la página actual contiene prólogos
- marcar estados activos

## 5.4 `footer.php`

Responsabilidades:

- cerrar estructura abierta
- mantener hooks de GeneratePress
- añadir lightbox global reutilizable

## 5.5 `assets/js/editorial.js`

Responsabilidades:

- animar el hero de portada
- mover sutilmente el hero con el ratón
- abrir/cerrar lightbox
- activar `fade-in` con `IntersectionObserver`

## 5.6 `style.css`

Responsabilidades:

- tipografía base y sistema visual
- layout editorial de dos columnas
- menú lateral
- prólogos
- lightbox
- fade-in
- hero home
- responsive

## 6. Layouts ACF que existen ahora

Según `acf-json/group_bloques_editoriales.json`, el flexible content `bloques` tiene estos layouts principales:

- `hero`
- `texto`
- `imagen`
- `texto_imagen`
- `capitulo`
- `prologos`
- `home_hero`

### Campos relevantes por layout

#### `hero`
- `titulo`

#### `texto`
- `contenido`

#### `imagen`
- `imagen`
- `caption`
- `full`

#### `texto_imagen`
- `contenido`
- `imagen`

#### `capitulo`
- `titulo`

#### `prologos`
- `prologos` (repeater)
  - `nombre`
  - `cargo`
  - `contenido`
  - `foto`

#### `home_hero`
- `imagen_de_fondo`
- `titulo`
- `texto`
- `boton_capitulos_texto`
- `boton_capitulos_url`
- `link_pdf`
- `link_epub`
- `logo_principal`
- `logos` (repeater con `imagen`)

## 7. Infraestructura editorial real que sigue el proyecto

La infraestructura editorial del sitio, tal como está implementada, es esta:

- GeneratePress aporta base y compatibilidad.
- El hijo redefine `page.php` como punto central.
- ACF controla la composición del contenido.
- Los bloques visuales se separan en partials individuales.
- El menú lateral no depende solo de menús de WordPress, sino de la estructura de páginas y, en prólogos, también de ACF.
- La interacción es progresiva y ligera, sin framework.
- El diseño está pensado como una publicación larga, no como un blog estándar.

## 8. Riesgos y decisiones técnicas actuales

### Fortalezas

- Theme hijo claro y directo.
- Separación razonable por partials.
- ACF flexible para el equipo no técnico.
- Sin dependencias JS complejas.
- Compatible con GeneratePress.

### Puntos delicados

- `page.php` concentra bastante lógica editorial y lógica de prólogos.
- El sistema de prólogos está resuelto dentro de ACF, no como páginas reales, por lo que requiere manejo especial en menú y render.
- `style.css` es largo y ya contiene varias capas de evolución.
- Hay lógica duplicada de prólogos en `functions.php`, `page.php` y `menu-lateral.php`.

## 9. Mapa técnico del CSS

`style.css` tiene 1193 líneas y cubre estas zonas:

- base global
- bloques ACF
- hero general
- texto
- imagen
- texto-imagen
- responsive
- capítulo
- imagen full bleed
- layout editorial
- menú lateral
- estados activos del menú
- índice editorial
- prólogos
- lightbox
- fade-in
- home hero
- responsive del home hero

## 10. Código clave del proyecto

Debajo copio el código principal del theme actual para que quede reunido en un solo documento.

---

## 10.1 `functions.php`

Ruta:

`/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child/functions.php`

```php
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
```

---

## 10.2 `page.php`

Ruta:

`/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child/page.php`

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $bloque_index;
$bloque_index = 0;

$current_page_id      = get_queried_object_id();
$parent_page_id       = wp_get_post_parent_id( $current_page_id );
$selected_prologo     = isset( $_GET['prologo'] ) ? max( 0, absint( wp_unslash( $_GET['prologo'] ) ) ) : 0;
$has_prologos_layout  = false;
$bloques_data         = function_exists( 'get_field' ) ? get_field( 'bloques', $current_page_id ) : array();
$prologo_items        = fiflp_collect_prologo_items_from_blocks( $bloques_data );
$current_children     = get_pages(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'parent'      => $current_page_id,
		'sort_column' => 'menu_order,post_title',
		'hierarchical'=> 0,
	)
);
$selected_prologo_item = null;

if ( ! empty( $prologo_items ) ) {
	$selected_prologo_item = $prologo_items[ min( $selected_prologo, count( $prologo_items ) - 1 ) ];
}

if (
	is_front_page()
	&& ! empty( $bloques_data )
	&& isset( $bloques_data[0]['acf_fc_layout'] )
	&& 'home_hero' === $bloques_data[0]['acf_fc_layout']
	&& function_exists( 'have_rows' )
	&& have_rows( 'bloques' )
) {
	the_row();
	get_template_part( 'template-parts/bloques/home-hero' );
	get_footer();
	return;
}
?>

<div class="layout-editorial">
	<?php get_template_part( 'template-parts/menu-lateral' ); ?>

	<main class="editorial">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $rendered_selected_prologo = false; ?>
				<?php $prologo_offset = 0; ?>

				<?php if ( function_exists( 'have_rows' ) && have_rows( 'bloques' ) ) : ?>
					<?php while ( have_rows( 'bloques' ) ) : the_row(); ?>
						<?php
						$layout = (string) get_row_layout();

						if ( 'home_hero' === $layout && ! is_front_page() ) {
							continue;
						}

						if ( 'prologos' === $layout || 'prologo' === $layout ) {
							$has_prologos_layout = true;
							$current_row         = function_exists( 'get_row' ) ? get_row() : array();
							$local_items         = array();

							if ( is_array( $current_row ) ) {
								$current_row['acf_fc_layout'] = $layout;
								$local_items                  = fiflp_collect_prologo_items_from_blocks( array( $current_row ) );
							}

							$local_count = count( $local_items );

							if (
								$local_count > 0
								&& $selected_prologo >= $prologo_offset
								&& $selected_prologo < ( $prologo_offset + $local_count )
								&& ! $rendered_selected_prologo
							) {
								$selected_local_item = $local_items[ $selected_prologo - $prologo_offset ];
								$nombre              = $selected_local_item['nombre'] ?? '';
								$cargo               = $selected_local_item['cargo'] ?? '';
								$contenido           = $selected_local_item['contenido'] ?? '';
								$foto                = $selected_local_item['foto'] ?? null;
								$foto_url  = is_array( $foto ) ? ( $foto['url'] ?? '' ) : (string) $foto;
								$foto_alt  = is_array( $foto ) ? ( $foto['alt'] ?? $nombre ) : $nombre;
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
								$rendered_selected_prologo = true;
							}

							$prologo_offset += $local_count;

							continue;
						}

						$template    = str_replace( '_', '-', $layout );
						$template_id = 'template-parts/bloques/' . $template;

						if ( locate_template( $template_id . '.php', false, false ) ) {
							get_template_part( $template_id );
						} elseif ( locate_template( 'template-parts/bloques/' . $layout . '.php', false, false ) ) {
							get_template_part( 'template-parts/bloques/' . $layout );
						}
						?>
					<?php endwhile; ?>
				<?php else : ?>
					<section class="bloque texto fade-in">
						<?php the_content(); ?>
					</section>
				<?php endif; ?>

				<?php if ( $selected_prologo_item && ! $rendered_selected_prologo ) : ?>
					<?php
					$nombre    = $selected_prologo_item['nombre'] ?? '';
					$cargo     = $selected_prologo_item['cargo'] ?? '';
					$contenido = $selected_prologo_item['contenido'] ?? '';
					$foto      = $selected_prologo_item['foto'] ?? null;
					$foto_url  = is_array( $foto ) ? ( $foto['url'] ?? '' ) : (string) $foto;
					$foto_alt  = is_array( $foto ) ? ( $foto['alt'] ?? $nombre ) : $nombre;
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
				<?php endif; ?>

				<?php if ( ! $parent_page_id && $current_children && ! $has_prologos_layout ) : ?>
					<section class="bloque texto editorial-indice fade-in">
						<h2>Artículos</h2>
						<ul class="editorial-indice-list">
							<?php foreach ( $current_children as $child_page ) : ?>
								<li>
									<a href="<?php echo esc_url( get_permalink( $child_page ) ); ?>">
										<?php echo esc_html( get_the_title( $child_page ) ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			<?php endwhile; ?>
		<?php endif; ?>
	</main>
</div>

<?php get_footer(); ?>
```

---

## 10.3 `template-parts/menu-lateral.php`

Ruta:

`/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child/template-parts/menu-lateral.php`

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'fiflp_menu_get_prologo_label' ) ) {
	function fiflp_menu_get_prologo_label( $item, $index ) {
		if ( ! is_array( $item ) ) {
			return 'Prólogo ' . ( (int) $index + 1 );
		}

		$possible_keys = array(
			'nombre',
			'titulo',
			'nombre_prologuista',
		);

		foreach ( $possible_keys as $key ) {
			if ( ! isset( $item[ $key ] ) ) {
				continue;
			}

			$value = trim( wp_strip_all_tags( (string) $item[ $key ] ) );

			if ( '' !== $value ) {
				return $value;
			}
		}

		return 'Prólogo ' . ( (int) $index + 1 );
	}
}

if ( ! function_exists( 'fiflp_menu_collect_prologo_items' ) ) {
	function fiflp_menu_collect_prologo_items( $bloques ) {
		$items = array();

		if ( ! is_array( $bloques ) ) {
			return $items;
		}

		foreach ( $bloques as $bloque ) {
			$layout = isset( $bloque['acf_fc_layout'] ) ? (string) $bloque['acf_fc_layout'] : '';

			if ( 'prologo' === $layout ) {
				$index   = count( $items );
				$items[] = array(
					'index' => $index,
					'label' => fiflp_menu_get_prologo_label( $bloque, $index ),
				);
			}

			if ( 'prologos' === $layout && isset( $bloque['prologos'] ) && is_array( $bloque['prologos'] ) ) {
				foreach ( $bloque['prologos'] as $prologo ) {
					$index   = count( $items );
					$items[] = array(
						'index' => $index,
						'label' => fiflp_menu_get_prologo_label( $prologo, $index ),
					);
				}
			}
		}

		return $items;
	}
}

$current_id     = (int) get_the_ID();
$active_prologo = isset( $_GET['prologo'] ) ? max( 0, absint( wp_unslash( $_GET['prologo'] ) ) ) : 0;
$root_pages     = get_pages(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'parent'      => 0,
		'sort_column' => 'menu_order,post_title',
		'hierarchical'=> 0,
	)
);

if ( $current_id <= 0 ) {
	$current_id = (int) get_queried_object_id();
}

$current_parent_id = (int) wp_get_post_parent_id( $current_id );
$current_ancestors = array_map( 'intval', get_post_ancestors( $current_id ) );

$current_root_id = $current_id;

while ( $current_root_id > 0 && wp_get_post_parent_id( $current_root_id ) ) {
	$current_root_id = (int) wp_get_post_parent_id( $current_root_id );
}

if ( function_exists( 'generatepress_child_is_editorial_page' ) ) {
	$root_pages = array_values(
		array_filter(
			$root_pages,
			static function( $page ) use ( $current_root_id ) {
				return (int) $page->ID === $current_root_id || generatepress_child_is_editorial_page( $page );
			}
		)
	);
}

$render_menu_branch = function( $page, $level = 0 ) use ( &$render_menu_branch, $current_id, $current_parent_id, $current_ancestors, $active_prologo ) {
	if ( ! ( $page instanceof WP_Post ) ) {
		return;
	}

	$page_id       = (int) $page->ID;
	$is_current    = $page_id === $current_id;
	$is_parent     = $page_id === $current_parent_id;
	$is_ancestor   = in_array( $page_id, $current_ancestors, true );
	$children      = get_pages(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'parent'      => $page_id,
			'sort_column' => 'menu_order,post_title',
			'hierarchical'=> 0,
		)
	);
	$prologo_items = array();

	if ( $is_current && function_exists( 'get_field' ) ) {
		$prologo_items = fiflp_menu_collect_prologo_items( get_field( 'bloques', $page_id ) );
	}

	$item_classes = array(
		'page_item',
		'page-item-' . $page_id,
		'menu-item-level-' . (int) $level,
	);

	if ( $is_current ) {
		$item_classes[] = 'current_page_item';
	} elseif ( $is_parent ) {
		$item_classes[] = 'current_page_parent';
	} elseif ( $is_ancestor ) {
		$item_classes[] = 'current_page_ancestor';
	}

	$has_children = ! empty( $children ) || ! empty( $prologo_items );
	$is_open      = 0 === $level || $is_current || $is_parent || $is_ancestor;
	?>
	<li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
		<?php if ( $has_children ) : ?>
			<details class="menu-lateral-grupo"<?php echo $is_open ? ' open' : ''; ?>>
				<summary class="menu-lateral-summary">
					<a
						href="<?php echo esc_url( get_permalink( $page ) ); ?>"
						<?php if ( $is_current && empty( $prologo_items ) ) : ?>
							aria-current="page"
						<?php endif; ?>
					>
						<?php echo esc_html( get_the_title( $page ) ); ?>
					</a>
				</summary>

				<ul class="children">
					<?php foreach ( $children as $child_page ) : ?>
						<?php $render_menu_branch( $child_page, $level + 1 ); ?>
					<?php endforeach; ?>

					<?php if ( ! empty( $prologo_items ) ) : ?>
						<?php foreach ( $prologo_items as $item ) : ?>
							<?php
							$item_index = isset( $item['index'] ) ? (int) $item['index'] : 0;
							$item_label = isset( $item['label'] ) ? (string) $item['label'] : 'Prólogo ' . ( $item_index + 1 );
							?>
							<li class="page_item page-item-prologo-<?php echo esc_attr( $item_index ); ?> menu-item-level-<?php echo esc_attr( (string) ( $level + 1 ) ); ?><?php echo ( $item_index === $active_prologo ) ? ' current_page_item' : ''; ?>">
								<a
									href="<?php echo esc_url( add_query_arg( 'prologo', $item_index, get_permalink( $page ) ) ); ?>"
									<?php if ( $item_index === $active_prologo ) : ?>
										aria-current="page"
									<?php endif; ?>
								>
									<?php echo esc_html( $item_label ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</details>
		<?php else : ?>
			<a
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
};
?>

<aside class="menu-lateral" aria-label="Navegacion editorial">
	<ul class="menu-lateral-list level-0">
		<?php foreach ( $root_pages as $root_page ) : ?>
			<?php $render_menu_branch( $root_page ); ?>
		<?php endforeach; ?>
	</ul>
</aside>
```

---

## 10.4 `footer.php`

Ruta:

`/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child/footer.php`

```php
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	</div>
</div>

<?php do_action( 'generate_before_footer' ); ?>

<div <?php generate_do_attr( 'footer' ); ?>>
	<?php do_action( 'generate_before_footer_content' ); ?>
	<?php do_action( 'generate_footer' ); ?>
	<?php do_action( 'generate_after_footer_content' ); ?>
</div>

<?php do_action( 'generate_after_footer' ); ?>

<div id="lightbox" class="lightbox">
	<span class="lightbox-close">&times;</span>
	<img class="lightbox-img" src="" alt="">
	<p class="lightbox-caption"></p>
</div>

<?php wp_footer(); ?>

</body>
</html>
```

---

## 10.5 `assets/js/editorial.js`

Ruta:

`/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`

```js
/**
 * EDITORIAL JS
 * - Lightbox
 * - Animaciones
 */

document.addEventListener("DOMContentLoaded", function () {
    const homeHero = document.querySelector('[data-editorial-hero]');
    const homeHeroContent = document.querySelector('[data-editorial-hero-content]');

    if (homeHero) {
        const activateHero = () => {
            homeHero.classList.add('is-ready');

            window.setTimeout(() => {
                homeHero.classList.add('is-content-ready');
            }, 900);
        };

        if (document.readyState === 'complete') {
            requestAnimationFrame(activateHero);
        } else {
            window.addEventListener('load', activateHero, { once: true });
            requestAnimationFrame(activateHero);
        }

        if (homeHeroContent && window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
            window.addEventListener('mousemove', function (event) {
                const x = (event.clientX / window.innerWidth) - 0.5;
                const y = (event.clientY / window.innerHeight) - 0.5;

                homeHero.style.setProperty('--hero-pan-x', `${x * 18}px`);
                homeHero.style.setProperty('--hero-pan-y', `${y * 12}px`);
                homeHeroContent.style.setProperty('--hero-content-x', `${x * -10}px`);
                homeHeroContent.style.setProperty('--hero-content-y', `${y * -8}px`);
            }, { passive: true });
        }
    }

    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.querySelector('.lightbox-img');
    const lightboxCaption = document.querySelector('.lightbox-caption');
    const lightboxClose = document.querySelector('.lightbox-close');

    if (lightbox && lightboxImg && lightboxClose) {
        const openLightbox = (src, alt = '') => {
            if (!src) {
                return;
            }

            lightboxImg.src = src;
            lightboxImg.alt = alt;
            if (lightboxCaption) {
                lightboxCaption.textContent = alt;
            }
            lightbox.style.display = 'flex';
            lightbox.setAttribute('aria-hidden', 'false');
        };

        const closeLightbox = () => {
            lightbox.style.display = 'none';
            lightbox.setAttribute('aria-hidden', 'true');
            lightboxImg.setAttribute('src', '');
            lightboxImg.setAttribute('alt', '');
            if (lightboxCaption) {
                lightboxCaption.textContent = '';
            }
        };

        document.addEventListener('click', function(e) {
            const link = e.target.closest('.lightbox-trigger');

            if (!link) {
                return;
            }

            const src = link.getAttribute('href');
            const caption = link.getAttribute('data-caption') || '';

            e.preventDefault();
            openLightbox(src, caption);
        });

        lightboxClose.addEventListener('click', function() {
            closeLightbox();
        });

        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.style.display === 'flex') {
                closeLightbox();
            }
        });
    }

    const bloques = document.querySelectorAll('.fade-in');

    if (bloques.length) {
        const applyDelay = (bloque, index) => {
            bloque.style.transitionDelay = (index * 0.08) + 's';
        };

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1
            });

            bloques.forEach((bloque, index) => {
                observer.observe(bloque);
                applyDelay(bloque, index);
            });
        } else {
            bloques.forEach((bloque, index) => {
                bloque.classList.add('visible');
                applyDelay(bloque, index);
            });
        }
    }
});
```

---

## 10.6 `template-parts/bloques/hero.php`

```php
<?php
$titulo = get_sub_field( 'titulo' );

if ( ! $titulo ) {
	return;
}
?>

<section class="bloque hero fade-in">
	<h1><?php echo esc_html( $titulo ); ?></h1>
</section>
```

## 10.7 `template-parts/bloques/texto.php`

```php
<?php
$contenido = get_sub_field( 'contenido' );

if ( ! $contenido ) {
	return;
}
?>

<section class="bloque texto fade-in">
	<?php echo wp_kses_post( $contenido ); ?>
</section>
```

## 10.8 `template-parts/bloques/imagen.php`

```php
<?php
$imagen  = get_sub_field( 'imagen' );
$caption = get_sub_field( 'caption' );
$full    = get_sub_field( 'full' );

if ( ! $imagen ) {
	return;
}

$imagen_url = is_array( $imagen ) ? ( $imagen['url'] ?? '' ) : $imagen;
$imagen_alt = is_array( $imagen ) ? ( $imagen['alt'] ?? '' ) : '';

if ( ! $imagen_url ) {
	return;
}

$clases = array( 'bloque', 'imagen', 'fade-in' );

if ( $full ) {
	$clases[] = 'imagen-full';
}
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">
	<figure>
		<a href="<?php echo esc_url( $imagen_url ); ?>" class="lightbox-trigger" data-caption="<?php echo esc_attr( $caption ?? '' ); ?>">
			<img src="<?php echo esc_url( $imagen_url ); ?>" alt="<?php echo esc_attr( $imagen_alt ); ?>">
		</a>

		<?php if ( $caption ) : ?>
			<figcaption>
				<?php echo esc_html( $caption ); ?>
			</figcaption>
		<?php endif; ?>
	</figure>
</section>
```

## 10.9 `template-parts/bloques/texto-imagen.php`

```php
<?php
global $bloque_index;

if ( ! isset( $bloque_index ) ) {
	$bloque_index = 0;
}

$bloque_index++;

$contenido = get_sub_field( 'contenido' );
$imagen    = get_sub_field( 'imagen' );

if ( ! $contenido && ! $imagen ) {
	return;
}

$invertido = ( $bloque_index % 2 === 0 );
$clases    = array( 'bloque', 'texto-imagen', 'fade-in' );

if ( $invertido ) {
	$clases[] = 'invertido';
	$clases[] = 'derecha';
} else {
	$clases[] = 'izquierda';
}
?>

<section class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">
	<?php if ( $contenido ) : ?>
		<div class="col texto">
			<?php echo wp_kses_post( $contenido ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $imagen ) : ?>
		<div class="col imagen">
			<a href="<?php echo esc_url( $imagen ); ?>" class="lightbox-trigger">
				<img src="<?php echo esc_url( $imagen ); ?>" alt="">
			</a>
		</div>
	<?php endif; ?>
</section>
```

## 10.10 `template-parts/bloques/capitulo.php`

```php
<?php
$titulo = get_sub_field( 'titulo' );

if ( ! $titulo ) {
	return;
}
?>

<?php
static $capitulo_index = 0;
$capitulo_index++;
$cap_id = 'cap_' . intval( $capitulo_index );
?>

<section id="<?php echo esc_attr( $cap_id ); ?>" class="bloque capitulo fade-in">
	<h2 class="capitulo-titulo">
		<?php echo esc_html( $titulo ); ?>
	</h2>
</section>
```

## 10.11 `template-parts/bloques/prologos.php`

```php
<?php
$prologos = get_sub_field( 'prologos' );

if ( empty( $prologos ) || ! is_array( $prologos ) ) {
	return;
}
?>

<section class="bloque prologos fade-in">
	<?php foreach ( $prologos as $prologo ) : ?>
		<?php
		$nombre    = isset( $prologo['nombre'] ) ? trim( (string) $prologo['nombre'] ) : '';
		$cargo     = isset( $prologo['cargo'] ) ? trim( (string) $prologo['cargo'] ) : '';
		$contenido = $prologo['contenido'] ?? '';
		$foto      = $prologo['foto'] ?? null;
		$foto_url  = is_array( $foto ) ? ( $foto['url'] ?? '' ) : (string) $foto;
		$foto_alt  = is_array( $foto ) ? ( $foto['alt'] ?? $nombre ) : $nombre;

		if ( '' === $nombre && '' === $cargo && '' === $contenido && '' === $foto_url ) {
			continue;
		}
		?>

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
	<?php endforeach; ?>
</section>
```

## 10.12 `template-parts/bloques/home-hero.php`

```php
<?php
$imagen         = get_sub_field( 'imagen_de_fondo' ) ?: get_sub_field( 'imagen_fondo' );
$logo_principal = get_sub_field( 'logo_principal' );
$titulo         = get_sub_field( 'titulo' );
$texto          = get_sub_field( 'texto' );
$boton_capitulos_texto = trim( (string) get_sub_field( 'boton_capitulos_texto' ) );
$boton_capitulos_url   = trim( (string) get_sub_field( 'boton_capitulos_url' ) );
$link_pdf       = get_sub_field( 'link_pdf' );
$link_epub      = get_sub_field( 'link_epub' );
$logos          = get_sub_field( 'logos' );

if ( '' === $boton_capitulos_url ) {
	$boton_capitulos_url = home_url( '/prueba/' );
}

if ( '' === $boton_capitulos_texto ) {
	$boton_capitulos_texto = 'IR A LOS CAPÍTULOS';
}

$boton_capitulos_visible = ! empty( $boton_capitulos_texto ) && ! empty( $boton_capitulos_url );
$pdf_visible   = ! empty( $link_pdf );
$epub_visible  = ! empty( $link_epub );

if ( empty( $imagen ) && empty( $titulo ) && empty( $texto ) && ! $boton_capitulos_visible && ! $pdf_visible && ! $epub_visible ) {
	return;
}

$bg_style = '';
if ( ! empty( $imagen['url'] ) ) {
	$bg_style = 'style="background-image: url(' . esc_url( $imagen['url'] ) . ');"';
}
?>

<section class="home-hero" <?php echo $bg_style; ?> data-editorial-hero>
	<div class="home-hero-overlay"></div>
	<div class="home-hero-glow" aria-hidden="true"></div>

	<div class="home-hero-content" data-editorial-hero-content>
		<?php if ( ! empty( $logo_principal['url'] ) ) : ?>
			<div class="home-hero-main-logo home-hero__reveal home-hero__reveal--logo">
				<img src="<?php echo esc_url( $logo_principal['url'] ); ?>" alt="<?php echo esc_attr( $logo_principal['alt'] ?? '' ); ?>" />
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $titulo ) ) : ?>
			<h1 class="home-hero-title home-hero__reveal home-hero__reveal--title"><?php echo esc_html( $titulo ); ?></h1>
		<?php endif; ?>

		<?php if ( ! empty( $texto ) ) : ?>
			<p class="home-hero__lead home-hero__reveal home-hero__reveal--text"><?php echo esc_html( $texto ); ?></p>
		<?php endif; ?>

		<?php if ( $boton_capitulos_visible ) : ?>
			<a class="home-hero__button home-hero__reveal home-hero__reveal--button" href="<?php echo esc_url( $boton_capitulos_url ); ?>"><?php echo esc_html( $boton_capitulos_texto ); ?></a>
		<?php endif; ?>

		<div class="home-hero__subactions home-hero__reveal home-hero__reveal--subactions">
			<?php if ( $pdf_visible ) : ?>
				<a class="home-hero__small-button" href="<?php echo esc_url( $link_pdf ); ?>">Descargar PDF</a>
			<?php endif; ?>
			<?php if ( $epub_visible ) : ?>
				<a class="home-hero__small-button" href="<?php echo esc_url( $link_epub ); ?>">Descargar EPUB</a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( is_array( $logos ) && ! empty( $logos ) ) : ?>
		<div class="home-hero-logos home-hero__reveal home-hero__reveal--logos">
			<?php foreach ( $logos as $item ) : ?>
				<?php if ( ! empty( $item['imagen']['url'] ) ) : ?>
					<div class="home-hero-logo">
						<img src="<?php echo esc_url( $item['imagen']['url'] ); ?>" alt="<?php echo esc_attr( $item['imagen']['alt'] ?? '' ); ?>" />
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
```

---

## 10.13 Código CSS clave

El CSS completo está en:

`/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child/style.css`

Por tamaño no lo copio completo aquí, pero estos son los bloques de código más estructurales del sistema.

### Layout editorial

```css
.layout-editorial {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    gap: 64px;
    align-items: start;
    max-width: 1400px;
    margin: 0 auto;
    padding: 56px 32px 120px;
}
```

### Menú lateral

```css
.menu-lateral {
    position: sticky;
    top: 48px;
    align-self: start;
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    line-height: 1.35;
    letter-spacing: 0.01em;
    text-transform: uppercase;
}
```

### Prologos

```css
.prologo {
    display: grid;
    grid-template-columns: minmax(220px, 320px) minmax(0, 1fr);
    gap: 40px;
    align-items: start;
}
```

### Lightbox

```css
.lightbox {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.92);
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    padding: 40px 20px;
}
```

### Fade in

```css
.fade-in {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s ease, transform 0.8s ease;
}

.fade-in.visible {
    opacity: 1;
    transform: translateY(0);
}
```

### Home hero

```css
.home-hero__reveal {
    opacity: 0;
    transform: translate3d(0, 34px, 0);
    transition: opacity 1.35s ease, transform 1.35s cubic-bezier(0.22, 1, 0.36, 1);
}

.home-hero__reveal--logo {
    transition-delay: 0.12s;
}

.home-hero__reveal--title {
    transition-delay: 0.32s;
}

.home-hero__reveal--text {
    transition-delay: 0.54s;
}

.home-hero__reveal--button {
    transition-delay: 1.28s;
}

.home-hero__reveal--subactions {
    transition-delay: 1.56s;
}

.home-hero__reveal--logos {
    transition-delay: 1.82s;
}
```

---

## 10.14 ACF JSON

Archivo:

`/Volumes/RAID/Codex/proyectos/fiflp/app/public/wp-content/themes/generatepress-child/acf-json/group_bloques_editoriales.json`

Este archivo contiene la definición exportable del grupo `Bloques editoriales` y es la base del flexible content `bloques`.

## 11. Conclusión técnica

La web FIFLP está construida como un sistema editorial WordPress bastante artesanal y controlado, basado en tema hijo + ACF + partials PHP. La infraestructura es ligera, sin builders ni frameworks pesados, y la lógica real vive sobre todo en:

- `functions.php`
- `page.php`
- `template-parts/menu-lateral.php`
- `template-parts/bloques/*`
- `style.css`
- `assets/js/editorial.js`

El modelo actual está preparado para:

- páginas editoriales largas
- navegación lateral
- bloques reutilizables
- portada especial con hero animado
- imágenes ampliables con lightbox
- prólogos controlados desde ACF

Si más adelante quieres, el siguiente paso útil sería hacer una segunda documentación separada con:

- mapa exacto de todas las páginas del libro
- lista de campos ACF por bloque
- guía de edición para no developers
- checklist de mantenimiento
