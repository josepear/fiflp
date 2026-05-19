# Sesión 2026-05-19 — Portada Hero centrado final de retaila/logos

## Objetivo
- Corregir definitivamente el centrado del bloque de logos (retaila) en Portada Hero.
- Mantener independencia del bloque de logos respecto al flujo del contenido.

## Cambios aplicados
- `portada-hero-retaila` pasa a anclaje absoluto robusto:
  - `left: 0; right: 0; width: 100%;`
  - centrado por `display:flex; justify-content:center;`
  - `bottom` por dispositivo con variables:
    - desktop: `--ph-retaila-bottom-desktop: 80px`
    - tablet: `--ph-retaila-bottom-tablet: 44px`
    - móvil: `--ph-retaila-bottom-mobile: 24px`
- Reforzado centrado interno en:
  - `.footer-editorial__partners`
  - `.footer-editorial__partners-grid`
  - `.footer-editorial__partners-row`
- Neutralizados desplazamientos no deseados (`transform/left`) y animaciones/transiciones del bloque de logos para evitar “arrastres”.

## Archivo modificado
- `app/public/wp-content/themes/generatepress-child/style.css`

## Resultado esperado
- Logos centrados abajo en Portada Hero sin escoramiento lateral.
- Sin dependencia de la posición vertical del resto del contenido.
