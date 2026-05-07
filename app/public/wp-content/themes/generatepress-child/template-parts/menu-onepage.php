<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sections = isset( $args['sections'] ) && is_array( $args['sections'] ) ? $args['sections'] : array();

if ( empty( $sections ) ) {
	return;
}

$panel_id = 'fiflp-onepage-nav-panel';
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
			<span class="fiflp-onepage-sidebar__toggle-bars" aria-hidden="true"></span>
			<span class="screen-reader-text"><?php echo esc_html__( 'Abrir o cerrar el índice de secciones', 'generatepress' ); ?></span>
		</button>

		<div class="fiflp-onepage-sidebar__sheet" data-onepage-sidebar-sheet>
			<div
				class="fiflp-onepage-sidebar__backdrop"
				data-onepage-sidebar-overlay
				aria-hidden="true"
			></div>

			<nav class="fiflp-onepage-sidebar__panel" id="<?php echo esc_attr( $panel_id ); ?>" data-onepage-sidebar-panel>
				<p class="fiflp-onepage-sidebar__heading"><?php echo esc_html__( 'Secciones', 'generatepress' ); ?></p>
				<ol class="fiflp-onepage-sidebar__list">
					<?php foreach ( $sections as $sec ) : ?>
						<?php
						if ( ! is_array( $sec ) ) {
							continue;
						}
						$anchor = isset( $sec['anchor'] ) ? (string) $sec['anchor'] : '';
						$label  = isset( $sec['label'] ) ? (string) $sec['label'] : '';

						if ( '' === $anchor || '' === $label ) {
							continue;
						}
						?>
						<li>
							<a href="#<?php echo esc_attr( $anchor ); ?>" data-onepage-nav-link><?php echo esc_html( $label ); ?></a>
						</li>
					<?php endforeach; ?>
				</ol>
			</nav>
		</div>
	</aside>
</div>
