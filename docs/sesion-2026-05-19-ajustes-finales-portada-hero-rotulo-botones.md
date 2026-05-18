# Sesión 2026-05-19 — Ajustes finales Portada Hero / Rótulo / Botones

## Objetivo
Afinar microajustes visuales en rótulo editorial y Portada Hero para cerrar composición final.

## Cambios aplicados

1. Rótulo editorial (contexto página global: Página + Portada Hero)
- Forzado `width: fit-content` en franjas superior y principal.
- Padding interno de texto en marco SVG unificado y explícito:
  - top: 12px
  - right: 42px
  - bottom: 11px
  - left: 46px
- `visibility: visible` en franjas para evitar clipping.
- Ajuste fino vertical de texto en rótulo:
  - `.rotulo-editorial__texto--superior` y `--principal` con `transform: translateY(0.5px)`.
- Subtítulo del rótulo con solape controlado:
  - `margin-top: -5px !important`.

2. Portada Hero — espaciados verticales
- Logo: `margin-bottom: 40px`.
- Subtítulo hero: `padding-top: 24px`.
- Bloque de acciones: `padding-top: 48px`.

3. Botones de descarga (PDF/EPUB)
- Tamaño ajustado a valor intermedio respecto al original.
- Añadidos iconos inline SVG (PDF y EPUB) dentro de cada botón secundario.
- Iconos escalados al doble del ajuste base y color final del amarillo onepage:
  - `stroke: #fde25f`.

## Archivos tocados
- `app/public/wp-content/themes/generatepress-child/style.css`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/portada-hero.php`

## Nota
Los ajustes de rótulo están aplicados en el scope compartido `rotulo-editorial--context-page`, por lo que impactan de forma consistente tanto en Página como en Portada Hero.
