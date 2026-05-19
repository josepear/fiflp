# Sesión 2026-05-19 — Unificación rótulo Página/Portada Hero + ajustes logos

## Objetivo
- Unificar el comportamiento del rótulo editorial entre Página y Portada Hero.
- Corregir visibilidad del rótulo en móvil.
- Ajustar distribución de logos institucionales en tablet/móvil sin romper el orden editorial por líneas.

## Cambios realizados

### 1) Rótulo en móvil no visible
- Archivo: `app/public/wp-content/themes/generatepress-child/style.css`
- Se corrigió una regla global móvil que ocultaba todos los `.rotulo-editorial`.
- Ahora la ocultación se limita a contexto onepage:
  - `body.fiflp-onepage .rotulo-editorial ... { display:none !important; }`

### 2) Unificación de lógica JS del rótulo (Página + Portada Hero)
- Archivo: `app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`
- `fitRotuloText()` en móvil deja de forzar autoajustes JS para cualquier rótulo `context-page`.
- Resultado: misma lógica móvil para Página y Portada Hero en el rótulo editorial.

### 3) Portada Hero pasa todos los parámetros del rótulo
- Archivo: `app/public/wp-content/themes/generatepress-child/template-parts/bloques/portada-hero.php`
- Se amplió el payload enviado a `rotulo-editorial-page` con campos completos:
  - variantes, tamaño, alineaciones, colores, etiqueta HTML,
  - interlineados/espaciados,
  - `titulo_lineas`,
  - color y control de subtítulo.
- Se añadió fallback `rotulo_* -> estándar` (`titulo`, `supertitulo`, etc.) para evitar divergencias por naming.

### 4) Márgenes externos del bloque de rótulo
- Archivo: `app/public/wp-content/themes/generatepress-child/style.css`
- Se fijó explícitamente para bloque Página/context-page:
  - `.bloque.rotulo-editorial-bloque.rotulo-editorial-bloque--page { margin:0; padding:0; }`

### 5) Logos institucionales (retaila) en tablet/móvil
- Archivo: `app/public/wp-content/themes/generatepress-child/template-parts/bloques/retaila-logos.php`
- Se respeta el orden editorial de líneas ACF (primera y segunda línea).
- Si llegan más de 2 líneas legacy, se mantiene línea 1 y se compacta resto en línea 2.

- Archivo: `app/public/wp-content/themes/generatepress-child/style.css`
- Ajustes de grid para tablet/móvil:
  - separación vertical ampliada,
  - separación horizontal por línea,
  - tamaños de slot con escalado proporcional,
  - ajuste específico de segunda línea en tablet.

## Resultado esperado
- El rótulo editorial vuelve a verse en móvil.
- Página y Portada Hero comparten la misma entrada de parámetros y la misma lógica móvil para rótulo `context-page`.
- Los logos mantienen orden de línea editorial y mejoran separación en tablet/móvil.

## Nota
- Queda pendiente validación visual final 1:1 en frontend (Página vs Portada Hero) con inspección de clases/computed styles para confirmar equivalencia total de geometría.
