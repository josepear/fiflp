<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

do_action( 'generate_before_main_content' );
?>

<main id="primary" class="site-main plantilla-editorial">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'pagina-editorial' ); ?>>
			<?php do_action( 'generate_before_content' ); ?>

			<?php if ( function_exists( 'have_rows' ) && have_rows( 'bloques' ) ) : ?>
				<div class="pagina-editorial__bloques">
					<?php
					while ( have_rows( 'bloques' ) ) :
						the_row();

						switch ( get_row_layout() ) :

							case 'hero':
								$titulo      = get_sub_field( 'titulo' );
								$texto       = get_sub_field( 'texto' );
								$imagen      = get_sub_field( 'imagen' );
								$imagen_id   = ( is_array( $imagen ) && isset( $imagen['ID'] ) ) ? $imagen['ID'] : $imagen;
								?>
								<section class="bloque-hero">
									<?php if ( $imagen_id ) : ?>
										<div class="bloque-hero__media">
											<?php
											echo wp_get_attachment_image(
												$imagen_id,
												'full',
												false,
												array(
													'class' => 'bloque-hero__imagen',
												)
											);
											?>
										</div>
									<?php endif; ?>

									<div class="bloque-hero__contenido">
										<?php if ( $titulo ) : ?>
											<h1 class="bloque-hero__titulo"><?php echo esc_html( $titulo ); ?></h1>
										<?php endif; ?>

										<?php if ( $texto ) : ?>
											<div class="bloque-hero__texto">
												<?php echo apply_filters( 'the_content', $texto ); ?>
											</div>
										<?php endif; ?>

									</div>
								</section>
								<?php
								break;

							case 'texto':
								$contenido = get_sub_field( 'contenido' );
								if ( $contenido ) :
									?>
									<section class="bloque-texto">
										<div class="bloque-texto__contenido">
											<?php echo apply_filters( 'the_content', $contenido ); ?>
										</div>
									</section>
									<?php
								endif;
								break;

							case 'subtitulo':
								$titulo    = get_sub_field( 'titulo' );
								$contenido = get_sub_field( 'contenido' );
								if ( $titulo || $contenido ) :
									?>
									<section class="bloque-subtitulo">
										<?php if ( $titulo ) : ?>
											<h2 class="bloque-subtitulo__texto"><?php echo esc_html( $titulo ); ?></h2>
										<?php endif; ?>

										<?php if ( $contenido ) : ?>
											<div class="bloque-subtitulo__contenido">
												<?php echo apply_filters( 'the_content', $contenido ); ?>
											</div>
										<?php endif; ?>
									</section>
									<?php
								endif;
								break;

							case 'galeria':
								$imagenes = get_sub_field( 'imagenes' );
								if ( ! empty( $imagenes ) && is_array( $imagenes ) ) :
									?>
									<section class="bloque-galeria">
										<div class="bloque-galeria__grid">
											<?php foreach ( $imagenes as $imagen_item ) : ?>
												<?php
												$imagen_id = ( is_array( $imagen_item ) && isset( $imagen_item['ID'] ) ) ? $imagen_item['ID'] : $imagen_item;
												$pie       = ( is_array( $imagen_item ) && ! empty( $imagen_item['caption'] ) ) ? $imagen_item['caption'] : '';
												?>
												<figure class="bloque-galeria__item">
													<?php
													echo wp_get_attachment_image(
														$imagen_id,
														'large',
														false,
														array(
															'class' => 'bloque-galeria__imagen',
														)
													);
													?>

													<?php if ( $pie ) : ?>
														<figcaption class="bloque-galeria__pie"><?php echo esc_html( $pie ); ?></figcaption>
													<?php endif; ?>
												</figure>
											<?php endforeach; ?>
										</div>
									</section>
									<?php
								endif;
								break;

							case 'imagen_lateral':
								$imagen          = get_sub_field( 'imagen' );
								$contenido       = get_sub_field( 'texto' );
								$posicion_imagen = get_sub_field( 'posicion' );
								$imagen_id       = ( is_array( $imagen ) && isset( $imagen['ID'] ) ) ? $imagen['ID'] : $imagen;
								$clase_posicion  = ( 'derecha' === $posicion_imagen ) ? 'derecha' : 'izquierda';
								?>
								<section class="bloque-imagen-lateral bloque-imagen-lateral--<?php echo esc_attr( $clase_posicion ); ?>">
									<?php if ( $imagen_id ) : ?>
										<div class="bloque-imagen-lateral__media">
											<?php
											echo wp_get_attachment_image(
												$imagen_id,
												'large',
												false,
												array(
													'class' => 'bloque-imagen-lateral__imagen',
												)
											);
											?>
										</div>
									<?php endif; ?>

									<?php if ( $contenido ) : ?>
										<div class="bloque-imagen-lateral__contenido">
											<?php echo apply_filters( 'the_content', $contenido ); ?>
										</div>
									<?php endif; ?>
								</section>
								<?php
								break;

							case 'imagen_full':
								$imagen    = get_sub_field( 'imagen' );
								$pie       = get_sub_field( 'caption' );
								$imagen_id = ( is_array( $imagen ) && isset( $imagen['ID'] ) ) ? $imagen['ID'] : $imagen;
								if ( $imagen_id ) :
									?>
									<section class="bloque-imagen-full">
										<figure class="bloque-imagen-full__figure">
											<?php
											echo wp_get_attachment_image(
												$imagen_id,
												'full',
												false,
												array(
													'class' => 'bloque-imagen-full__imagen',
												)
											);
											?>

											<?php if ( $pie ) : ?>
												<figcaption class="bloque-imagen-full__pie"><?php echo esc_html( $pie ); ?></figcaption>
											<?php endif; ?>
										</figure>
									</section>
									<?php
								endif;
								break;

						endswitch;
					endwhile;
					?>
				</div>
			<?php else : ?>
				<header class="pagina-editorial__header">
					<h1 class="pagina-editorial__titulo"><?php the_title(); ?></h1>
				</header>

				<div class="pagina-editorial__contenido">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php do_action( 'generate_after_content' ); ?>
		</article>
	<?php endwhile; ?>
</main>

<?php
do_action( 'generate_after_main_content' );
get_footer();
