<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	</div>
</div>

<?php do_action( 'generate_before_footer' ); ?>

<div <?php generate_do_attr( 'footer' ); ?>>
	<?php do_action( 'generate_before_footer_content' ); ?>
	<?php
	$footer_logo            = function_exists( 'get_field' ) ? get_field( 'footer_logo_fiflp', 'option' ) : null;
	$footer_colaboradores   = function_exists( 'get_field' ) ? get_field( 'footer_logos_colaboradores', 'option' ) : array();
	$footer_copyright       = function_exists( 'get_field' ) ? trim( (string) get_field( 'footer_texto_copyright', 'option' ) ) : '';
	$footer_credito         = function_exists( 'get_field' ) ? trim( (string) get_field( 'footer_texto_credito', 'option' ) ) : '';
	$footer_titulo_logos    = function_exists( 'get_field' ) ? trim( (string) get_field( 'footer_titulo_colaboradores', 'option' ) ) : '';
	$footer_logo_data       = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $footer_logo, 'full', get_bloginfo( 'name' ) ) : array(
		'url' => is_array( $footer_logo ) ? ( $footer_logo['url'] ?? '' ) : (string) $footer_logo,
		'alt' => is_array( $footer_logo ) ? ( $footer_logo['alt'] ?? get_bloginfo( 'name' ) ) : get_bloginfo( 'name' ),
	);
	$footer_logo_url        = isset( $footer_logo_data['url'] ) ? (string) $footer_logo_data['url'] : '';
	$footer_logo_alt        = isset( $footer_logo_data['alt'] ) ? (string) $footer_logo_data['alt'] : get_bloginfo( 'name' );
	$footer_copyright_final = $footer_copyright ? $footer_copyright : '© ' . gmdate( 'Y' ) . ' FIFLP';
	$footer_partner_rows    = array();
	$footer_logo_style      = function_exists( 'fiflp_build_style_attribute' )
		? fiflp_build_style_attribute(
			array(
				'--fiflp-footer-logo-width' => function_exists( 'fiflp_get_option_number' ) ? fiflp_get_option_number( 'apariencia_logo_footer_ancho', 180, 0, 400 ) . 'px' : '180px',
				'--fiflp-footer-logo-height' => function_exists( 'fiflp_get_option_number' ) ? fiflp_get_option_number( 'apariencia_logo_footer_alto', 72, 0, 400 ) . 'px' : '72px',
			)
		)
		: '';

	if ( '' === $footer_logo_url && function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
		$custom_logo_id  = (int) get_theme_mod( 'custom_logo' );
		$custom_logo     = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $custom_logo_id, 'full', get_bloginfo( 'name' ) ) : array();
		$footer_logo_url = isset( $custom_logo['url'] ) ? (string) $custom_logo['url'] : '';
		$footer_logo_alt = isset( $custom_logo['alt'] ) ? (string) $custom_logo['alt'] : get_bloginfo( 'name' );
	}

	if ( ! empty( $footer_colaboradores ) && is_array( $footer_colaboradores ) ) {
		$footer_partner_items = array();
		$footer_partner_row_1 = array();
		$footer_partner_row_2 = array();
		$footer_partner_auto  = array();

		foreach ( $footer_colaboradores as $colaborador ) {
			$logo   = $colaborador['logo'] ?? null;
			$enlace = isset( $colaborador['enlace'] ) ? trim( (string) $colaborador['enlace'] ) : '';
			$nombre = isset( $colaborador['nombre'] ) ? trim( (string) $colaborador['nombre'] ) : '';
			$linea  = isset( $colaborador['linea'] ) ? strtolower( trim( (string) $colaborador['linea'] ) ) : '';

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

			$footer_partner_items[] = array(
				'enlace' => $enlace,
				'svg'    => $logo_svg,
				'url'    => $url,
				'alt'    => $alt,
				'linea'  => $linea,
			);
		}

		foreach ( $footer_partner_items as $item ) {
			$linea = $item['linea'];
			if ( in_array( $linea, array( '1', 'linea_1', 'primera', 'first' ), true ) ) {
				$footer_partner_row_1[] = $item;
				continue;
			}
			if ( in_array( $linea, array( '2', 'linea_2', 'segunda', 'second' ), true ) ) {
				$footer_partner_row_2[] = $item;
				continue;
			}
			$footer_partner_auto[] = $item;
		}

		foreach ( $footer_partner_auto as $item ) {
			if ( count( $footer_partner_row_1 ) < 6 ) {
				$footer_partner_row_1[] = $item;
				continue;
			}
			if ( count( $footer_partner_row_2 ) < 6 ) {
				$footer_partner_row_2[] = $item;
				continue;
			}
			break;
		}

		$footer_partner_row_1 = array_slice( $footer_partner_row_1, 0, 6 );
		$footer_partner_row_2 = array_slice( $footer_partner_row_2, 0, 6 );
		if ( ! empty( $footer_partner_row_1 ) ) {
			$footer_partner_rows[] = $footer_partner_row_1;
		}
		if ( ! empty( $footer_partner_row_2 ) ) {
			$footer_partner_rows[] = $footer_partner_row_2;
		}
	}
	?>
	<footer class="footer-editorial">
		<div class="footer-editorial__inner">
			<div class="footer-editorial__brand">
				<a class="footer-editorial__brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"<?php echo '' !== $footer_logo_style ? ' style="' . esc_attr( $footer_logo_style ) . '"' : ''; ?>>
					<?php $footer_logo_svg = function_exists( 'fiflp_get_svg_logo_markup' ) ? fiflp_get_svg_logo_markup( $footer_logo, array( 'class' => 'footer-editorial__brand-logo', 'alt' => $footer_logo_alt ) ) : ''; ?>
					<?php if ( $footer_logo_svg ) : ?>
						<?php echo $footer_logo_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php elseif ( $footer_logo_url ) : ?>
						<img class="footer-editorial__brand-logo" src="<?php echo esc_url( $footer_logo_url ); ?>" alt="<?php echo esc_attr( $footer_logo_alt ); ?>" loading="lazy" decoding="async">
					<?php else : ?>
						<span class="footer-editorial__brand-text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
					<?php endif; ?>
				</a>
			</div>

			<div class="footer-editorial__partners">
				<?php if ( $footer_titulo_logos ) : ?>
					<p class="footer-editorial__partners-title"><?php echo esc_html( $footer_titulo_logos ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $footer_partner_rows ) ) : ?>
					<div class="footer-editorial__partners-grid">
						<?php foreach ( $footer_partner_rows as $row ) : ?>
							<div class="footer-editorial__partners-row" style="--footer-logo-cols: <?php echo esc_attr( (string) count( $row ) ); ?>;">
								<?php foreach ( $row as $item ) : ?>
									<div class="footer-editorial__partner">
										<?php if ( $item['enlace'] ) : ?>
											<a href="<?php echo esc_url( $item['enlace'] ); ?>" target="_blank" rel="noopener noreferrer">
												<?php if ( $item['svg'] ) : ?>
													<?php echo $item['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												<?php else : ?>
													<img src="<?php echo esc_url( $item['url'] ); ?>" alt="<?php echo esc_attr( $item['alt'] ); ?>" loading="lazy" decoding="async">
												<?php endif; ?>
											</a>
										<?php else : ?>
											<?php if ( $item['svg'] ) : ?>
												<?php echo $item['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											<?php else : ?>
												<img src="<?php echo esc_url( $item['url'] ); ?>" alt="<?php echo esc_attr( $item['alt'] ); ?>" loading="lazy" decoding="async">
											<?php endif; ?>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="footer-editorial__legal">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer_legal',
						'container'      => false,
						'menu_class'     => 'footer-editorial__menu',
						'fallback_cb'    => false,
						'depth'          => 1,
					)
				);
				?>
			</div>
		</div>

		<div class="footer-editorial__bottom">
			<p class="footer-editorial__copyright"><?php echo esc_html( $footer_copyright_final ); ?></p>
			<?php if ( $footer_credito ) : ?>
				<p class="footer-editorial__credit"><?php echo esc_html( $footer_credito ); ?></p>
			<?php endif; ?>
		</div>
	</footer>
	<?php do_action( 'generate_after_footer_content' ); ?>
</div>

<?php do_action( 'generate_after_footer' ); ?>

<div id="lightbox" class="lightbox" aria-hidden="true">
	<span class="lightbox-close">&times;</span>
	<button type="button" class="lightbox-zoom" aria-label="<?php echo esc_attr( 'Ampliar imagen' ); ?>" aria-pressed="false" title="<?php echo esc_attr( 'Ampliar' ); ?>">
		<svg class="lightbox-zoom__icon" width="26" height="26" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<circle cx="10" cy="10" r="6" fill="none" stroke="currentColor" stroke-width="2" />
			<line x1="14.5" y1="14.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
			<line x1="7" y1="10" x2="13" y2="10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="lightbox-zoom__bar" />
		</svg>
	</button>
	<div class="lightbox-viewport">
		<img class="lightbox-img" src="" alt="">
	</div>
	<p class="lightbox-caption"></p>
</div>

<?php wp_footer(); ?>

</body>
</html>
