# Sesión 2026-05-19 — Portada Hero: galería/fade y ajustes de logos

## Cambios incluidos
- Galería aleatoria por dispositivo (desktop/tablet/móvil) en Portada Hero.
- Nuevo control por dispositivo para opacidad del velo.
- Nuevo switch por dispositivo para fundido automático de galería cada 5s.
- Animación de rótulo desde el centro de pantalla hacia su posición final (más lenta y suave).
- Ajustes de distribución y normalización visual de logos institucionales en Portada Hero.
- Compactación vertical en desktop para reducir necesidad de scroll en Hero.

## Archivos tocados
- `app/public/wp-content/themes/generatepress-child/functions.php`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/portada-hero.php`
- `app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`
- `app/public/wp-content/themes/generatepress-child/style.css`

## Notas
- El fundido de galería solo se activa si el switch está activo y hay más de 1 imagen en la galería del dispositivo.
- La opacidad del velo se aplica por breakpoint usando variables CSS por dispositivo.
