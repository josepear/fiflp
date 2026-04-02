<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $bloque_index;
$bloque_index = 0;

$current_page_id       = get_queried_object_id();
$parent_page_id        = wp_get_post_parent_id( $current_page_id );
$selected_prologo      = isset( $_GET['prologo'] ) ? max( 0, absint( wp_unslash( $_GET['prologo'] ) ) ) : 0;
$has_prologos_layout   = false;
$bloques_data          = function_exists( 'get_field' ) ? get_field( 'bloques', $current_page_id ) : array();
$prologo_items         = fiflp_collect_prologo_items_from_blocks( $bloques_data );
$is_prologos_page      = ! empty( $prologo_items );
$current_children = get_pages(
	array(
		'parent'      => $current_page_id,
		'sort_column' => 'menu_order',
	)
);
?>

<?php
// Si el primer bloque es home_hero Y estamos en la home, renderizamos solo el hero y detenemos todo el layout.
$skip_first_block = false;
if ( is_front_page() && function_exists( 'have_rows' ) && have_rows( 'bloques' ) ) {
	the_row();
	if ( 'home_hero' === get_row_layout() ) {
		get_template_part( 'template-parts/bloques/home-hero' );
		get_footer();
		return;
	} else {
		$skip_first_block = true;
	}
}
?>

<div class="layout-editorial">
	<?php get_template_part( 'template-parts/menu-lateral' ); ?>

	<main class="editorial">
		<?php if ( $is_prologos_page ) : ?>
			<?php
			$selected_index   = min( $selected_prologo, count( $prologo_items ) - 1 );
			$selected_item    = $prologo_items[ $selected_index ];
			$nombre           = $selected_item['nombre'] ?? '';
			$cargo            = $selected_item['cargo'] ?? '';
			$contenido        = $selected_item['contenido'] ?? '';
			$foto             = $selected_item['foto'] ?? null;
			$foto_url         = is_array( $foto ) ? ( $foto['url'] ?? '' ) : (string) $foto;
			$foto_alt         = is_array( $foto ) ? ( $foto['alt'] ?? $nombre ) : $nombre;
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
		<?php elseif ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php if ( function_exists( 'have_rows' ) && have_rows( 'bloques' ) ) : ?>
					<?php if ( $skip_first_block && ! $is_prologos_page ) : ?>
						<?php
						$layout      = (string) get_row_layout();

						if ( 'home_hero' === $layout && ! is_front_page() ) {
							// No renderizar home_hero en páginas no front
						} else {
							$template    = str_replace( '_', '-', $layout );
							$template_id = 'template-parts/bloques/' . $template;

							if ( locate_template( $template_id . '.php', false, false ) ) {
								get_template_part( $template_id );
							} elseif ( locate_template( 'template-parts/bloques/' . $layout . '.php', false, false ) ) {
								get_template_part( 'template-parts/bloques/' . $layout );
							}
						}
						?>
					<?php endif; ?>
					<?php while ( have_rows( 'bloques' ) ) : the_row(); ?>
						<?php
						$layout      = (string) get_row_layout();

						if ( 'home_hero' === $layout && ! is_front_page() ) {
							continue;
						}

						if ( 'capitulo' === $layout ) {
							continue;
						}

						if ( 'prologos' === $layout || 'prologo' === $layout ) {
							$has_prologos_layout = true;
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
