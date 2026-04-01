<?php
/**
 * BLOQUE CAPÍTULO
 * - Sirve como separador
 * - Añade ID para navegación (ancla)
 */

$titulo = get_sub_field('titulo');

/**
 * Genera ID limpio para usar en enlaces
 */
$anchor = sanitize_title($titulo);
?>

<section id="<?php echo $anchor; ?>" class="bloque capitulo">

    <h2 class="capitulo-titulo">
        <?php echo esc_html($titulo); ?>
    </h2>

</section>