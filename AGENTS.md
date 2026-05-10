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