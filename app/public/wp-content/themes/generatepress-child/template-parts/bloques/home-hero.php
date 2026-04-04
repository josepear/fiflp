<?php
/**
 * Bloque: Home Hero
 *
 * Requisito: template-parts/bloques/home-hero.php
 * Usa ACF flexible content con nombre de layout home_hero.
 */

$hero_data = isset( $args['hero_data'] ) && is_array( $args['hero_data'] ) ? $args['hero_data'] : array();
$using_direct_data = ! empty( $hero_data );

$imagen                 = $using_direct_data ? ( $hero_data['imagen'] ?? null ) : ( get_sub_field( 'imagen_de_fondo' ) ?: get_sub_field( 'imagen_fondo' ) );
$logo_principal         = $using_direct_data ? ( $hero_data['logo_principal'] ?? null ) : get_sub_field( 'logo_principal' );
$titulo                 = $using_direct_data ? (string) ( $hero_data['titulo'] ?? '' ) : (string) get_sub_field( 'titulo' );
$texto                  = $using_direct_data ? (string) ( $hero_data['texto'] ?? '' ) : (string) get_sub_field( 'texto' );
$boton_capitulos_texto  = $using_direct_data ? trim( (string) ( $hero_data['boton_capitulos_texto'] ?? '' ) ) : trim( (string) get_sub_field( 'boton_capitulos_texto' ) );
$boton_capitulos_url    = $using_direct_data ? trim( (string) ( $hero_data['boton_capitulos_url'] ?? '' ) ) : trim( (string) get_sub_field( 'boton_capitulos_url' ) );
$link_pdf               = $using_direct_data ? ( $hero_data['link_pdf'] ?? '' ) : get_sub_field( 'link_pdf' );
$link_epub              = $using_direct_data ? ( $hero_data['link_epub'] ?? '' ) : get_sub_field( 'link_epub' );
$logos                  = $using_direct_data ? ( $hero_data['logos'] ?? array() ) : get_sub_field( 'logos' );
$imagen_data    = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $imagen, 'full', (string) $titulo ) : array();
$logo_data      = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $logo_principal, 'full', get_bloginfo( 'name' ) ) : array();

if ( '' === $boton_capitulos_url ) {
	$boton_capitulos_url = home_url( '/prueba/' );
}

if ( '' === $boton_capitulos_texto ) {
	$boton_capitulos_texto = 'IR A LOS CAPÍTULOS';
}

// Asegurar que los botones solo aparecen con URLs válidas.
$boton_capitulos_visible = ! empty( $boton_capitulos_texto ) && ! empty( $boton_capitulos_url );
$pdf_visible   = ! empty( $link_pdf );
$epub_visible  = ! empty( $link_epub );

// Si no hay datos mínimos, no renderiza.
if ( empty( $imagen_data['url'] ) && empty( $logo_data['url'] ) && empty( $titulo ) && empty( $texto ) && ! $boton_capitulos_visible && ! $pdf_visible && ! $epub_visible && empty( $logos ) ) {
	return;
}

$bg_style = '';
if ( ! empty( $imagen_data['url'] ) ) {
	$bg_style = 'style="background-image: url(' . esc_url( $imagen_data['url'] ) . ');"';
}
?>

<section class="home-hero" <?php echo $bg_style; ?> data-editorial-hero>
	<div class="home-hero-overlay"></div>
	<div class="home-hero-glow" aria-hidden="true"></div>

	<div class="home-hero-content" data-editorial-hero-content>
		<?php if ( ! empty( $logo_data['url'] ) ) : ?>
			<div class="home-hero-main-logo home-hero__reveal home-hero__reveal--logo">
				<?php $main_logo_svg = function_exists( 'fiflp_get_svg_logo_markup' ) ? fiflp_get_svg_logo_markup( $logo_principal, array( 'class' => 'home-hero-main-logo__svg', 'alt' => $logo_data['alt'] ?? '' ) ) : ''; ?>
				<?php if ( $main_logo_svg ) : ?>
					<?php echo $main_logo_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<img src="<?php echo esc_url( $logo_data['url'] ); ?>" alt="<?php echo esc_attr( $logo_data['alt'] ?? '' ); ?>" />
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $titulo ) ) : ?>
			<h1 class="home-hero-title home-hero__reveal home-hero__reveal--title"><?php echo esc_html( $titulo ); ?></h1>
		<?php endif; ?>

		<?php if ( ! empty( $texto ) ) : ?>
			<p class="home-hero__lead home-hero__reveal home-hero__reveal--text"><?php echo esc_html( $texto ); ?></p>
		<?php endif; ?>

		<?php if ( $boton_capitulos_visible ) : ?>
			<a class="home-hero__button home-hero__reveal home-hero__reveal--button" href="<?php echo esc_url( $boton_capitulos_url ); ?>"><?php echo esc_html( $boton_capitulos_texto ); ?></a>
		<?php endif; ?>

		<div class="home-hero__subactions home-hero__reveal home-hero__reveal--subactions">
			<?php if ( $pdf_visible ) : ?>
				<a class="home-hero__small-button" href="<?php echo esc_url( $link_pdf ); ?>">Descargar PDF</a>
			<?php endif; ?>
			<?php if ( $epub_visible ) : ?>
				<a class="home-hero__small-button" href="<?php echo esc_url( $link_epub ); ?>">Descargar EPUB</a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( is_array( $logos ) && ! empty( $logos ) ) : ?>
		<div class="home-hero-logos home-hero__reveal home-hero__reveal--logos">
			<?php foreach ( $logos as $item ) : ?>
				<?php
				$item_logo = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $item['imagen'] ?? null, 'full', '' ) : array();
				$item_svg  = function_exists( 'fiflp_get_svg_logo_markup' ) ? fiflp_get_svg_logo_markup( $item['imagen'] ?? null, array( 'class' => 'home-hero-logo__svg', 'alt' => $item_logo['alt'] ?? '' ) ) : '';
				?>
				<?php if ( ! empty( $item_logo['url'] ) ) : ?>
					<div class="home-hero-logo">
						<?php if ( $item_svg ) : ?>
							<?php echo $item_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<img src="<?php echo esc_url( $item_logo['url'] ); ?>" alt="<?php echo esc_attr( $item_logo['alt'] ?? '' ); ?>" />
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
