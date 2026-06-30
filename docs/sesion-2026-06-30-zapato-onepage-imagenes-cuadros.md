# Sesion 2026-06-30 - Zapato onepage, imagenes y cuadros

## Objetivo

Dejar estable la fase actual del sitio editorial FIFLP sin rehacer estructura:

- Onepage estable con menu lateral, cabecera sticky y scroll suave.
- Modulo imagen con comportamientos separados para `A sangre` y `Full page`.
- Pies de foto unificados visualmente.
- Cuadros editoriales con mas control desde ACF.
- Primera fase de optimizacion de carga de imagenes.

## Cambios principales

### Menu onepage y movimiento del contenido

- El menu lateral mantiene el comportamiento de empujar el contenido.
- La apertura y el cierre quedan sincronizados para evitar pausas raras al colapsar.
- El contenido, las imagenes full page y el menu se recalculan durante y despues de la animacion para no quedarse desalineados.

### Imagenes `A sangre` y `Full page`

- `A sangre` vuelve a comportarse como estaba: la imagen arranca alineada con el texto y se pierde hacia la derecha.
- `Full page` queda como opcion separada: ocupa el ancho disponible de pantalla con 10px de aire a izquierda y derecha.
- Al abrir/cerrar el menu lateral, las imagenes full page se recogen y vuelven a expandirse junto con el resto del contenido.
- No se toca la colocacion visual de la imagen cuando ya esta correcta; solo se ajusta su ancho segun el estado del menu.

### Pies de foto

- Los captions del modulo imagen, full page, a sangre, texto-imagen, masonry, WordPress captions y cronologia quedan unificados.
- Tipografia: Manrope.
- Peso: 800.
- Color base: `#1e1e1e`.
- Los pies de foto full page copian el carril visual de los pies de foto a sangre para que no se rompan ni aparezcan en vertical.

### Separacion vertical del modulo imagen

- El modulo imagen deja 30px por encima y 30px por debajo.
- Esta separacion incluye imagen + pie de foto como un bloque completo.
- Aplica a imagen normal, a sangre y full page.

### Cuadros editoriales

Se anaden manejadores ACF al CPT de cuadros editoriales:

- Alineacion del titular: izquierda, centro o derecha.
- Interlineado del texto: valor numerico editable.

Esto permite ajustar el modulo desde WordPress sin tocar codigo.

### Prologos

- Se eliminan fondos no deseados en cajas de texto de prologos.
- Las imagenes de prologos quedan preparadas para carga diferida.

### Optimizacion fase 1

- Se anade `loading="lazy"` y `decoding="async"` en imagenes de modulos editoriales donde es seguro hacerlo.
- La idea es que el navegador no cargue de golpe todas las imagenes largas del libro.
- Se mantienen sin lazy los elementos donde puede afectar a logos, cabecera o piezas especiales.

## Archivos tocados

- `app/public/wp-content/themes/generatepress-child/assets/js/editorial.js`
- `app/public/wp-content/themes/generatepress-child/style.css`
- `app/public/wp-content/themes/generatepress-child/functions.php`
- `app/public/wp-content/themes/generatepress-child/footer.php`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/imagen.php`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/texto-imagen.php`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/prologos.php`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/cronologia-editorial.php`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/retaila-logos.php`
- `app/public/wp-content/themes/generatepress-child/template-parts/bloques/cuadro-markup.php`
- `app/public/wp-content/themes/generatepress-child/acf-json/group_bloques_editoriales.json`
- `app/public/wp-content/themes/generatepress-child/acf-json/group_secciones_onepage.json`
- `app/public/wp-content/themes/generatepress-child/acf-json/group_fiflp_cuadro.json`

## Validaciones realizadas

- Sintaxis PHP revisada con `php -l` en los archivos PHP tocados.
- Sintaxis JS revisada con `node --check`.
- JSON ACF revisado con `python3 -m json.tool`.
- Revision de espacios conflictivos con `git diff --check`.

## Nota WordPress / ACF

Si WordPress no muestra de inmediato los campos nuevos de cuadros editoriales, sincronizar ACF:

1. Entrar en el administrador de WordPress.
2. Ir a Campos personalizados.
3. Revisar si aparece aviso de sincronizacion.
4. Sincronizar los grupos pendientes.

Especialmente importante para:

- `Cuadro editorial`
- `Bloques editoriales`
- `Secciones onepage`

## Estado

Queda como punto estable para seguir metiendo contenido sin tocar de nuevo la base visual salvo ajustes concretos.
