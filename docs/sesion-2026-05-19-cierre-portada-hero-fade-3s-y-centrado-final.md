# Sesión 2026-05-19 — Cierre Portada Hero

## Cierre final aplicado
- Slideshow del fondo de Portada Hero robusto y estable.
- Intervalo final fijado en 3 segundos entre cambios.
- Fundido foto→foto (sin salto por color de fondo) con precarga robusta.
- Ajustes finales de centrado/anclaje de retaila de logos y separación entre filas.

## Archivos modificados
- `app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/portada-hero.php`
- `app/public/wp-content/themes/generatepress-child/style.css`

## Notas
- Se reemplazó lógica de timers no determinista por un único motor activo sobre el fondo visible.
- Se mantuvo data-interval en 3000ms para consistencia con la lógica JS.
