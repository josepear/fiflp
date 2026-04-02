<?php
/**
 * BLOQUE CAPÍTULO
 * - Sirve como separador visual
 * - Ya no genera anclas internas
 */

$titulo = get_sub_field( 'titulo' );

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
