<?php
/**
 * FOOTER DEL THEME HIJO
 * - Necesario para wp_footer()
 * - Aquí metemos el lightbox global
 */
?>

    <?php wp_footer(); ?>

    <!-- =========================
         LIGHTBOX GLOBAL
         Se usa para abrir imágenes en grande
    ========================== -->
    <div class="lightbox-overlay" id="lightbox">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-img" src="" alt="">
    </div>

</body>
</html>