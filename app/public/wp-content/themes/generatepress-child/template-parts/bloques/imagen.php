<?php
/**
 * BLOQUE IMAGEN
 * - Soporta modo normal / full
 * - Añade lightbox (clic en imagen)
 */

$imagen = get_sub_field('imagen');
$caption = get_sub_field('caption');
$full = get_sub_field('full');

$clase = $full ? 'full' : '';
?>

<section class="bloque imagen <?php echo $clase; ?>">

    <figure>

        <!-- Envolvemos imagen con enlace para lightbox -->
        <a href="<?php echo esc_url($imagen); ?>" class="lightbox-trigger">

            <img src="<?php echo esc_url($imagen); ?>" alt="">

        </a>

        <?php if ($caption): ?>
            <figcaption>
                <?php echo esc_html($caption); ?>
            </figcaption>
        <?php endif; ?>

    </figure>

</section>