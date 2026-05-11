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

CUADRO EDITORIAL (CPT reutilizable):
- CPT `fiflp_cuadro` (menú «Cuadros editoriales»): el contenido se define una vez; se referencia donde haga falta.
- ACF del CPT: `acf-json/group_fiflp_cuadro.json` (columnas 2–4, filas cifra/texto, intro, tipografía y colores).
- Inserción: layout flexible `cuadro_editorial` en `group_bloques_editoriales.json` (páginas) y en `group_secciones_onepage.json` (módulos onepage; opcional submenú). En cronología: campo `cuadro` en cada hito (`group_cronologias_editoriales.json`), opcional; en front va tras el texto del hito y antes de la galería masonry.
- PHP: `fiflp_render_cuadro( $post_id, $args )` y helpers `fiflp_cuadro_normalize_px_pair` / `fiflp_cuadro_clamp_font_size` en `functions.php` (clamp CSS con min ≤ max). Plantillas `template-parts/bloques/cuadro-editorial.php` (bloque/módulo) y `cuadro-markup.php` (marcado único). Estilos: `.fiflp-cuadro*` en `style.css`.
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