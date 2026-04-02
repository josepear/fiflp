<?php
/**
 * Bloque: Home Hero
 *
 * Requisito: template-parts/bloques/home-hero.php
 * Usa ACF flexible content con nombre de layout home_hero.
 */

// Campos estándar de ACF.
$imagen         = get_sub_field( 'imagen_de_fondo' ) ?: get_sub_field( 'imagen_fondo' );
$logo_principal = get_sub_field( 'logo_principal' );
$titulo         = get_sub_field( 'titulo' );
$texto          = get_sub_field( 'texto' );
$boton_capitulos_texto = trim( (string) get_sub_field( 'boton_capitulos_texto' ) );
$boton_capitulos_url   = trim( (string) get_sub_field( 'boton_capitulos_url' ) );
$link_pdf       = get_sub_field( 'link_pdf' );
$link_epub      = get_sub_field( 'link_epub' );
$logos          = get_sub_field( 'logos' );

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
if ( empty( $imagen ) && empty( $titulo ) && empty( $texto ) && ! $boton_capitulos_visible && ! $pdf_visible && ! $epub_visible ) {
	return;
}

$bg_style = '';
if ( ! empty( $imagen['url'] ) ) {
	$bg_style = 'style="background-image: url(' . esc_url( $imagen['url'] ) . ');"';
}
?>

<section class="home-hero" <?php echo $bg_style; ?> data-editorial-hero>
	<div class="home-hero-overlay"></div>
	<div class="home-hero-glow" aria-hidden="true"></div>

	<div class="home-hero-content" data-editorial-hero-content>
		<?php if ( ! empty( $logo_principal['url'] ) ) : ?>
			<div class="home-hero-main-logo home-hero__reveal home-hero__reveal--logo">
				<img src="<?php echo esc_url( $logo_principal['url'] ); ?>" alt="<?php echo esc_attr( $logo_principal['alt'] ?? '' ); ?>" />
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
				<?php if ( ! empty( $item['imagen']['url'] ) ) : ?>
					<div class="home-hero-logo">
						<img src="<?php echo esc_url( $item['imagen']['url'] ); ?>" alt="<?php echo esc_attr( $item['imagen']['alt'] ?? '' ); ?>" />
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
