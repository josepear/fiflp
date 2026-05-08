# Sesión 2026-05-08 — Onepage rótulo + cronología admin

## Objetivo
Aplicar dos ajustes sin romper estructura existente:
1. Añadir manejador de tamaño al módulo `rotulo_editorial` dentro de onepage.
2. Recuperar el botón `-` (borrar hito) en cronología (admin ACF).

## Cambios aplicados

### 1) Manejador de tamaño en rótulo onepage
Archivo:
- `app/public/wp-content/themes/generatepress-child/acf-json/group_secciones_onepage.json`

Cambio:
- En `layout_onepage_rotulo_editorial` se añadió el campo:
  - `name: tamano`
  - tipo `button_group`
  - opciones `s / m / l / xl`
  - valor por defecto `m`

Nota:
- No se tocó plantilla PHP del rótulo porque ya soporta `tamano`.

### 2) Botón borrar hito en cronología admin
Archivo:
- `app/public/wp-content/themes/generatepress-child/assets/css/acf-cronologia-editorial-admin.css`

Causa:
- Una regla ocultaba iconos de `.acf-row-handle` y terminaba ocultando también el icono `-` de borrar.

Fix:
- Se limitó la ocultación de iconos para excluir el handle de borrado.
- Se forzó visibilidad del icono `-` en `.acf-row-handle.remove .acf-icon.-minus`.

## Verificación técnica
- JSON ACF válido (`group_secciones_onepage.json`).
- Ajuste CSS puntual sin cambios de JS/PHP.

## Verificación funcional
1. En onepage, módulo `rotulo_editorial` muestra el control `Tamaño` (S/M/L/XL).
2. En cronología (admin), vuelve a verse el botón `-` para borrar cada hito.

## Estado
OK
