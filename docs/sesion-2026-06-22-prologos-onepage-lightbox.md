# Sesión 2026-06-22: prólogos, SVG Onepage y lightbox

## Prólogos

- Se unifica el tratamiento del contenido mediante `wpautop()` para respetar los párrafos introducidos en ACF.
- Se normalizan prólogos antiguos cuya imagen estaba insertada dentro del texto: JavaScript la mueve al contenedor `.prologo-img` y limpia saltos vacíos.
- En tablet se conserva la composición de dos columnas (retrato + contenido).

## Onepage

- Se elimina la opacidad fija aplicada al SVG en móvil.
- Las capas de relleno y línea vuelven a respetar los controles ACF:
  - `numero_opacidad_relleno`
  - `numero_opacidad_linea`

## Lightbox

- Un clic en la transparencia alrededor de la imagen cierra el lightbox.
- El clic sobre la imagen mantiene el comportamiento de zoom.
- El pie de foto se muestra cuando existe contenido.
- La leyenda se obtiene de `data-caption`, del `figcaption` o del texto alternativo como respaldo.

