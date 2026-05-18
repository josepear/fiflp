# Sesión 2026-05-18 — Portada Hero como Landing Gate

## Objetivo
Reemplazar el antiguo Home Hero por un sistema nuevo `Portada Hero` reutilizable y permitir modo pantalla de entrada (Landing Gate) en portada principal.

## Cambios principales
- Eliminación de `home-hero` legado en plantillas y rutas asociadas.
- Nuevo CPT `fiflp_portada_hero` para gestionar el hero de portada.
- Nuevo CPT `fiflp_retaila_logos` para reutilizar la franja de logos institucionales.
- Render de portada hero en `page.php` cuando la página es portada y tiene referencia seleccionada.
- Nuevo template `template-parts/bloques/portada-hero.php` con:
  - fondo por dispositivo (desktop/tablet/móvil), imagen o vídeo,
  - logo,
  - rótulo editorial de página,
  - subtítulo,
  - botones central/PDF/EPUB,
  - retaila de logos.
- Nuevo template `template-parts/bloques/retaila-logos.php`.
- Botones PDF/EPUB como selector de archivo de medios (`pdf`, `epub`, `zip`).
- Modo Landing Gate:
  - campo `modo_pantalla_entrada` en Portada Hero,
  - campo `destino_capitulos` para elegir página destino,
  - si Landing Gate está activo, la portada renderiza solo el hero y no el flujo editorial inferior.

## Estabilidad ACF
- Blindaje para evitar warnings por claves faltantes en campos ACF:
  - `_name` en subcampos de flexible/repeater,
  - `multiple` en campos `select`.
- Refuerzo específico del layout `portada_hero` inyectado dentro de `field_bloques`.

## Limpieza
- Eliminados archivos residuales `Icon` en:
  - `app/public/wp-content/themes/generatepress-child/acf-json/`
  - `docs/`

## Nota funcional
Con `modo_pantalla_entrada` activo, la home funciona como pantalla de presentación independiente; el botón central dirige a la página de capítulos seleccionada.
