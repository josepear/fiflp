<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_page_id       = get_queried_object_id();
$selected_prologo      = isset( $_GET['prologo'] ) ? max( 0, absint( wp_unslash( $_GET['prologo'] ) ) ) : 0;
$bloques_data          = function_exists( 'get_field' ) ? get_field( 'bloques', $current_page_id ) : array();
$prologo_items         = function_exists( 'fiflp_collect_prologo_items_from_blocks' ) ? fiflp_collect_prologo_items_from_blocks( $bloques_data ) : array();
$selected_prologo_item = null;

if ( ! empty( $prologo_items ) ) {
	$selected_prologo_item = $prologo_items[ min( $selected_prologo, count( $prologo_items ) - 1 ) ];
}
?>

<div class="layout-editorial">
	<?php get_template_part( 'template-parts/menu-lateral' ); ?>

	<main class="editorial editorial--prologos">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $rendered_selected_prologo = false; ?>
				<?php $prologo_offset = 0; ?>

				<?php if ( function_exists( 'have_rows' ) && have_rows( 'bloques' ) ) : ?>
					<?php while ( have_rows( 'bloques' ) ) : the_row(); ?>
						<?php
						$layout = (string) get_row_layout();

						if ( 'prologos' === $layout || 'prologo' === $layout ) {
							$current_row = function_exists( 'get_row' ) ? get_row() : array();
							$local_items = array();

							if ( is_array( $current_row ) && function_exists( 'fiflp_collect_prologo_items_from_blocks' ) ) {
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
								fiflp_render_single_prologo( $local_items[ $selected_prologo - $prologo_offset ] );
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
				<?php elseif ( empty( $prologo_items ) ) : ?>
					<section class="bloque texto editorial-prologos-empty fade-in">
						<h2 class="editorial-prologos-empty__title">Prólogos</h2>
						<p class="editorial-prologos-empty__text">Todavía no hay prólogos cargados en esta página.</p>
					</section>
				<?php endif; ?>

				<?php fiflp_render_editorial_pagination( $current_page_id, ! empty( $prologo_items ) ? $selected_prologo : null ); ?>
			<?php endwhile; ?>
		<?php endif; ?>
	</main>
</div>
