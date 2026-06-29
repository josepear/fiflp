<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sections = isset( $args['sections'] ) && is_array( $args['sections'] ) ? $args['sections'] : array();

if ( empty( $sections ) ) {
	return;
}

$panel_id = 'fiflp-onepage-nav-panel';

$logo = function_exists( 'fiflp_get_option_image_data' )
	? fiflp_get_option_image_data(
		'apariencia_logo_menu',
		function_exists( 'fiflp_get_theme_asset_url' ) ? fiflp_get_theme_asset_url( 'assets/rfiflp.png' ) : get_stylesheet_directory_uri() . '/assets/rfiflp.png',
		esc_html__( 'Logo centenario FIFLP', 'generatepress' )
	)
	: array(
		'url' => get_stylesheet_directory_uri() . '/assets/rfiflp.png',
		'alt' => esc_html__( 'Logo centenario FIFLP', 'generatepress' ),
	);

$logo_src = isset( $logo['url'] ) ? (string) $logo['url'] : '';
$logo_alt = isset( $logo['alt'] ) ? (string) $logo['alt'] : esc_html__( 'Logo centenario FIFLP', 'generatepress' );
$logo_style = function_exists( 'fiflp_build_style_attribute' )
	? fiflp_build_style_attribute(
		array(
			'--fiflp-onepage-logo-width'        => function_exists( 'fiflp_get_option_number' ) ? fiflp_get_option_number( 'apariencia_logo_menu_ancho', 140, 0, 400 ) . 'px' : '140px',
			'--fiflp-onepage-logo-width-mobile' => function_exists( 'fiflp_get_option_number' ) ? fiflp_get_option_number( 'apariencia_logo_menu_ancho_movil', 118, 0, 400 ) . 'px' : '118px',
		)
	)
	: '';
$logo_href = esc_url( home_url( '/' ) );
$hamburger_icons = function_exists( 'fiflp_get_hamburger_icon_candidates' )
	? fiflp_get_hamburger_icon_candidates( 'thumbnail' )
	: array();
$hamburger_icon = ! empty( $hamburger_icons )
	? $hamburger_icons[ array_rand( $hamburger_icons ) ]
	: array(
		'url' => '',
		'alt' => '',
	);
?>
<div class="fiflp-onepage-sidebar-col">
	<aside class="fiflp-onepage-sidebar" data-onepage-sidebar aria-label="<?php echo esc_attr__( 'Índice de secciones', 'generatepress' ); ?>">
		<button
			type="button"
			class="fiflp-onepage-sidebar__toggle"
			aria-expanded="true"
			aria-controls="<?php echo esc_attr( $panel_id ); ?>"
			data-onepage-sidebar-toggle
		>
			<span class="fiflp-onepage-sidebar__toggle-bars<?php echo ! empty( $hamburger_icon['url'] ) ? ' fiflp-onepage-sidebar__toggle-bars--image' : ''; ?>" aria-hidden="true"<?php echo ! empty( $hamburger_icons ) ? ' data-hamburger-icons="' . esc_attr( wp_json_encode( $hamburger_icons ) ) . '"' : ''; ?>>
				<?php if ( ! empty( $hamburger_icon['url'] ) ) : ?>
					<img src="<?php echo esc_url( $hamburger_icon['url'] ); ?>" alt="" width="60" height="60" decoding="async" />
				<?php endif; ?>
			</span>
			<span class="screen-reader-text"><?php echo esc_html__( 'Abrir o cerrar el índice de secciones', 'generatepress' ); ?></span>
		</button>

		<div class="fiflp-onepage-sidebar__sheet" data-onepage-sidebar-sheet>
			<div
				class="fiflp-onepage-sidebar__backdrop"
				data-onepage-sidebar-overlay
				aria-hidden="true"
			></div>

			<nav class="fiflp-onepage-sidebar__panel" id="<?php echo esc_attr( $panel_id ); ?>" data-onepage-sidebar-panel>
				<a class="fiflp-onepage-sidebar__brand" href="<?php echo esc_url( $logo_href ); ?>" aria-label="<?php echo esc_attr( $logo_alt ); ?>"<?php echo '' !== $logo_style ? ' style="' . esc_attr( $logo_style ) . '"' : ''; ?>>
					<img src="<?php echo esc_url( $logo_src ); ?>" alt="<?php echo esc_attr( $logo_alt ); ?>" width="360" height="134" decoding="async" fetchpriority="high" />
				</a>
				<ol class="fiflp-onepage-sidebar__list">
					<?php foreach ( $sections as $sec ) : ?>
						<?php
						if ( ! is_array( $sec ) ) {
							continue;
						}
						$anchor = isset( $sec['anchor'] ) ? (string) $sec['anchor'] : '';
						$label  = isset( $sec['label'] ) ? (string) $sec['label'] : '';
						$subitems = isset( $sec['subitems'] ) && is_array( $sec['subitems'] ) ? $sec['subitems'] : array();

						if ( '' === $anchor || '' === $label ) {
							continue;
						}
						?>
						<li>
							<a href="#<?php echo esc_attr( $anchor ); ?>" data-onepage-nav-link><?php echo esc_html( $label ); ?></a>
							<?php if ( ! empty( $subitems ) ) : ?>
								<ol class="fiflp-onepage-sidebar__sublist">
									<?php foreach ( $subitems as $sub ) : ?>
										<?php
										if ( ! is_array( $sub ) ) {
											continue;
										}
										$sub_anchor = isset( $sub['anchor'] ) ? (string) $sub['anchor'] : '';
										$sub_label  = isset( $sub['label'] ) ? (string) $sub['label'] : '';
										if ( '' === $sub_anchor || '' === $sub_label ) {
											continue;
										}
										?>
										<li>
											<a href="#<?php echo esc_attr( $sub_anchor ); ?>" data-onepage-nav-link data-onepage-sub-nav-link><span class="fiflp-onepage-sidebar__sublist-label"><?php echo esc_html( $sub_label ); ?></span></a>
										</li>
									<?php endforeach; ?>
								</ol>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</nav>
		</div>
	</aside>
</div>
