# FIFLP: Contexto de trabajo para Copilot

## Rama
- Trabaja sobre `dev`.
- No rehagas nada desde cero.
- No cambies la arquitectura del proyecto.

## Proyecto
Web editorial en WordPress con:
- GeneratePress Child
- ACF Flexible Content
- JS vanilla
- CSS editorial propio

Objetivo del proyecto:
- experiencia tipo libro
- navegación editorial clara
- bloques ACF reutilizables
- sistema visual cuidado

## No tocar
- No tocar Nextcloud.
- No tocar Cloudflare.
- No mover la estructura del theme hijo.
- No reintroducir comportamiento onepage.
- No meter frameworks JS ni rehacer el frontend.
- No cambiar la lógica base del menú ni de los prólogos sin necesidad real.

## Estructura importante
- `app/public/wp-content/themes/generatepress-child/page.php`
  Render principal de bloques ACF.
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/*`
  Partials de cada layout editorial.
- `app/public/wp-content/themes/generatepress-child/template-parts/menu-lateral.php`
  Menú editorial lateral.
- `app/public/wp-content/themes/generatepress-child/template-parts/editorial-prologos-page.php`
  Render específico de la página de prólogos.
- `app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`
  Interacciones: lightbox, menú, reveal, etc.
- `app/public/wp-content/themes/generatepress-child/style.css`
  Sistema visual principal.
- `app/public/wp-content/themes/generatepress-child/functions.php`
  Helpers, hooks, menú global, helpers de imágenes/logos, utilidades editoriales.

## Cómo está resuelto ahora

### Navegación
- Hay menú global tipo índice y menú lateral editorial.
- Los menús con hijos son desplegables.
- El desplegable debe abrir/cerrar tanto desde el botón `+ / −` como desde el texto.
- La navegación editorial no es onepage.
- No usar scroll por anclas para capítulos.

### Prólogos
- Los prólogos se gestionan desde ACF.
- Existe soporte específico para página de prólogos.
- Se renderiza un prólogo por vez según query param.
- No convertir prólogos a páginas hijas si no se pide explícitamente.

### Logos
- Hay helpers para normalizar imágenes/logos en `functions.php`.
- Los logos deben soportar ACF en formato:
  - array
  - ID
  - URL
- No tratar los logos como fotos editoriales.
- Los logos no deben heredar bordes redondeados, recortes ni estilos de foto.
- Casos sensibles:
  - logo del footer
  - logo principal del home hero
  - logos colaboradores
  - logo centenario de cabecera

### Cabecera
- La cabecera está fija.
- El logo centenario va centrado en la cabecera.
- El branding principal y el disparador del índice ya están colocados con una lógica concreta.
- No romper esa composición.

### Tipografía
- Menús: `FKScreamer Upright`
- Elemento activo del menú con taco negro: `FKScreamer Slanted`
- Títulos editoriales y rótulos: familia `FKScreamer`
- Subtítulos y cargos/meta: `Manrope`
- Texto de lectura: `Source Serif 4`
- No mezclar tipografías arbitrariamente.

### Rótulos editoriales
- El bloque `rotulo_editorial` ya tiene una lógica visual concreta.
- Tiene variantes ACF.
- No rehacer sus ángulos, SVG ni composición salvo que el ajuste sea puntual y muy justificado.
- Si algo falla en un caso concreto, ajustar el componente, no desmontarlo.

## ACF
- Mantener ACF Flexible Content como sistema principal de bloques.
- Ya existen JSON locales en:
  - `app/public/wp-content/themes/generatepress-child/acf-json/`
- Si se añaden campos, deben tener sentido editorial y no duplicar lógica ya existente.
- Priorizar controles útiles para edición:
  - variantes
  - tipografía/jerarquía cuando aplique
  - colores
  - opciones de apariencia reales

## CSS y JS
- Mantener la lógica central en los archivos ya existentes.
- Evitar repartir una sola responsabilidad en demasiados sitios.
- Si se mejora algo global, hacerlo en el sistema existente, no con parches duplicados.
- Prioridades de UX:
  - suavidad en menús y transiciones
  - responsive limpio
  - proporción correcta en móvil, tablet y escritorio
  - claridad visual del índice y estados activos

## Regla de trabajo
Antes de tocar algo, comprobar:
1. si ya existe helper o clase para hacerlo
2. si el cambio rompe el flujo editorial
3. si el ajuste debe hacerse en `functions.php`, en el partial concreto o en `style.css`

## Prioridad técnica
1. Que funcione
2. Que no rompa lo existente
3. Que sea limpio
4. Que sea coherente editorialmente

## Si hay duda
- Comparar siempre contra la lógica actual de `dev`
- Hacer cambios mínimos
- No reinventar el proyecto
