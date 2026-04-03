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

	if ( '' === $footer_logo_url && function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
		$custom_logo_id  = (int) get_theme_mod( 'custom_logo' );
		$custom_logo     = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $custom_logo_id, 'full', get_bloginfo( 'name' ) ) : array();
		$footer_logo_url = isset( $custom_logo['url'] ) ? (string) $custom_logo['url'] : '';
		$footer_logo_alt = isset( $custom_logo['alt'] ) ? (string) $custom_logo['alt'] : get_bloginfo( 'name' );
	}
	?>
	<footer class="footer-editorial">
		<div class="footer-editorial__inner">
			<div class="footer-editorial__brand">
				<a class="footer-editorial__brand-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php if ( $footer_logo_url ) : ?>
						<img class="footer-editorial__brand-logo" src="<?php echo esc_url( $footer_logo_url ); ?>" alt="<?php echo esc_attr( $footer_logo_alt ); ?>">
					<?php else : ?>
						<span class="footer-editorial__brand-text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
					<?php endif; ?>
				</a>
			</div>

			<div class="footer-editorial__partners">
				<?php if ( $footer_titulo_logos ) : ?>
					<p class="footer-editorial__partners-title"><?php echo esc_html( $footer_titulo_logos ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $footer_colaboradores ) && is_array( $footer_colaboradores ) ) : ?>
					<div class="footer-editorial__partners-grid">
						<?php foreach ( $footer_colaboradores as $colaborador ) : ?>
							<?php
							$logo   = $colaborador['logo'] ?? null;
							$enlace = isset( $colaborador['enlace'] ) ? trim( (string) $colaborador['enlace'] ) : '';
							$nombre = isset( $colaborador['nombre'] ) ? trim( (string) $colaborador['nombre'] ) : '';
							$logo_data = function_exists( 'fiflp_get_image_data' ) ? fiflp_get_image_data( $logo, 'full', $nombre ) : array(
								'url' => is_array( $logo ) ? ( $logo['url'] ?? '' ) : (string) $logo,
								'alt' => is_array( $logo ) ? ( $logo['alt'] ?? $nombre ) : $nombre,
							);
							$url      = isset( $logo_data['url'] ) ? (string) $logo_data['url'] : '';
							$alt      = isset( $logo_data['alt'] ) ? (string) $logo_data['alt'] : $nombre;

							if ( '' === $url ) {
								continue;
							}
							?>
							<div class="footer-editorial__partner">
								<?php if ( $enlace ) : ?>
									<a href="<?php echo esc_url( $enlace ); ?>" target="_blank" rel="noopener noreferrer">
										<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
									</a>
								<?php else : ?>
									<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
								<?php endif; ?>
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

<div id="lightbox" class="lightbox">
	<span class="lightbox-close">&times;</span>
	<img class="lightbox-img" src="" alt="">
	<p class="lightbox-caption"></p>
</div>

<?php wp_footer(); ?>

</body>
</html>
