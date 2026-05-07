<?php
/**
 * BLOQUE CAPÍTULO
 * - Sirve como separador visual
 * - Ya no genera anclas internas
 */

$module_args = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$titulo      = function_exists( 'fiflp_get_sub_field_compat' ) ? fiflp_get_sub_field_compat( 'titulo', $module_args ) : get_sub_field( 'titulo' );

if ( ! $titulo ) {
	return;
}

?>

<?php
static $capitulo_index = 0;
$capitulo_index++;
$cap_id = 'cap_' . intval( $capitulo_index );
?>

<section id="<?php echo esc_attr( $cap_id ); ?>" class="bloque capitulo fade-in">

	<h2 class="capitulo-titulo">
		<?php echo esc_html( $titulo ); ?>
	</h2>

</section>
