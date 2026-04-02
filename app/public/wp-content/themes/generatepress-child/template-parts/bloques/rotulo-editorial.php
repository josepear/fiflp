<?php
$supertitulo = trim( (string) get_sub_field( 'supertitulo' ) );
$titulo      = trim( (string) get_sub_field( 'titulo' ) );
$subtitulo   = trim( (string) get_sub_field( 'subtitulo' ) );

if ( '' === $titulo && '' === $supertitulo && '' === $subtitulo ) {
	return;
}
?>

<section class="bloque rotulo-editorial-bloque fade-in">
	<div class="rotulo-editorial">
		<?php if ( '' !== $supertitulo ) : ?>
			<div class="rotulo-editorial__franja rotulo-editorial__franja--superior">
				<svg class="rotulo-editorial__marco" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
					<polygon class="rotulo-editorial__marco-shape" points="12,2 98,2 88,98 2,98"></polygon>
				</svg>
				<span class="rotulo-editorial__union">
					<span class="rotulo-editorial__texto rotulo-editorial__texto--superior"><?php echo esc_html( $supertitulo ); ?></span>
				</span>
			</div>
		<?php endif; ?>

		<?php if ( '' !== $titulo ) : ?>
			<div class="rotulo-editorial__franja rotulo-editorial__franja--principal">
				<svg class="rotulo-editorial__marco" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
					<polygon class="rotulo-editorial__marco-shape rotulo-editorial__marco-shape--sin-top" points="-6,2 93,2 99,98 0,98"></polygon>
				</svg>
				<span class="rotulo-editorial__texto rotulo-editorial__texto--principal"><?php echo esc_html( $titulo ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( '' !== $subtitulo ) : ?>
			<p class="rotulo-editorial__subtitulo"><?php echo esc_html( $subtitulo ); ?></p>
		<?php endif; ?>
	</div>
</section>
