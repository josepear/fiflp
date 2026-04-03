<?php
/**
 * BLOQUE TEXTO
 * - Muestra contenido WYSIWYG
 * - Aplica alineación seleccionada en ACF (left | center | right)
 */

$contenido  = get_sub_field( 'contenido' );
$alineacion = get_sub_field( 'alineacion' );

$alineaciones_validas = array( 'left', 'center', 'right' );
if ( ! in_array( $alineacion, $alineaciones_validas, true ) ) {
	$alineacion = 'left';
}
?>

<section class="bloque texto fade-in texto-<?php echo esc_attr( $alineacion ); ?>">
	<?php echo wp_kses_post( $contenido ); ?>
</section>
