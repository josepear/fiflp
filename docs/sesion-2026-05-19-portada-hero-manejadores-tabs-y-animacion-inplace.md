# Sesión 2026-05-19 — Portada Hero: manejadores por tabs + animación in-place

## Objetivo
Dejar Portada Hero preparada "para guerra": control granular por dispositivo desde ACF y animación de entrada robusta sin recolocaciones ni saltos.

## 1) Manejadores por tab (Desktop / Tablet / Móvil)
Se añadieron controles por dispositivo en el CPT `fiflp_portada_hero` para:
- Logo: ancho máximo, separación inferior.
- Rótulo: ancho máximo.
- Subtítulo: padding superior.
- Acciones: padding superior.
- Botones de descarga: escala.
- Contenedor hero: padding superior y padding lateral.

Implementación:
- Nuevos campos en `functions.php`.
- Lectura/normalización en `template-parts/bloques/portada-hero.php`.
- Exposición como variables CSS en el `style` inline del `<section class="portada-hero">`.
- Aplicación en `style.css` con fallback Desktop → Tablet → Móvil.

## 2) Rótulo editorial compartido (Página + Portada Hero)
Se mantuvo el scope compartido `rotulo-editorial--context-page` para que los cambios sean globales en ambos contextos.

## 3) Animación de entrada Portada Hero
Ajustes finales según feedback:
- Se eliminó la recolocación del rótulo (nada de fixed→relative ni offsets calculados).
- El rótulo ahora anima in-place (escala+fade en su sitio final).
- El resto de elementos también aparece en su posición final (solo fade/desplazamiento local).
- Se retiró blur de la secuencia.
- Se suavizó la curva temporal para un comportamiento más cinematográfico.

Secuencia temporal vigente:
- Rótulo: entrada principal.
- Después: logo, subtítulo, botón principal, botones secundarios + logos institucionales.

## 4) Fondo de Portada Hero
Se reforzó el encuadre para evitar saltos de tamaño al cerrar la animación:
- `background-size: cover !important;`
- `background-position: center center !important;`

## 5) Botones de descarga
- Se añadieron iconos inline (PDF / EPUB).
- Escala aumentada.
- Color final del icono: amarillo onepage `#fde25f`.

## Archivos tocados
- `app/public/wp-content/themes/generatepress-child/functions.php`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/portada-hero.php`
- `app/public/wp-content/themes/generatepress-child/style.css`
- `app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`
