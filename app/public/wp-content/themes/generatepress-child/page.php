<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $bloque_index;
$bloque_index = 0;

$current_page_id = get_queried_object_id();
$parent_page_id  = wp_get_post_parent_id( $current_page_id );
$menu_pages       = generatepress_child_get_editorial_menu_pages( $current_page_id );
$current_children = generatepress_child_get_editorial_children( $current_page_id );
$current_ancestors = array_map( 'intval', get_post_ancestors( $current_page_id ) );
?>

<div class="layout-editorial">
	<aside class="menu-lateral">
		<ul>
			<?php if ( $menu_pages ) : ?>
				<?php foreach ( $menu_pages as $menu_page ) : ?>
					<?php
					$menu_children = generatepress_child_get_editorial_children( $menu_page->ID );
					$item_classes = array(
						'page_item',
						'page-item-' . $menu_page->ID,
					);

					if ( (int) $menu_page->ID === (int) $current_page_id ) {
						$item_classes[] = 'current_page_item';
					}

					if ( in_array( (int) $menu_page->ID, $current_ancestors, true ) ) {
						$item_classes[] = 'current_page_parent';
					}
					?>
					<li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
						<a href="<?php echo esc_url( get_permalink( $menu_page ) ); ?>">
							<?php echo esc_html( get_the_title( $menu_page ) ); ?>
						</a>

						<?php if ( $menu_children ) : ?>
							<ul class="children">
								<?php foreach ( $menu_children as $child_page ) : ?>
									<?php
									$child_classes = array(
										'page_item',
										'page-item-' . $child_page->ID,
									);

									if ( (int) $child_page->ID === (int) $current_page_id ) {
										$child_classes[] = 'current_page_item';
									}
									?>
									<li class="<?php echo esc_attr( implode( ' ', $child_classes ) ); ?>">
										<a href="<?php echo esc_url( get_permalink( $child_page ) ); ?>">
											<?php echo esc_html( get_the_title( $child_page ) ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
	</aside>

	<main class="editorial">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php if ( function_exists( 'have_rows' ) && have_rows( 'bloques' ) ) : ?>
					<?php while ( have_rows( 'bloques' ) ) : the_row(); ?>
						<?php
						$layout      = (string) get_row_layout();
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

				<?php if ( ! $parent_page_id && $current_children ) : ?>
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
