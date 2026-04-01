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