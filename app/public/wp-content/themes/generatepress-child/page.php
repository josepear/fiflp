<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $bloque_index;
$bloque_index = 0;

$current_page_id = get_queried_object_id();
$parent_page_id  = wp_get_post_parent_id( $current_page_id );
$menu_parent_id  = $parent_page_id ? $parent_page_id : 0;

$menu_pages = get_pages(
	array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'parent'      => $menu_parent_id,
		'sort_column' => 'menu_order,post_title',
		'hierarchical'=> 0,
		'meta_key'    => 'bloques',
	)
);

if ( empty( $menu_pages ) ) {
	$current_page = get_post( $current_page_id );

	if ( $current_page instanceof WP_Post && 'page' === $current_page->post_type ) {
		$menu_pages = array( $current_page );
	}
}
?>

<div class="layout-editorial">
	<aside class="menu-lateral">
		<ul>
			<?php if ( $menu_pages ) : ?>
				<?php foreach ( $menu_pages as $menu_page ) : ?>
					<?php
					$item_classes = array(
						'page_item',
						'page-item-' . $menu_page->ID,
					);

					if ( (int) $menu_page->ID === (int) $current_page_id ) {
						$item_classes[] = 'current_page_item';
					}

					if ( $parent_page_id && (int) $menu_page->ID === (int) $parent_page_id ) {
						$item_classes[] = 'current_page_parent';
					}
					?>
					<li class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
						<a href="<?php echo esc_url( get_permalink( $menu_page ) ); ?>">
							<?php echo esc_html( get_the_title( $menu_page ) ); ?>
						</a>
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
			<?php endwhile; ?>
		<?php endif; ?>
	</main>
</div>

<?php get_footer(); ?>
