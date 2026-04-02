<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $bloque_index;
$bloque_index = 0;
?>

<div class="layout-editorial">
	<aside class="menu-lateral">
		<ul>
			<?php if ( function_exists( 'have_rows' ) && have_rows( 'bloques' ) ) : ?>
				<?php while ( have_rows( 'bloques' ) ) : the_row(); ?>
					<?php if ( 'capitulo' === get_row_layout() ) : ?>
						<?php $titulo = get_sub_field( 'titulo' ); ?>
						<?php if ( $titulo ) : ?>
							<li>
								<a href="#<?php echo esc_attr( generatepress_child_editorial_anchor( $titulo ) ); ?>">
									<?php echo esc_html( $titulo ); ?>
								</a>
							</li>
						<?php endif; ?>
					<?php endif; ?>
				<?php endwhile; ?>
			<?php endif; ?>
		</ul>
	</aside>

	<main class="editorial">
		<?php if ( is_page( 'prueba' ) ) : ?>
			<?php get_template_part( 'template-parts/demo-rotulo-editorial' ); ?>
		<?php endif; ?>
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php if ( function_exists( 'have_rows' ) && have_rows( 'bloques' ) ) : ?>
					<?php while ( have_rows( 'bloques' ) ) : the_row(); ?>
						<?php get_template_part( 'template-parts/bloques/' . get_row_layout() ); ?>
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
