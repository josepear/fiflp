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
	<?php do_action( 'generate_footer' ); ?>
	<?php do_action( 'generate_after_footer_content' ); ?>
</div>

<?php do_action( 'generate_after_footer' ); ?>

<div class="lightbox-overlay" id="lightbox" aria-hidden="true">
	<button type="button" class="lightbox-close" aria-label="Cerrar imagen">&times;</button>
	<img class="lightbox-img" src="" alt="">
</div>

<?php wp_footer(); ?>

</body>
</html>
