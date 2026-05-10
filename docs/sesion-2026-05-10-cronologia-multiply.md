# Sesión 2026-05-10 — Cronología: modo "Multiplicar" por imagen

## Objetivo
Arreglar el modo de fusión `multiply` en imágenes de hitos de cronología para que funcione por imagen (Imagen 1 / Imagen 2), sin romper nodos, línea ni estructura del bloque.

## Cambios aplicados

### 1) Render por imagen en cronología
Archivo: `app/public/wp-content/themes/generatepress-child/template-parts/bloques/cronologia-editorial.php`

- Se mantiene control independiente por imagen:
  - `imagen_multiplicar_1`
  - `imagen_multiplicar_2`
- Se mantiene compatibilidad con campo legacy `imagen_multiplicar`.
- Se añade clase `is-multiply` en el `img` de cada media marcada.
- Se añade clase de contexto `cronologia-editorial__media--multiply` al `figure` cuando al menos una imagen del hito está en modo multiplicar.
- Se eliminó `fade-in` en el `<section>` de cronología para evitar stacking contexts por animación (`transform/opacity`) que interfieren con blend.

### 2) CSS de blend en cronología
Archivo: `app/public/wp-content/themes/generatepress-child/style.css`

- `mix-blend-mode: multiply` se aplica al `img.is-multiply` (no al enlace completo).
- Se relaja encapsulado del blend en contexto multiply:
  - `overflow: visible` en `figure` y trigger dentro de `--multiply`.
- Se neutralizan propiedades que pueden crear contextos aislados en la rama de cronología con multiply:
  - `transform`, `filter`, `opacity`, `will-change`, `isolation`.
- Se añadió fallback controlado con `feColorMatrix` (solo en cronología + multiply) y ajuste de medios tonos para evitar imagen lavada:
  - `contrast(1.08) brightness(1.03)` + matriz suavizada.

### 3) ACF JSON
Archivo: `app/public/wp-content/themes/generatepress-child/acf-json/group_cronologias_editoriales.json`

- Se conserva estructura con campos por imagen de multiply en hitos.

## Validación

- `php -l` OK en PHP tocado.
- JSON ACF válido.
- Prueba visual manual en hito 1949 con hard refresh.

## Alcance

- Solo se tocó lo relativo a cronología/multiply.
- No se modificaron nodos, línea de cronología ni estructura general del módulo.
