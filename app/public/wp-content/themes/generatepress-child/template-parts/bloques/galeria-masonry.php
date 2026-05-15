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
	$alineacion_galeria = isset( $args['alineacion_galeria'] ) ? trim( (string) $args['alineacion_galeria'] ) : '';
	$caption_global = isset( $args['caption_global'] ) ? trim( (string) $args['caption_global'] ) : '';
	$imagenes_multiplicar = ! empty( $args['imagenes_multiplicar'] );
	$sin_redondeo = ! empty( $args['sin_redondeo'] );
	$context      = isset( $args['context'] ) ? trim( (string) $args['context'] ) : '';
} else {
	$imagenes     = $get_field( 'galeria_imagenes', array() );
	$columnas     = trim( (string) $get_field( 'columnas_masonry', '3' ) );
	$titulo       = trim( (string) $get_field( 'titulo', '' ) );
	$alineacion_galeria = trim( (string) $get_field( 'alineacion_galeria', '' ) );
	$caption_global = trim( (string) $get_field( 'caption_global', '' ) );
	$imagenes_multiplicar = (bool) $get_field( 'imagenes_multiplicar', false );
	$sin_redondeo = (bool) $get_field( 'sin_redondeo', false );
	$context      = '';
}

if ( ! is_array( $imagenes ) || empty( $imagenes ) ) {
	return;
}

if ( ! in_array( $columnas, array( '2', '3', '4' ), true ) ) {
	$columnas = '3';
}
if ( ! in_array( $alineacion_galeria, array( 'left', 'center', 'right' ), true ) ) {
	$alineacion_galeria = '';
}

$titulo_rotulo_style = '--rotulo-color:#0f2d30; --rotulo-text-color:#0f2d30;';

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

if ( $imagenes_multiplicar ) {
	$clases[] = 'galeria-masonry--multiply';
}

if ( ! empty( $args['onepage'] ) ) {
	$clases[] = 'galeria-masonry--onepage';
}
if ( '' !== $alineacion_galeria ) {
	$clases[] = 'galeria-masonry--align-' . $alineacion_galeria;
}
?>

<<?php echo esc_attr( $tag ); ?> class="<?php echo esc_attr( implode( ' ', $clases ) ); ?>">
	<?php if ( '' !== $titulo ) : ?>
		<div class="galeria-masonry__meta">
			<div class="rotulo-editorial rotulo-editorial--linea rotulo-editorial--tamano-m" style="<?php echo esc_attr( $titulo_rotulo_style ); ?>">
				<div class="rotulo-editorial__franja rotulo-editorial__franja--principal">
					<svg class="rotulo-editorial__marco" viewBox="0 0 106 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
						<polygon class="rotulo-editorial__marco-shape" points="7,2 106,2 100,98 1,98"></polygon>
					</svg>
					<p class="rotulo-editorial__texto rotulo-editorial__texto--principal"><?php echo esc_html( $titulo ); ?></p>
				</div>
			</div>
		</div>
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
			$img_multiply_class = $imagenes_multiplicar ? ' is-multiply' : '';
			$item_multiply_class = $imagenes_multiplicar ? ' galeria-masonry__item--multiply' : '';
			?>
			<figure class="galeria-masonry__item<?php echo esc_attr( $item_multiply_class ); ?>" role="listitem">
				<a href="<?php echo esc_url( $lightbox_url ); ?>" class="lightbox-trigger galeria-masonry__link<?php echo esc_attr( $img_multiply_class ); ?>" data-caption="<?php echo esc_attr( $caption ); ?>">
					<img class="galeria-masonry__img<?php echo esc_attr( $img_multiply_class ); ?>" src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy" decoding="async"<?php echo $dim_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				</a>
				<?php if ( '' !== $caption ) : ?>
					<figcaption class="galeria-masonry__caption imagen-meta__caption imagen-meta__caption--tamano-m imagen-meta__caption--tipografia-body"><?php echo esc_html( $caption ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
	<?php if ( '' !== $caption_global ) : ?>
		<div class="galeria-masonry__meta">
			<div class="galeria-masonry__caption-global imagen-meta__caption imagen-meta__caption--tamano-m imagen-meta__caption--tipografia-body"><?php echo esc_html( $caption_global ); ?></div>
		</div>
	<?php endif; ?>
</<?php echo esc_attr( $tag ); ?>>
