<?php
$contenido  = get_sub_field( 'contenido' );
$alineacion = get_sub_field( 'alineacion' ) ?: 'left';
$clase      = 'texto-alineacion-' . sanitize_html_class( $alineacion );
?>

<section class="bloque texto fade-in <?php echo esc_attr( $clase ); ?>">
	<?php echo wp_kses_post( $contenido ); ?>
</section>
