<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$portada_hero_id = 0;
if ( isset( $args['portada_hero_id'] ) ) {
	$portada_hero_id = (int) $args['portada_hero_id'];
} else {
	$sub_ref = function_exists( 'get_sub_field' ) ? get_sub_field( 'portada_hero' ) : null;
	if ( is_numeric( $sub_ref ) ) {
		$portada_hero_id = (int) $sub_ref;
	}
}

if ( $portada_hero_id <= 0 || 'fiflp_portada_hero' !== get_post_type( $portada_hero_id ) ) {
	return;
}

$get = static function ( $field ) use ( $portada_hero_id ) {
	return function_exists( 'get_field' ) ? get_field( $field, $portada_hero_id ) : null;
};

$desktop_tipo  = strtolower( trim( (string) $get( 'desktop_tipo_fondo' ) ) );
$tablet_tipo   = strtolower( trim( (string) $get( 'tablet_tipo_fondo' ) ) );
$mobile_tipo   = strtolower( trim( (string) $get( 'mobile_tipo_fondo' ) ) );
$desktop_img   = $get( 'desktop_imagen_fondo' );
$tablet_img    = $get( 'tablet_imagen_fondo' );
$mobile_img    = $get( 'mobile_imagen_fondo' );
$desktop_video = trim( (string) $get( 'desktop_video_fondo' ) );
$tablet_video  = trim( (string) $get( 'tablet_video_fondo' ) );
$mobile_video  = trim( (string) $get( 'mobile_video_fondo' ) );
$desktop_color = sanitize_hex_color( (string) $get( 'desktop_color_fondo' ) );
$tablet_color  = sanitize_hex_color( (string) $get( 'tablet_color_fondo' ) );
$mobile_color  = sanitize_hex_color( (string) $get( 'mobile_color_fondo' ) );

$logo_principal = $get( 'logo_principal' );
$logo_data      = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $logo_principal, 'full', get_the_title( $portada_hero_id ) ) : array();
$logo_url       = isset( $logo_data['url'] ) ? (string) $logo_data['url'] : '';
$logo_alt       = isset( $logo_data['alt'] ) ? (string) $logo_data['alt'] : get_the_title( $portada_hero_id );
$logo_svg       = function_exists( 'fiflp_get_svg_logo_markup' ) ? fiflp_get_svg_logo_markup( $logo_principal, array( 'class' => 'portada-hero__logo-svg', 'alt' => $logo_alt ) ) : '';

$rotulo_titulo              = trim( (string) $get( 'rotulo_titulo' ) );
$rotulo_supertitulo         = trim( (string) $get( 'rotulo_supertitulo' ) );
$rotulo_subtitulo           = trim( (string) $get( 'rotulo_subtitulo' ) );
$rotulo_variante_titulo     = trim( (string) $get( 'rotulo_variante_titulo' ) );
$rotulo_variante_supertitle = trim( (string) $get( 'rotulo_variante_supertitulo' ) );
$rotulo_tamano              = trim( (string) $get( 'rotulo_tamano' ) );
$rotulo_align               = trim( (string) $get( 'rotulo_alineacion_rotulo' ) );

$subtitulo_portada = trim( (string) $get( 'subtitulo_portada' ) );

$boton_central_texto = trim( (string) $get( 'boton_central_texto' ) );
$boton_central_url   = trim( (string) $get( 'boton_central_url' ) );
$destino_capitulos_id = (int) $get( 'destino_capitulos' );
$boton_pdf_texto     = trim( (string) $get( 'boton_pdf_texto' ) );
$boton_pdf_raw       = $get( 'boton_pdf_url' );
$boton_epub_texto    = trim( (string) $get( 'boton_epub_texto' ) );
$boton_epub_raw      = $get( 'boton_epub_url' );
$retaila_id          = (int) $get( 'retaila_logos_referencia' );

$img_url = static function ( $image ) {
	if ( is_array( $image ) ) {
		return isset( $image['url'] ) ? (string) $image['url'] : '';
	}
	return is_string( $image ) ? trim( $image ) : '';
};

$file_url = static function ( $file ) {
	if ( is_array( $file ) ) {
		return isset( $file['url'] ) ? trim( (string) $file['url'] ) : '';
	}
	return trim( (string) $file );
};

$desktop_img_url = $img_url( $desktop_img );
$tablet_img_url  = $img_url( $tablet_img );
$mobile_img_url  = $img_url( $mobile_img );

if ( '' === $tablet_img_url ) {
	$tablet_img_url = $desktop_img_url;
}
if ( '' === $mobile_img_url ) {
	$mobile_img_url = $tablet_img_url ? $tablet_img_url : $desktop_img_url;
}
if ( '' === $tablet_video ) {
	$tablet_video = $desktop_video;
}
if ( '' === $mobile_video ) {
	$mobile_video = $tablet_video ? $tablet_video : $desktop_video;
}
if ( '' === $tablet_color ) {
	$tablet_color = $desktop_color;
}
if ( '' === $mobile_color ) {
	$mobile_color = $tablet_color ? $tablet_color : $desktop_color;
}

$boton_pdf_url  = $file_url( $boton_pdf_raw );
$boton_epub_url = $file_url( $boton_epub_raw );

if ( $destino_capitulos_id > 0 ) {
	$destino_permalink = get_permalink( $destino_capitulos_id );
	if ( is_string( $destino_permalink ) && '' !== $destino_permalink ) {
		$boton_central_url = $destino_permalink;
	}
}

$rotulo_module = array(
	'titulo' => $rotulo_titulo,
	'supertitulo' => $rotulo_supertitulo,
	'subtitulo' => $rotulo_subtitulo,
	'variante_titulo' => $rotulo_variante_titulo,
	'variante_supertitulo' => $rotulo_variante_supertitle,
	'tamano' => $rotulo_tamano,
	'alineacion_rotulo' => $rotulo_align,
	'etiqueta_html' => 'h2',
);

$has_rotulo = '' !== $rotulo_titulo || '' !== $rotulo_supertitulo || '' !== $rotulo_subtitulo;
?>
<section
	class="portada-hero"
	style="--portada-hero-color-desktop: <?php echo esc_attr( $desktop_color ? $desktop_color : '#0f2d30' ); ?>; --portada-hero-color-tablet: <?php echo esc_attr( $tablet_color ? $tablet_color : '#0f2d30' ); ?>; --portada-hero-color-mobile: <?php echo esc_attr( $mobile_color ? $mobile_color : '#0f2d30' ); ?>;"
	data-bg-type-desktop="<?php echo esc_attr( 'video' === $desktop_tipo ? 'video' : 'imagen' ); ?>"
	data-bg-type-tablet="<?php echo esc_attr( 'video' === $tablet_tipo ? 'video' : 'imagen' ); ?>"
	data-bg-type-mobile="<?php echo esc_attr( 'video' === $mobile_tipo ? 'video' : 'imagen' ); ?>"
>
	<?php if ( '' !== $desktop_video || '' !== $tablet_video || '' !== $mobile_video ) : ?>
		<video class="portada-hero__video portada-hero__video--desktop" autoplay muted loop playsinline preload="metadata" <?php echo 'video' === $desktop_tipo && '' !== $desktop_video ? '' : 'hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( '' !== $desktop_video ) : ?><source src="<?php echo esc_url( $desktop_video ); ?>"><?php endif; ?>
		</video>
		<video class="portada-hero__video portada-hero__video--tablet" autoplay muted loop playsinline preload="metadata" <?php echo 'video' === $tablet_tipo && '' !== $tablet_video ? '' : 'hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( '' !== $tablet_video ) : ?><source src="<?php echo esc_url( $tablet_video ); ?>"><?php endif; ?>
		</video>
		<video class="portada-hero__video portada-hero__video--mobile" autoplay muted loop playsinline preload="metadata" <?php echo 'video' === $mobile_tipo && '' !== $mobile_video ? '' : 'hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php if ( '' !== $mobile_video ) : ?><source src="<?php echo esc_url( $mobile_video ); ?>"><?php endif; ?>
		</video>
	<?php endif; ?>

	<div class="portada-hero__bg portada-hero__bg--desktop" style="<?php echo esc_attr( '' !== $desktop_img_url ? 'background-image:url(' . esc_url_raw( $desktop_img_url ) . ');' : '' ); ?>"<?php echo 'imagen' === $desktop_tipo ? '' : ' hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></div>
	<div class="portada-hero__bg portada-hero__bg--tablet" style="<?php echo esc_attr( '' !== $tablet_img_url ? 'background-image:url(' . esc_url_raw( $tablet_img_url ) . ');' : '' ); ?>"<?php echo 'imagen' === $tablet_tipo ? '' : ' hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></div>
	<div class="portada-hero__bg portada-hero__bg--mobile" style="<?php echo esc_attr( '' !== $mobile_img_url ? 'background-image:url(' . esc_url_raw( $mobile_img_url ) . ');' : '' ); ?>"<?php echo 'imagen' === $mobile_tipo ? '' : ' hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></div>

	<div class="portada-hero__overlay"></div>
	<div class="portada-hero__content">
		<?php if ( $logo_svg || '' !== $logo_url ) : ?>
			<div class="portada-hero__logo">
				<?php if ( $logo_svg ) : ?>
					<?php echo $logo_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php else : ?>
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $logo_alt ); ?>">
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $has_rotulo ) : ?>
			<div class="portada-hero__rotulo">
				<?php get_template_part( 'template-parts/bloques/rotulo-editorial-page', null, array( 'module' => $rotulo_module ) ); ?>
			</div>
		<?php endif; ?>

		<?php if ( '' !== $subtitulo_portada ) : ?>
			<p class="portada-hero__subtitulo"><?php echo wp_kses_post( nl2br( esc_html( $subtitulo_portada ) ) ); ?></p>
		<?php endif; ?>

		<div class="portada-hero__acciones">
			<?php if ( '' !== $boton_central_texto && '' !== $boton_central_url ) : ?>
				<a class="portada-hero__boton portada-hero__boton--central" href="<?php echo esc_url( $boton_central_url ); ?>"><span><?php echo esc_html( $boton_central_texto ); ?></span></a>
			<?php endif; ?>
			<div class="portada-hero__acciones-sec">
				<?php if ( '' !== $boton_pdf_texto && '' !== $boton_pdf_url ) : ?>
					<a class="portada-hero__boton portada-hero__boton--sec" href="<?php echo esc_url( $boton_pdf_url ); ?>"><span><?php echo esc_html( $boton_pdf_texto ); ?></span></a>
				<?php endif; ?>
				<?php if ( '' !== $boton_epub_texto && '' !== $boton_epub_url ) : ?>
					<a class="portada-hero__boton portada-hero__boton--sec" href="<?php echo esc_url( $boton_epub_url ); ?>"><span><?php echo esc_html( $boton_epub_texto ); ?></span></a>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $retaila_id > 0 ) : ?>
			<?php get_template_part( 'template-parts/bloques/retaila-logos', null, array( 'retaila_id' => $retaila_id ) ); ?>
		<?php endif; ?>
	</div>
</section>
