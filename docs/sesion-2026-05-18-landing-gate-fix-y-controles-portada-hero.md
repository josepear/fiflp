# Sesión 2026-05-18 — Landing Gate + controles Portada Hero

## Cambios aplicados

1. Landing Gate full-screen real en portada
- Detección de portada con Portada Hero también cuando viene desde flexible (`bloques_0_portada_hero`).
- En ese caso, `page.php` renderiza solo `Portada Hero` con HTML mínimo (sin `get_header()`/`get_footer()` del tema).
- Resultado buscado: sin cabecera, sin menú lateral, sin footer, sin scroll al contenido editorial.

2. Clase de body para modo landing
- Se mantiene clase `fiflp-landing-gate` para CSS scoped de pantalla de entrada.

3. Nuevos controles en CPT Portada Hero
- Titular/rótulo:
  - color de línea (`rotulo_color_trazo`)
  - color de relleno (`rotulo_color_fondo`)
  - color de letra (`rotulo_color_texto`)
- Subtítulo de portada:
  - color (`subtitulo_color`)
  - alineación (`subtitulo_alineacion`)
  - tipografía (`subtitulo_tipografia`)

4. Render de los nuevos controles
- `template-parts/bloques/portada-hero.php` pasa colores del rótulo al módulo `rotulo-editorial-page`.
- Se añaden clases de alineación/tipografía del subtítulo y variable CSS de color.

5. Ajuste global de rótulo editorial
- Se reduce padding horizontal interno (extremos) en XS/S/M/L/XL para acercar texto al borde del bloque.
- Separación supertítulo/título ajustada por variable global de gap.

## Archivos tocados
- `app/public/wp-content/themes/generatepress-child/functions.php`
- `app/public/wp-content/themes/generatepress-child/page.php`
- `app/public/wp-content/themes/generatepress-child/style.css`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/portada-hero.php`
