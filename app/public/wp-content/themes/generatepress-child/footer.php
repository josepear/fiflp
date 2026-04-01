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

<div id="lightbox" class="lightbox">
	<span class="lightbox-close">&times;</span>
	<img class="lightbox-img" src="" alt="">
	<p class="lightbox-caption"></p>
</div>

<?php wp_footer(); ?>

</body>
</html>
