# Sesión 2026-05-13 — Ajustes onepage móvil (header, SVG, titular)

## Objetivo
Pulir el comportamiento móvil de la onepage sin tocar escritorio:
- Header con fondo continuo y padding superior/inferior.
- Jerarquía visual fija: fondo < SVG número < contenido.
- Eliminar salto vertical al fijar SVG.
- Transición de color del header progresiva entre secciones.
- Titular: llega arriba completo y luego se recorta con estilo máquina de escribir hasta quedar en una línea con `...`.

## Archivos tocados
- `app/public/wp-content/themes/generatepress-child/style.css`
- `app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`

## Cambios principales
1. Header móvil
- Fallback de color del header en onepage móvil a amarillo mientras JS calcula color de sección.
- Padding superior +10px sobre safe-area y padding inferior +5px.
- Transición de color del header alargada para cambio más suave entre secciones.

2. Capas visuales (móvil)
- Se fuerza jerarquía de z-index para que el SVG no tape contenido:
- SVG (`.seccion-onepage__numero-wrap`) por debajo del contenido.
- Contenido (`.seccion-onepage__contenido-wrap`) por encima del SVG.
- Titular sticky por encima del contenido.

3. Salto vertical al pasar SVG a fixed
- Se igualó el espacio reservado por el placeholder (`::before`) con el margen inferior del SVG (`28px`) para evitar salto al activar estado sticky.

4. Transición de color entre secciones
- Se sustituyó la selección por “sección más visible” por un blend progresivo sección->sección según avance del scroll.
- Ejemplo: amarillo->gris->verde según entrada de cada nueva sección al área del header.

5. Titular y puntos suspensivos (móvil)
- El titular llega arriba completo.
- Con scroll adicional, empieza recorte progresivo (máquina de escribir invertida) y termina en una línea con `...`.
- El estado de anclaje (`docked`) se calcula por distancia real al header (px) con histéresis para evitar parpadeo.
- El cálculo del recorte se mide en modo de una línea real para que el truncado sea consistente.

## Notas
- Se añadió debug temporal en móvil para diagnosticar estados del titular y se eliminó al cerrar la corrección.
- No se tocaron reglas de escritorio de forma intencionada; ajustes centrados en flujo móvil onepage.

---

## Update: Lightbox (móvil + general)

### Objetivo
Refinar UX del lightbox sin romper el flujo editorial:
- Foto protagonista con esquinas redondeadas.
- Sin botón lupa visible.
- Zoom/deszoom con tap en la imagen.
- Apertura/cierre suaves.
- Cierre en botón circular (borde 3px), posición estable.
- Drag en zoom para recorrer la imagen.
- Sin barra de desplazamiento visible en lateral.

### Cambios aplicados
1. Estructura visual
- `lightbox` pasa a estado con transición (`.lightbox--open`) para fade suave al abrir/cerrar.
- Caption oculto para priorizar imagen.
- Fondo oscurecido con más transparencia (`rgba(0,0,0,0.68)`).

2. Imagen y zoom
- Lupa oculta por CSS (`.lightbox-zoom { display:none !important; }`).
- Click/tap sobre imagen: toggle ampliar/reducir.
- Zoom suavizado (transición en `width/height/transform`).
- Deszoom también suavizado (sin salto brusco).
- Factor de zoom moderado para mejor paneo: `1.25`.

3. Drag/Pan en modo ampliado
- Cursor `grab/grabbing` en zoom.
- Arrastre con pointer events para mover la imagen ampliada.
- Protección anti-click accidental tras drag.

4. Botón cerrar
- Botón circular con `border: 3px`.
- Posición fija superior derecha para que no "baile" durante zoom/deszoom.
- Ajuste de centrado visual del símbolo `×` dentro del círculo.

5. Scrollbars
- Ocultas en viewport interno del lightbox.
- Scroll global de `body` bloqueado mientras el lightbox está abierto.

### Ajustes descartados en esta tanda
- Se probó heredar `multiply` al lightbox para imágenes con blend, pero se retiró por solicitud (se dejó el comportamiento previo en lightbox para ese punto).

### Archivos tocados (update)
- `app/public/wp-content/themes/generatepress-child/style.css`
- `app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`
