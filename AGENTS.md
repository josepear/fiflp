PROJECT: Web editorial FIFLP

STACK:
- WordPress (GeneratePress Child)
- ACF Flexible Content
- JS vanilla (editorial.js)
- CSS editorial

RULES:
- NO rehacer estructura
- NO romper Nextcloud / Cloudflare
- SOLO corregir y mejorar lo existente

ARCHITECTURE:
- page.php → render bloques ACF
- template-parts/bloques/*
- assets/js/editorial.js
- style.css

RESPONSIVE (móvil alineado con escritorio):
- **No hay “versión móvil” duplicada en el CMS**: el contenido ACF es único; la adaptación es **CSS** (`style.css`: `@media`, `clamp()`, `min()`/`max()`). Guardar cambios = **commits en Git** (rama `main` o ramas cortas por tema, p. ej. `fix/mobile-bloques-padding`, y merge).
- **Objetivo visual**: mismos bloques y jerarquía que en escritorio; en estrecho se ajustan **padding/margin del contenedor**, **huecos entre bloques** y **rejillas** (no reordenar el libro salvo que sea inevitable).
- **Breakpoints ya usados en el tema** (referencia al auditar): 520, 640, 720, 768, 782, 900, 1024, 1025px. Preferir reutilizarlos antes de inventar cortes nuevos.
- **Menú lateral editorial (≤1024px)**: abierto = overlay **pantalla completa** (`.menu-lateral.is-mobile-open`, `z-index: 9995`), lista con scroll; `body.fiflp-editorial-nav-open` bloquea scroll (`editorial.js`). Escape cierra; clic en enlace de navegación cierra (no afecta `preventDefault` del índice plegable).
- **Menú onepage (≤1024px)**: `template-parts/menu-onepage.php`, `style.css` y `editorial.js` — panel a pantalla completa, capas por encima del header fijo; logo en el panel; animación abrir desde arriba / cerrar hacia abajo (`onepage-menu-exit-down`, `onepage-menu-skip-panel-transition`, `transitionend` + timeout de respaldo).
- **Cronología editorial (≤900px)**: tipografía compacta en móvil (título del bloque, fecha del hito, cuerpo); imágenes del hito visibles; ajuste fino del eje con `transform` en `.cronologia-editorial__meta::before` solo en ese breakpoint.
- **Estrategia de trabajo**: ir bloque a bloque (`.bloque.*`, `.layout-editorial`, `main.editorial`, onepage, cronología); revisar reglas que en desktop asumen mucho padding horizontal y en móvil quedan descompensadas; usar variables en `:root` solo si repetimos el mismo valor en muchos sitios (p. ej. `--fiflp-editorial-pad-x`).
- **No** añadir campos ACF “solo móvil” salvo excepción editorial clara (casi nunca hace falta).

CUADRO EDITORIAL (CPT reutilizable):
- CPT `fiflp_cuadro` (menú «Cuadros editoriales»): el contenido se define una vez; se referencia donde haga falta.
- ACF del CPT: `acf-json/group_fiflp_cuadro.json` (columnas 2–4, filas cifra/texto, intro, tipografía y colores).
- Inserción: layout flexible `cuadro_editorial` en `group_bloques_editoriales.json` (páginas) y en `group_secciones_onepage.json` (módulos onepage; opcional submenú). En cronología: campo `cuadro` en cada hito (`group_cronologias_editoriales.json`), opcional; en front va tras el texto del hito y antes de la galería masonry.
- PHP: `fiflp_render_cuadro( $post_id, $args )` y helpers `fiflp_cuadro_normalize_px_pair`, `fiflp_cuadro_clamp_font_size`, `fiflp_cuadro_clamp_font_size_fluid` en `functions.php`. Plantillas `template-parts/bloques/cuadro-editorial.php` y `cuadro-markup.php`. Front: `.fiflp-cuadro*` en `style.css`. Admin CPT: `assets/css/acf-cuadro-editorial-admin.css` (rejilla 2 col., repeater compacto), encolado en `functions.php` con `fiflp_cuadro`.
- Tipografía titular/intro: variables `--fiflp-cuadro-titular-size` / `--fiflp-cuadro-intro-size` desde `cuadro-markup.php`. En `style.css`, selectores **`.fiflp-cuadro .fiflp-cuadro__mensaje`** y **`.fiflp-cuadro .fiflp-cuadro__texto`** (dos clases) para que no los pise `@media (min-width: 1025px) { .editorial p { font-size: 18px; } }`. Intro WYSIWYG: **`.fiflp-cuadro .fiflp-cuadro__intro p { font-size: inherit; }`**.
- Semilla cuadro «2» (datos de obra, 3 columnas): `inc/fiflp-cuadro-seed-2.php` + `fiflp_seed_cuadro_editorial_2()`. En admin, listado **Cuadros editoriales**: aviso con enlace «Crear cuadro 2» (solo si no existe un post titulado `2`; requiere `manage_options`). Alternativa CLI: `php wp-content/themes/generatepress-child/bin/create-cuadro-editorial-2.php` desde la carpeta que contiene `wp-load.php`.
- Tras desplegar JSON: sincronizar grupos ACF en WP si hace falta.

LIGHTBOX:
- `footer.php`: contenedor `.lightbox-viewport`, botón `.lightbox-zoom` (ampliar/restaurar). `editorial.js`: zoom con recentrado del viewport; selectores acotados al `#lightbox`. Estilos en `style.css` (`.lightbox--zoomed`, etc.).

OBJECTIVE:
Sistema editorial tipo libro:
- menú lateral
- navegación por capítulos
- lightbox
- animaciones

PRIORITY:
1. Que funcione
2. Que sea limpio
3. Que no rompa nada existente

CRONOLOGÍA EDITORIAL (hitos, multiply):
- Plantilla: `template-parts/bloques/cronologia-editorial.php` — campos `imagen_multiplicar_1` / `imagen_multiplicar_2`; fallback legacy `imagen_multiplicar` para ambas imágenes si no existen los nuevos.
- HTML: clase `is-multiply` en `<a>` e `<img>`; el `<figure>` recibe `cronologia-editorial__media--multiply` si alguna imagen del hito activa multiply.
- CSS: `style.css` — `mix-blend-mode: multiply` solo en `.cronologia-editorial__media img.is-multiply`. Con multiply se relaja `overflow` y se neutralizan propiedades que crean stacking context aislado (`isolation`, `transform`, `filter`, `opacity`, `will-change`) en figure, stack, triggers y en `.bloque.cronologia-editorial:has(.cronologia-editorial__media--multiply)`.
- No usar filtros SVG ni overlays para “simular” multiply: el efecto debe mezclar con el backdrop real; fondo casi blanco hace que multiply sea poco visible (comportamiento esperado del modo).
- ACF JSON: `acf-json/group_cronologias_editoriales.json`; admin: `assets/js/acf-cronologia-editorial-admin.js`, `assets/css/acf-cronologia-editorial-admin.css`.