<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $bloque_index;
$bloque_index = 0;

$current_page_id      = get_queried_object_id();
$parent_page_id       = wp_get_post_parent_id( $current_page_id );
$selected_prologo     = isset( $_GET['prologo'] ) ? max( 0, absint( wp_unslash( $_GET['prologo'] ) ) ) : 0;
$has_prologos_layout  = false;
$bloques_data         = function_exists( 'get_field' ) ? get_field( 'bloques', $current_page_id ) : array();
$onepage_sections     = function_exists( 'fiflp_collect_onepage_nav_sections' )
	? fiflp_collect_onepage_nav_sections( is_array( $bloques_data ) ? $bloques_data : array() )
	: array();
$has_onepage_nav      = ! empty( $onepage_sections );
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
$portada_hero_ref_id = ( is_front_page() && function_exists( 'get_field' ) ) ? (int) get_field( 'portada_hero_referencia', $current_page_id ) : 0;

if ( is_front_page() && $portada_hero_ref_id <= 0 && is_array( $bloques_data ) ) {
	foreach ( $bloques_data as $bloque ) {
		if ( ! is_array( $bloque ) ) {
			continue;
		}
		$layout = isset( $bloque['acf_fc_layout'] ) ? (string) $bloque['acf_fc_layout'] : '';
		if ( 'portada_hero' !== $layout ) {
			continue;
		}
		if ( isset( $bloque['portada_hero'] ) && is_numeric( $bloque['portada_hero'] ) ) {
			$portada_hero_ref_id = (int) $bloque['portada_hero'];
		}
		break;
	}
}

$portada_hero_es_entrada = function_exists( 'fiflp_is_front_page_landing_gate_active' )
	? fiflp_is_front_page_landing_gate_active( $current_page_id )
	: false;

if ( is_front_page() && $portada_hero_ref_id > 0 ) {
	$portada_hero_es_entrada = true;
}

if ( $portada_hero_es_entrada && $portada_hero_ref_id > 0 ) {
	?><!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<?php wp_body_open(); ?>
		<?php get_template_part( 'template-parts/bloques/portada-hero', null, array( 'portada_hero_id' => $portada_hero_ref_id ) ); ?>
		<?php wp_footer(); ?>
	</body>
	</html>
	<?php
	return;
}

get_header();

if ( ! empty( $prologo_items ) ) {
	$selected_prologo_item = $prologo_items[ min( $selected_prologo, count( $prologo_items ) - 1 ) ];
}

?>

<?php if ( $portada_hero_ref_id > 0 ) : ?>
	<?php get_template_part( 'template-parts/bloques/portada-hero', null, array( 'portada_hero_id' => $portada_hero_ref_id ) ); ?>
<?php endif; ?>

<div class="layout-editorial<?php echo $has_onepage_nav ? ' layout-editorial--onepage' : ''; ?>"<?php echo $has_onepage_nav ? ' data-onepage-layout="1"' : ''; ?>>
	<?php if ( $has_onepage_nav ) : ?>
		<?php
		/*
		 * Onepage: solo índice de secciones (menu-onepage). No cargar menu-lateral.
		 */
		?>
		<?php get_template_part( 'template-parts/menu-onepage', null, array( 'sections' => $onepage_sections ) ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/menu-lateral' ); ?>
	<?php endif; ?>

	<main class="editorial">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php $rendered_selected_prologo = false; ?>
				<?php $prologo_offset = 0; ?>

				<?php if ( function_exists( 'have_rows' ) && have_rows( 'bloques' ) ) : ?>
					<?php while ( have_rows( 'bloques' ) ) : the_row(); ?>
						<?php
						$layout = (string) get_row_layout();

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
