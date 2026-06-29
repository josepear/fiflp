# Sesion 2026-06-29 - Zapato total onepage y cabecera

## Estado guardado

- Rama: `main`.
- Sitio local revisado en `http://localhost:10006/fiflp/`.
- Cabecera onepage en escritorio estabilizada:
  - cabecera fija;
  - contenido reserva el alto inicial de cabecera;
  - alto progresivo de `222px` a `80px`;
  - logo progresivo de `180px` a `60px`;
  - movil sin cambios intencionados.
- Menu onepage conservado con panel lateral, icono SVG configurable y fondo adaptado a seccion.
- Ajustes acumulados de ACF, bloques editoriales, prologos, rotulos, imagen/caption, footer y portada hero quedan dentro del mismo guardado.

## Detalle importante de cabecera

La cabecera no debe volver a depender de `position: sticky` en escritorio si se quiere evitar que el contenido salte al compactarse. La solucion estable es mantenerla `fixed`, reservar espacio en el `body` y animar solo variables internas:

- `--fiflp-header-current-height`
- `--fiflp-header-logo-scale`

El calculo vive en `assets/js/editorial.js` dentro de `initOnepageHeaderSync`.

## Verificacion realizada

- `node --check assets/js/editorial.js`
- `git diff --check`
- Medicion en Chrome/in-app browser:
  - inicio: header `222px`, logo `180px`, contenido bajo cabecera;
  - scroll: header baja progresivamente hasta `80px`, logo hasta `60px`.

