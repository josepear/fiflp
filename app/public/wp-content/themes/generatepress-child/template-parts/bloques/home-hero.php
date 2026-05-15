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
$video_fondo            = $using_direct_data ? (string) ( $hero_data['video'] ?? '' ) : (string) get_sub_field( 'video_fondo' );
$color_fondo            = $using_direct_data ? (string) ( $hero_data['color_fondo'] ?? '' ) : (string) get_sub_field( 'color_fondo' );
$logo_principal         = $using_direct_data ? ( $hero_data['logo_principal'] ?? null ) : get_sub_field( 'logo_principal' );
$titulo                 = $using_direct_data ? (string) ( $hero_data['titulo'] ?? '' ) : (string) get_sub_field( 'titulo' );
$texto                  = $using_direct_data ? (string) ( $hero_data['texto'] ?? '' ) : (string) get_sub_field( 'texto' );
$boton_capitulos_texto  = $using_direct_data ? trim( (string) ( $hero_data['boton_capitulos_texto'] ?? '' ) ) : trim( (string) get_sub_field( 'boton_capitulos_texto' ) );
$boton_capitulos_url    = $using_direct_data ? trim( (string) ( $hero_data['boton_capitulos_url'] ?? '' ) ) : trim( (string) get_sub_field( 'boton_capitulos_url' ) );
$boton_capitulos_url_libre = $using_direct_data ? trim( (string) ( $hero_data['boton_capitulos_url_libre'] ?? '' ) ) : trim( (string) get_sub_field( 'boton_capitulos_url_libre' ) );
$rotulo_titulo_lineas   = $using_direct_data ? ( $hero_data['rotulo_titulo_lineas'] ?? array() ) : get_sub_field( 'rotulo_titulo_lineas' );
$rotulo_etiqueta_html   = $using_direct_data ? trim( (string) ( $hero_data['rotulo_etiqueta_html'] ?? '' ) ) : trim( (string) get_sub_field( 'rotulo_etiqueta_html' ) );
$rotulo_interlineado    = $using_direct_data ? ( $hero_data['rotulo_interlineado'] ?? null ) : get_sub_field( 'rotulo_interlineado' );
$rotulo_espaciado_letras = $using_direct_data ? ( $hero_data['rotulo_espaciado_letras'] ?? null ) : get_sub_field( 'rotulo_espaciado_letras' );
$link_pdf               = $using_direct_data ? ( $hero_data['link_pdf'] ?? '' ) : get_sub_field( 'link_pdf' );
$link_epub              = $using_direct_data ? ( $hero_data['link_epub'] ?? '' ) : get_sub_field( 'link_epub' );
$logos                  = $using_direct_data ? ( $hero_data['logos'] ?? array() ) : get_sub_field( 'logos' );
$imagen_data    = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $imagen, 'full', (string) $titulo ) : array();
$logo_data      = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $logo_principal, 'full', get_bloginfo( 'name' ) ) : array();

if ( '' === $boton_capitulos_url ) {
	$boton_capitulos_url = '/prologos/';
}

if ( '' !== $boton_capitulos_url_libre ) {
	$boton_capitulos_url = $boton_capitulos_url_libre;
}

if ( '' === $boton_capitulos_texto ) {
	$boton_capitulos_texto = 'IR A LOS CAPÍTULOS';
}

// Asegurar que los botones solo aparecen con URLs válidas.
$boton_capitulos_visible = ! empty( $boton_capitulos_texto ) && ! empty( $boton_capitulos_url );
$pdf_visible   = ! empty( $link_pdf );
$epub_visible  = ! empty( $link_epub );
$rotulo_activo = is_array( $rotulo_titulo_lineas ) && ! empty( $rotulo_titulo_lineas );

// Si no hay datos mínimos, no renderiza.
$color_fondo = sanitize_hex_color( $color_fondo );

if ( empty( $imagen_data['url'] ) && empty( $video_fondo ) && empty( $color_fondo ) && empty( $logo_data['url'] ) && empty( $titulo ) && empty( $texto ) && ! $boton_capitulos_visible && ! $pdf_visible && ! $epub_visible && empty( $logos ) ) {
	return;
}

$hero_logo_items = array();
$hero_logo_row_1 = array();
$hero_logo_row_2 = array();
$hero_logo_auto  = array();

if ( is_array( $logos ) ) {
	foreach ( $logos as $item ) {
		$item_logo = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $item['imagen'] ?? null, 'full', '' ) : array();
		$item_svg  = function_exists( 'fiflp_get_svg_logo_markup' ) ? fiflp_get_svg_logo_markup( $item['imagen'] ?? null, array( 'class' => 'footer-editorial__partner-logo', 'alt' => $item_logo['alt'] ?? '' ) ) : '';
		$item_url  = isset( $item_logo['url'] ) ? (string) $item_logo['url'] : '';
		$item_alt  = isset( $item_logo['alt'] ) ? (string) $item_logo['alt'] : '';
		$item_row  = isset( $item['linea'] ) ? strtolower( trim( (string) $item['linea'] ) ) : '';

		if ( '' === $item_url && '' === $item_svg ) {
			continue;
		}

		$hero_logo_items[] = array(
			'url'   => $item_url,
			'alt'   => $item_alt,
			'svg'   => $item_svg,
			'linea' => $item_row,
		);
	}
}

foreach ( $hero_logo_items as $item ) {
	if ( in_array( $item['linea'], array( '1', 'linea_1', 'primera', 'first' ), true ) ) {
		$hero_logo_row_1[] = $item;
		continue;
	}
	if ( in_array( $item['linea'], array( '2', 'linea_2', 'segunda', 'second' ), true ) ) {
		$hero_logo_row_2[] = $item;
		continue;
	}
	$hero_logo_auto[] = $item;
}

foreach ( $hero_logo_auto as $item ) {
	if ( count( $hero_logo_row_1 ) < 6 ) {
		$hero_logo_row_1[] = $item;
		continue;
	}
	if ( count( $hero_logo_row_2 ) < 6 ) {
		$hero_logo_row_2[] = $item;
		continue;
	}
	break;
}

$hero_logo_row_1 = array_slice( $hero_logo_row_1, 0, 6 );
$hero_logo_row_2 = array_slice( $hero_logo_row_2, 0, 6 );
$hero_logo_rows  = array();
if ( ! empty( $hero_logo_row_1 ) ) {
	$hero_logo_rows[] = $hero_logo_row_1;
}
if ( ! empty( $hero_logo_row_2 ) ) {
	$hero_logo_rows[] = $hero_logo_row_2;
}

$bg_rules = array();

if ( ! empty( $color_fondo ) ) {
	$bg_rules[] = 'background-color: ' . $color_fondo;
}

if ( ! empty( $imagen_data['url'] ) ) {
	$bg_rules[] = 'background-image: url(' . esc_url_raw( $imagen_data['url'] ) . ')';
}

$bg_style = ! empty( $bg_rules ) ? 'style="' . esc_attr( implode( '; ', $bg_rules ) ) . ';"' : '';
?>

<section class="home-hero" <?php echo $bg_style; ?> data-editorial-hero>
	<?php if ( ! empty( $video_fondo ) ) : ?>
		<video class="home-hero__bg-video" autoplay muted loop playsinline preload="metadata" aria-hidden="true">
			<source src="<?php echo esc_url( $video_fondo ); ?>">
		</video>
	<?php endif; ?>
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

		<?php if ( ! empty( $titulo ) || $rotulo_activo ) : ?>
			<?php if ( $rotulo_activo ) : ?>
				<div class="home-hero-title home-hero-title--rotulo home-hero__reveal home-hero__reveal--title">
					<?php
					$hero_rotulo_module = array(
						'titulo' => $titulo,
						'titulo_lineas' => $rotulo_titulo_lineas,
						'etiqueta_html' => '' !== $rotulo_etiqueta_html ? $rotulo_etiqueta_html : 'h1',
						'interlineado' => $rotulo_interlineado,
						'espaciado_letras' => $rotulo_espaciado_letras,
					);
					get_template_part( 'template-parts/bloques/rotulo-editorial', null, array( 'module' => $hero_rotulo_module ) );
					?>
				</div>
			<?php else : ?>
				<h1 class="home-hero-title home-hero__reveal home-hero__reveal--title"><?php echo esc_html( $titulo ); ?></h1>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( ! empty( $texto ) ) : ?>
			<p class="home-hero__lead home-hero__reveal home-hero__reveal--text"><?php echo esc_html( $texto ); ?></p>
		<?php endif; ?>

		<?php if ( $boton_capitulos_visible ) : ?>
			<a class="home-hero__button home-hero__reveal home-hero__reveal--button" href="<?php echo esc_url( $boton_capitulos_url ); ?>"><span><?php echo esc_html( $boton_capitulos_texto ); ?></span></a>
		<?php endif; ?>

		<div class="home-hero__subactions home-hero__reveal home-hero__reveal--subactions">
			<?php if ( $pdf_visible ) : ?>
				<a class="home-hero__small-button" href="<?php echo esc_url( $link_pdf ); ?>"><span>Descargar PDF</span></a>
			<?php endif; ?>
			<?php if ( $epub_visible ) : ?>
				<a class="home-hero__small-button" href="<?php echo esc_url( $link_epub ); ?>"><span>Descargar EPUB</span></a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( ! empty( $hero_logo_rows ) ) : ?>
		<div class="home-hero-logos home-hero__reveal home-hero__reveal--logos footer-editorial__partners-grid">
			<?php foreach ( $hero_logo_rows as $row ) : ?>
				<div class="footer-editorial__partners-row" style="--footer-logo-cols: <?php echo esc_attr( (string) count( $row ) ); ?>;">
					<?php foreach ( $row as $item ) : ?>
						<div class="footer-editorial__partner home-hero-logo">
							<?php if ( $item['svg'] ) : ?>
								<?php echo $item['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<img src="<?php echo esc_url( $item['url'] ); ?>" alt="<?php echo esc_attr( $item['alt'] ); ?>" />
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
