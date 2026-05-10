<?php
/**
 * Galería estilo masonry (columnas CSS).
 * Uso: bloque editorial, módulo onepage, o hito de cronología (args explícitos).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = $args ?? array();

$get_field = static function ( $name, $default = null ) use ( $args ) {
	if ( function_exists( 'fiflp_get_sub_field_compat' ) ) {
		return fiflp_get_sub_field_compat( $name, $args, $default );
	}
	if ( function_exists( 'get_sub_field' ) ) {
		$v = get_sub_field( $name );
		return null !== $v ? $v : $default;
	}
	return $default;
};

$pass_through = isset( $args['imagenes'] ) && is_array( $args['imagenes'] );

if ( $pass_through ) {
	$imagenes     = $args['imagenes'];
	$columnas     = isset( $args['columnas'] ) ? trim( (string) $args['columnas'] ) : '3';
	$titulo       = isset( $args['titulo'] ) ? trim( (string) $args['titulo'] ) : '';
	$sin_redondeo = ! empty( $args['sin_redondeo'] );
	$context      = isset( $args['context'] ) ? trim( (string) $args['context'] ) : '';
} else {
	$imagenes     = $get_field( 'galeria_imagenes', array() );
	$columnas     = trim( (string) $get_field( 'columnas_masonry', '3' ) );
	$titulo       = trim( (string) $get_field( 'titulo', '' ) );
	$sin_redondeo = (bool) $get_field( 'sin_redondeo', false );
	$context      = '';
}

if ( ! is_array( $imagenes ) || empty( $imagenes ) ) {
	return;
}

if ( ! in_array( $columnas, array( '2', '3', '4' ), true ) ) {
	$columnas = '3';
}

$is_cronologia = ( 'cronologia' === $context );
$tag           = $is_cronologia ? 'div' : 'section';

$clases = array(
	'galeria-masonry',
	'galeria-masonry--cols-' . $columnas,
	'fade-in',
);

if ( ! $is_cronologia ) {
	$clases[] = 'bloque';
}

if ( $is_cronologia ) {
	$clases[] = 'cronologia-editorial__galeria-masonry';
}

if ( $sin_redondeo ) {
	$clases[] = 'galeria-masonry--sin-redondeo';
}

if ( ! empty( $args['onepage'] ) ) {
	$clases[] = 'galeria-masonry--onepage';
}
?>

<<?php echo esc_attr( $tag ); ?> class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">
	<?php if ( '' !== $titulo ) : ?>
		<h2 class="galeria-masonry__titulo"><?php echo esc_html( $titulo ); ?></h2>
	<?php endif; ?>

	<div class="galeria-masonry__grid" role="list">
		<?php foreach ( $imagenes as $img ) : ?>
			<?php
			if ( ! is_array( $img ) ) {
				continue;
			}
			$lightbox_url = (string) ( $img['url'] ?? '' );
			$src          = (string) ( $img['sizes']['large'] ?? $img['sizes']['medium_large'] ?? $lightbox_url );
			$alt          = (string) ( $img['alt'] ?? '' );
			$caption      = trim( (string) ( $img['caption'] ?? '' ) );
			if ( '' === $src ) {
				continue;
			}
			if ( '' === $lightbox_url ) {
				$lightbox_url = $src;
			}
			$w_attr = isset( $img['width'] ) ? max( 0, (int) $img['width'] ) : 0;
			$h_attr = isset( $img['height'] ) ? max( 0, (int) $img['height'] ) : 0;
			$dim_attr = ( $w_attr > 0 && $h_attr > 0 ) ? ' width="' . esc_attr( (string) $w_attr ) . '" height="' . esc_attr( (string) $h_attr ) . '"' : '';
			?>
			<figure class="galeria-masonry__item" role="listitem">
				<a href="<?php echo esc_url( $lightbox_url ); ?>" class="lightbox-trigger galeria-masonry__link" data-caption="<?php echo esc_attr( $caption ); ?>">
					<img class="galeria-masonry__img" src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy" decoding="async"<?php echo $dim_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				</a>
				<?php if ( '' !== $caption ) : ?>
					<figcaption class="galeria-masonry__caption"><?php echo esc_html( $caption ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
</<?php echo esc_attr( $tag ); ?>>
