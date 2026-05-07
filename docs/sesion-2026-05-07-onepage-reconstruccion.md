# Sesión técnica — Reconstrucción Onepage (2026-05-07)

## Contexto
Trabajo sobre proyecto FIFLP (`main`) para reconstruir el sistema onepage con ACF reutilizable, sin rehacer theme y manteniendo compatibilidad con contenido legacy.

## Objetivo aplicado
- Convertir `seccion_onepage` en referencia a CPT.
- Hacer que el CPT `Secciones Onepage` sea contenedor de composición con módulos flexibles.
- Mantener fallback de datos legacy para no romper contenido previo.
- Ajustar experiencia visual fullwidth/fullscreen y flujo narrativo del `01`.

## Cambios estructurales

### 1) ACF Onepage
Archivo:
- `app/public/wp-content/themes/generatepress-child/acf-json/group_secciones_onepage.json`

Hecho:
- Se añadió `modulos_onepage` (flexible content) con layouts reutilizables:
  - `texto`
  - `imagen`
  - `texto_imagen`
  - `rotulo_editorial`
  - `cronologia_editorial`
  - `prologos`
  - `capitulo`
  - `hero`
- Se añadió selector `Tipografía número` para `01/02/...`:
  - `Upright`
  - `Slanted`
  - `Backslanted`
- Se eliminaron del editor ACF los `items_contenido` legacy para no confundir en admin.

### 2) Render onepage
Archivo:
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/seccion-onepage.php`

Hecho:
- Render prioritario desde `modulos_onepage`.
- Fallback legacy mantenido a nivel de código.
- Número gigante (`01`) renderizado como capa visual con clases para transición sólido/línea.
- Soporte de tipografía del número por campo ACF (`upright/slanted/backslanted`).

### 3) Compatibilidad de bloques reutilizados
Archivo:
- `app/public/wp-content/themes/generatepress-child/functions.php`

Hecho:
- Añadido helper `fiflp_get_sub_field_compat()` para que templates puedan leer datos desde ACF normal o desde módulos onepage.
- Añadido helper `fiflp_onepage_section_has_renderable_content()` para navegación onepage.

Archivos adaptados a compatibilidad de subcampos:
- `template-parts/bloques/texto.php`
- `template-parts/bloques/imagen.php`
- `template-parts/bloques/texto-imagen.php`
- `template-parts/bloques/rotulo-editorial.php`
- `template-parts/bloques/cronologia-editorial.php`
- `template-parts/bloques/prologos.php`
- `template-parts/bloques/capitulo.php`
- `template-parts/bloques/hero.php`

Notas:
- `prologos.php` quedó compatible con `contenido` y `texto` para evitar vacíos por variación de claves.
- `imagen.php` y `rotulo_editorial` incorporan variantes mínimas solicitadas:
  - `linea`
  - `relleno`
  - `linea_inversa`
  - `relleno_inverso`

### 4) CSS/JS onepage
Archivos:
- `app/public/wp-content/themes/generatepress-child/style.css`
- `app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`

Hecho:
- Fullwidth/fullscreen scoped a contexto onepage.
- Menú onepage y layout lateral conservados.
- Número gigante situado a la derecha en desktop y en capa de fondo en móvil.
- Móvil forzado a una sola columna.
- Lógica de scroll por progreso para narrativa del `01`:
  - fase de aparición de título
  - fase de aparición de contenido
  - morph progresivo sólido -> línea durante sección activa
- Ajuste para funcionar tanto con legacy items como con módulos nuevos.

## Validación técnica ejecutada
- `php -l` en PHP tocados: OK.
- JSON `group_secciones_onepage.json`: OK.
- `node --check` en `assets/js/editorial.js`: OK.

## Referencia visual
Se tomó como referencia de comportamiento la web:
- https://2020.milkshake.studio/

## Estado
- Fase estructural: aplicada.
- Fase animación avanzada: base implementada y calibrable por umbrales de scroll.

## Siguiente ajuste recomendado
- Calibrar finamente los umbrales de entrada/morph del `01` con validación visual sobre la sección real activa (la sección onepage con `rotulo_editorial` + `prologos`).
