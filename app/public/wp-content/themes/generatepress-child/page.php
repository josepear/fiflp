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
$home_hero_data         = is_front_page() ? fiflp_get_home_hero_data( $current_page_id ) : array();
$home_hero_has_content  = ! empty( $home_hero_data['imagen'] )
	|| ! empty( $home_hero_data['logo_principal'] )
	|| ! empty( $home_hero_data['titulo'] )
	|| ! empty( $home_hero_data['texto'] )
	|| ! empty( $home_hero_data['boton_capitulos_texto'] )
	|| ! empty( $home_hero_data['boton_capitulos_url'] )
	|| ! empty( $home_hero_data['link_pdf'] )
	|| ! empty( $home_hero_data['link_epub'] )
	|| ! empty( $home_hero_data['logos'] );

if ( ! empty( $prologo_items ) ) {
	$selected_prologo_item = $prologo_items[ min( $selected_prologo, count( $prologo_items ) - 1 ) ];
}

if ( is_front_page() && $home_hero_has_content ) {
	get_template_part( 'template-parts/bloques/home-hero', null, array( 'hero_data' => $home_hero_data ) );
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
								fiflp_render_single_prologo( $selected_local_item );
								$rendered_selected_prologo = true;
							}

							$prologo_offset += $local_count;

							continue;
						}

						fiflp_render_editorial_block_layout( $layout );
						?>
					<?php endwhile; ?>
				<?php else : ?>
					<section class="bloque texto fade-in">
						<?php the_content(); ?>
					</section>
				<?php endif; ?>

				<?php if ( $selected_prologo_item && ! $rendered_selected_prologo ) : ?>
					<?php fiflp_render_single_prologo( $selected_prologo_item ); ?>
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

				<?php
				fiflp_render_editorial_pagination(
					$current_page_id,
					! empty( $prologo_items ) ? $selected_prologo : null
				);
				?>
			<?php endwhile; ?>
		<?php endif; ?>
	</main>
</div>

<?php get_footer(); ?>
