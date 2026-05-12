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
