<?php
/**
 * BLOQUE TEXTO + IMAGEN
 * Alterna izquierda/derecha + alineación editorial
 */

global $bloque_index;

if (!isset($bloque_index)) {
    $bloque_index = 0;
}

$bloque_index++;

// Detecta si es par
$invertido = ($bloque_index % 2 === 0);

// Clases dinámicas
$clase = $invertido ? 'invertido derecha' : 'izquierda';
?>

<section class="bloque texto-imagen <?php echo $clase; ?>">

    <div class="col texto">
        <?php the_sub_field('contenido'); ?>
    </div>

    <div class="col imagen">
        <img src="<?php the_sub_field('imagen'); ?>" />
    </div>

</section>