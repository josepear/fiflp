<?php
/**
 * BLOQUE CAPÍTULO
 * - Sirve como separador
 * - Añade ID para navegación (ancla)
 */

$titulo = get_sub_field( 'titulo' );

if ( ! $titulo ) {
	return;
}

$anchor = function_exists( 'generatepress_child_editorial_anchor' )
	? generatepress_child_editorial_anchor( $titulo )
	: sanitize_title( $titulo );
?>

<section id="<?php echo esc_attr( $anchor ); ?>" class="bloque capitulo fade-in">

	<h2 class="capitulo-titulo">
		<?php echo esc_html( $titulo ); ?>
	</h2>

</section>
