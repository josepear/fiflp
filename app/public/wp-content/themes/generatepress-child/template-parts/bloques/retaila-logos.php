<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$retaila_id = isset( $args['retaila_id'] ) ? (int) $args['retaila_id'] : 0;
if ( $retaila_id <= 0 || 'fiflp_retaila_logos' !== get_post_type( $retaila_id ) ) {
	return;
}

$titulo = function_exists( 'get_field' ) ? trim( (string) get_field( 'retaila_titulo', $retaila_id ) ) : '';
$items  = function_exists( 'get_field' ) ? get_field( 'retaila_logos_items', $retaila_id ) : array();

if ( ! is_array( $items ) || empty( $items ) ) {
	return;
}

$logos = array();
foreach ( $items as $item ) {
	$logo   = $item['logo'] ?? null;
	$enlace = isset( $item['enlace'] ) ? trim( (string) $item['enlace'] ) : '';
	$nombre = isset( $item['nombre'] ) ? trim( (string) $item['nombre'] ) : '';
	$linea  = isset( $item['linea'] ) ? strtolower( trim( (string) $item['linea'] ) ) : '';

	$logo_data = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $logo, 'full', $nombre ) : array(
		'url' => is_array( $logo ) ? ( $logo['url'] ?? '' ) : (string) $logo,
		'alt' => is_array( $logo ) ? ( $logo['alt'] ?? $nombre ) : $nombre,
	);
	$logo_svg = function_exists( 'fiflp_get_svg_logo_markup' ) ? fiflp_get_svg_logo_markup( $logo, array( 'class' => 'footer-editorial__partner-logo', 'alt' => $nombre ) ) : '';
	$url      = isset( $logo_data['url'] ) ? (string) $logo_data['url'] : '';
	$alt      = isset( $logo_data['alt'] ) ? (string) $logo_data['alt'] : $nombre;

	if ( '' === $url && '' === $logo_svg ) {
		continue;
	}

	$logos[] = array(
		'enlace' => $enlace,
		'svg'    => $logo_svg,
		'url'    => $url,
		'alt'    => $alt,
		'linea'  => $linea,
	);
}

if ( empty( $logos ) ) {
	return;
}

$rows_raw = function_exists( 'fiflp_partition_partner_logo_rows' ) ? fiflp_partition_partner_logo_rows( $logos ) : array( $logos );
if ( empty( $rows_raw ) ) {
	return;
}

// Respetar siempre el orden editorial de líneas definido en ACF (línea 1 / línea 2).
// Si por legado aparecen más de 2 líneas, se conserva la primera y se compacta el resto en la segunda.
$rows = array_values(
	array_filter(
		$rows_raw,
		static function ( $row ) {
			return is_array( $row ) && ! empty( $row );
		}
	)
);

if ( empty( $rows ) ) {
	return;
}

if ( count( $rows ) > 2 ) {
	$merged_second = array();
	for ( $i = 1, $len = count( $rows ); $i < $len; $i++ ) {
		$merged_second = array_merge( $merged_second, $rows[ $i ] );
	}
	$rows = array( $rows[0], $merged_second );
}
?>
<section class="portada-hero-retaila">
	<div class="footer-editorial__partners">
		<?php if ( '' !== $titulo ) : ?>
			<p class="footer-editorial__partners-title"><?php echo esc_html( $titulo ); ?></p>
		<?php endif; ?>
		<div class="footer-editorial__partners-grid">
			<?php foreach ( $rows as $row ) : ?>
				<div class="footer-editorial__partners-row" style="--footer-logo-cols: <?php echo esc_attr( (string) count( $row ) ); ?>;">
					<?php foreach ( $row as $logo ) : ?>
						<div class="footer-editorial__partner">
							<?php if ( $logo['enlace'] ) : ?>
								<a href="<?php echo esc_url( $logo['enlace'] ); ?>" target="_blank" rel="noopener noreferrer">
									<?php if ( $logo['svg'] ) : ?>
										<?php echo $logo['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php else : ?>
										<img src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php echo esc_attr( $logo['alt'] ); ?>" loading="lazy" decoding="async">
									<?php endif; ?>
								</a>
							<?php else : ?>
								<?php if ( $logo['svg'] ) : ?>
									<?php echo $logo['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php else : ?>
									<img src="<?php echo esc_url( $logo['url'] ); ?>" alt="<?php echo esc_attr( $logo['alt'] ); ?>" loading="lazy" decoding="async">
								<?php endif; ?>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
