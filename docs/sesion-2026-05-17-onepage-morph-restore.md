# Sesión 2026-05-17 — Restaurar morph móvil onepage (patrón 9e17754)

## Contexto

El morph del número SVG en secciones onepage (sólido → trazo con barrido al scroll) **ya funcionaba** con el patrón introducido en el commit `9e17754` (`fix(mobile/onepage): suavizar header, capas SVG y truncado progresivo del titular`).

Intentos posteriores añadieron lógica que rompió el comportamiento:

- Flag `_fiflpOutlined` y overrides de `--onepage-morph-progress` (memoria del morph entre frames).
- Mezcla de número onepage dentro del bloque **cronología** (PHP + CSS + campo ACF `color_trazo_svg_cronologia`).
- Reajuste de z-index que invertía el contrato «contenido por encima del número».

## Referencia buena (no tocar la lógica del morph)

**Commit:** `9e17754`  
**Archivo JS:** `assets/js/editorial.js` → `initOnepageNarrative` → `syncNumberState` (rama `isMobile`)

### Flujo móvil (por sección, sin estado compartido)

Cada `[data-onepage-shell]` es independiente. El morph **no** usa `sessionStorage` ni flags persistentes entre secciones.

| Mecanismo | Uso |
|-----------|-----|
| `is-onepage-numero-sticky` | Número en `position: fixed` al centro del viewport durante el barrido |
| `_fiflpOnepageMorphScrollY0` | Scroll Y al activar sticky; base del progreso 0→1 |
| `_fiflpShellPadTop` | Padding superior de la sección; estimar posición natural con fixed activo |
| `_fiflpWasBelow` | El número pasó por debajo del centro; evita salto en prólogo al cargar |
| `--onepage-morph-progress` | 0 = sólido visible, 1 = trazo visible (clip-path en CSS) |

### Ramas de `syncNumberState` (móvil)

1. **Sticky activo y hay que desactivar** (`nc > centerY + 2` o sección pasó por debajo del número): quita sticky, `morphProgressMobile = 0`, borra flags de scroll.
2. **Número aún no llegó al centro** (`!isFixed && nc > centerY + 0.75`): progreso 0 (sólido).
3. **Prólogo / sección ya arriba sin haber pasado por abajo** (`!isFixed && !wasBelow`): morph por scroll de la sección (`-rect.top`), sin fixed al centro.
4. **Resto**: activa sticky + morph por delta de scroll, o continúa morph si ya está fixed.

El barrido lo pinta solo CSS (`clip-path` extendido en `@media (max-width: 768px)`).

## Qué se restauró en esta sesión

### `style.css` (solo onepage móvil / capas)

Vuelta al contrato de capas de `9e17754`:

- `.seccion-onepage__numero-wrap` → `z-index: -1` (escritorio).
- `.seccion-onepage__contenido-wrap` → `z-index: 12`.
- Móvil: número `10`, contenido `30`, número fixed `10`.
- Eliminado bloque posterior «Contrato de capas» con z-index que competía con el flujo natural.
- Eliminados estilos de número onepage **dentro de cronología** (no mezclar sistemas).

### `cronologia-editorial.php`

Eliminado overlay SVG de número de sección onepage en cronología. La cronología mantiene su propio diseño; el morph del 00/01/02 solo vive en `seccion-onepage.php` + `editorial.js`.

### `acf-json/group_secciones_onepage.json`

Eliminado campo `color_trazo_svg_cronologia` del módulo cronología en onepage (ya no tiene uso en front).

### `editorial.js`

Sin cambios en este commit: el árbol en `main` ya coincide con la rama móvil de `9e17754` (sin `_fiflpOutlined` ni locks).

## Qué NO hacer en el futuro

- No añadir `sessionStorage` ni flags tipo `_fiflpOutlined` / `readMorphLocked` para el morph.
- No reutilizar el número onepage en cronología u otros bloques.
- No subir z-index del número por encima del contenido en móvil.
- No tocar la rama desktop de `syncNumberState` al arreglar móvil.

## Validación manual (móvil ≤768px)

1. **00 PRÓLOGOS:** sólido al inicio → scroll → barrido → trazo; al subir/bajar dentro de la sección el flujo sigue el scroll local.
2. **01:** al entrar por primera vez empieza en sólido (shell propio, sin estado del 00).
3. Sin parpadeo raro entre secciones ni número de cronología superpuesto.
4. Escritorio: sin regresiones (reglas fuera de `@media (max-width: 768px)` para morph de capas).

## Archivos de esta entrega

- `app/public/wp-content/themes/generatepress-child/style.css`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/cronologia-editorial.php`
- `app/public/wp-content/themes/generatepress-child/acf-json/group_secciones_onepage.json`
- `docs/sesion-2026-05-17-onepage-morph-restore.md`
- `AGENTS.md` (nota de referencia)
- `.gitignore` (excluir archivos `Icon` de macOS)
