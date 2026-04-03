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
			<div class="menu-lateral-grupo<?php echo $is_open ? ' is-open' : ''; ?>">
				<div class="menu-lateral-summary">
					<a
						href="<?php echo esc_url( get_permalink( $page ) ); ?>"
						<?php if ( $is_current && empty( $prologo_items ) ) : ?>
							aria-current="page"
						<?php endif; ?>
					>
						<?php echo esc_html( get_the_title( $page ) ); ?>
					</a>
					<button class="menu-lateral-toggle" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $is_open ? 'Recoger' : 'Desplegar' ); ?>" data-disclosure-toggle>
						<span aria-hidden="true"><?php echo $is_open ? '−' : '+'; ?></span>
					</button>
				</div>

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
			</div>
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

<aside class="menu-lateral" aria-label="Navegacion editorial" data-mobile-nav>
	<button
		class="menu-lateral-mobile-toggle"
		type="button"
		aria-expanded="false"
		aria-controls="menu-lateral-panel"
		data-mobile-nav-toggle
	>
		<span class="menu-lateral-mobile-toggle__eyebrow">Indice</span>
		<span class="menu-lateral-mobile-toggle__title"><?php echo esc_html( get_the_title( $current_id ) ); ?></span>
		<span class="menu-lateral-mobile-toggle__icon" aria-hidden="true">+</span>
	</button>

	<div class="menu-lateral-mobile-panel" id="menu-lateral-panel" data-mobile-nav-panel>
		<ul class="menu-lateral-list level-0">
			<?php foreach ( $root_pages as $root_page ) : ?>
				<?php $render_menu_branch( $root_page ); ?>
			<?php endforeach; ?>
		</ul>
	</div>
</aside>
