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

// Si el primer bloque es home_hero y estamos en la home, renderizamos solo ese bloque sin consumir filas por error.
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
