<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$seccion_ref = get_sub_field( 'seccion_onepage' );
$seccion_id  = 0;

if ( is_numeric( $seccion_ref ) ) {
	$seccion_id = (int) $seccion_ref;
} elseif ( is_array( $seccion_ref ) ) {
	$seccion_id = (int) ( $seccion_ref['ID'] ?? $seccion_ref['id'] ?? 0 );
} elseif ( $seccion_ref instanceof WP_Post ) {
	$seccion_id = (int) $seccion_ref->ID;
}

if ( $seccion_id <= 0 ) {
	return;
}

$numero   = trim( (string) get_field( 'numero_seccion', $seccion_id ) );
$titulo   = trim( (string) get_field( 'titulo_seccion', $seccion_id ) );
$fondo    = strtolower( trim( (string) get_field( 'fondo_color', $seccion_id ) ) );
$columnas = (string) get_field( 'columnas_texto_desktop', $seccion_id );
$items    = get_field( 'items_contenido', $seccion_id );

if ( '' === $titulo ) {
	$titulo = get_the_title( $seccion_id );
}

$palette = array( '#ea4142', '#fde25f', '#072728', '#e9e9e9', '#73c3b6' );
if ( ! in_array( $fondo, $palette, true ) ) {
	$fondo = '#e9e9e9';
}

if ( ! in_array( $columnas, array( '1', '2', '3' ), true ) ) {
	$columnas = '2';
}

if ( ! is_array( $items ) || empty( $items ) ) {
	return;
}

$onepage_row_index = function_exists( 'get_row_index' ) ? (int) get_row_index() : 0;
$onepage_anchor_id = $onepage_row_index > 0 ? 'fiflp-onepage-row-' . $onepage_row_index : '';
?>

<section class="bloque seccion-onepage seccion-onepage--fullscreen fade-in"<?php echo '' !== $onepage_anchor_id ? ' id="' . esc_attr( $onepage_anchor_id ) . '"' : ''; ?>>
	<div class="seccion-onepage__shell seccion-onepage__cols-<?php echo esc_attr( $columnas ); ?>" style="--onepage-bg: <?php echo esc_attr( $fondo ); ?>;" data-onepage-shell>
		<aside class="seccion-onepage__indice" aria-label="Índice de sección">
			<?php if ( '' !== $numero ) : ?>
				<p class="seccion-onepage__numero-wrap">
					<span class="seccion-onepage__numero seccion-onepage__numero--solid"><?php echo esc_html( $numero ); ?></span>
					<span class="seccion-onepage__numero seccion-onepage__numero--outline" aria-hidden="true"><?php echo esc_html( $numero ); ?></span>
				</p>
			<?php endif; ?>
			<h2 class="seccion-onepage__titulo"><?php echo esc_html( $titulo ); ?></h2>
		</aside>

		<div class="seccion-onepage__contenido-wrap">
			<div class="seccion-onepage__foto-stage" aria-hidden="true" data-onepage-photo-stage>
				<?php
				$stage_index = 0;
				foreach ( $items as $item_stage ) :
					$foto_stage = $item_stage['foto_vinculada'] ?? null;
					$foto_url_stage = '';
					$foto_alt_stage = '';

					if ( is_array( $foto_stage ) ) {
						$foto_url_stage = (string) ( $foto_stage['sizes']['large'] ?? $foto_stage['url'] ?? '' );
						$foto_alt_stage = (string) ( $foto_stage['alt'] ?? '' );
					} elseif ( is_string( $foto_stage ) ) {
						$foto_url_stage = trim( $foto_stage );
					}

					if ( '' === $foto_url_stage ) {
						continue;
					}
					?>
					<figure class="seccion-onepage__foto-stage-item<?php echo 0 === $stage_index ? ' is-active' : ''; ?>" data-onepage-photo="<?php echo esc_attr( (string) $stage_index ); ?>">
						<img src="<?php echo esc_url( $foto_url_stage ); ?>" alt="<?php echo esc_attr( $foto_alt_stage ); ?>" loading="lazy" decoding="async">
					</figure>
					<?php
					$stage_index++;
				endforeach;
				?>
			</div>

			<?php $stage_index = 0; ?>
			<div class="seccion-onepage__contenido" role="list">
			<?php foreach ( $items as $item ) : ?>
				<?php
				$texto         = $item['texto'] ?? '';
				$sumario       = trim( (string) ( $item['sumario'] ?? '' ) );
				$ladillo       = trim( (string) ( $item['ladillo'] ?? '' ) );
				$foto          = $item['foto_vinculada'] ?? null;
				$posicion_foto = (string) ( $item['posicion_foto'] ?? 'derecha_contenido' );

				if ( '' === trim( wp_strip_all_tags( (string) $texto ) ) && '' === $sumario && '' === $ladillo && empty( $foto ) ) {
					continue;
				}

				$foto_url = '';
				$foto_alt = '';

				if ( is_array( $foto ) ) {
					$foto_url = (string) ( $foto['sizes']['large'] ?? $foto['url'] ?? '' );
					$foto_alt = (string) ( $foto['alt'] ?? '' );
				} elseif ( is_string( $foto ) ) {
					$foto_url = trim( $foto );
				}

				if ( ! in_array( $posicion_foto, array( 'izquierda_titulo', 'derecha_contenido' ), true ) ) {
					$posicion_foto = 'derecha_contenido';
				}

				$item_classes = array( 'seccion-onepage__item', 'seccion-onepage__item--' . $posicion_foto );
				$photo_index  = '';
				if ( '' !== $foto_url ) {
					$item_classes[] = 'has-photo';
					$photo_index = (string) $stage_index;
					$stage_index++;
				}
				?>
				<article class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>" role="listitem"<?php echo '' !== $photo_index ? ' data-onepage-photo-index="' . esc_attr( $photo_index ) . '"' : ''; ?> data-onepage-item>
					<?php if ( '' !== $foto_url && 'izquierda_titulo' === $posicion_foto ) : ?>
						<figure class="seccion-onepage__foto seccion-onepage__foto--left">
							<img src="<?php echo esc_url( $foto_url ); ?>" alt="<?php echo esc_attr( $foto_alt ); ?>" loading="lazy" decoding="async">
						</figure>
					<?php endif; ?>

					<div class="seccion-onepage__texto-wrap">
						<?php if ( '' !== $sumario ) : ?>
							<p class="seccion-onepage__sumario"><?php echo esc_html( $sumario ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== trim( wp_strip_all_tags( (string) $texto ) ) ) : ?>
							<div class="seccion-onepage__texto">
								<?php echo wp_kses_post( $texto ); ?>
							</div>
						<?php endif; ?>

						<?php if ( '' !== $ladillo ) : ?>
							<p class="seccion-onepage__ladillo"><?php echo esc_html( $ladillo ); ?></p>
						<?php endif; ?>
					</div>

					<?php if ( '' !== $foto_url && 'derecha_contenido' === $posicion_foto ) : ?>
						<figure class="seccion-onepage__foto seccion-onepage__foto--right">
							<img src="<?php echo esc_url( $foto_url ); ?>" alt="<?php echo esc_attr( $foto_alt ); ?>" loading="lazy" decoding="async">
						</figure>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
