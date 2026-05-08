# Sesión 2026-05-08 — ACF Cronología (pegado en hitos)

## Problema
En WordPress admin (CPT `fiflp_cronologia`), en el repeater de hitos, los campos:
- `fecha_titulo` (input)
- `texto` (textarea)

no permitían pegar texto de forma fiable (`Cmd+V` o menú contextual).

## Causa raíz
El bloqueo venía del propio script admin de cronología:

- `app/public/wp-content/themes/generatepress-child/assets/js/acf-cronologia-editorial-admin.js`

En versiones intermedias se añadieron interceptores de pegado que anulaban el comportamiento nativo:
- listeners `paste` con `preventDefault()` + `stopPropagation()`
- lógica manual de inserción de texto
- fallback con `navigator.clipboard.readText()`

Esa combinación hacía el pegado inestable o lo anulaba según navegador/contexto de permisos.

## Solución aplicada (mínima y segura)
Se eliminó la lógica de pegado personalizada y se restauró el comportamiento nativo de ACF/browser.

Archivo afectado:
- `app/public/wp-content/themes/generatepress-child/assets/js/acf-cronologia-editorial-admin.js`

Se mantuvo intacto:
- agrupación DOM de subcampos
- título colapsado
- colapsado/expand de filas
- drag/sort de hitos
- composición visual de nodos/hitos

## Verificación técnica
- `node --check app/public/wp-content/themes/generatepress-child/assets/js/acf-cronologia-editorial-admin.js` → OK

## Verificación funcional
1. Abrir un `fiflp_cronologia` en wp-admin.
2. En un hito abierto, pegar en:
   - `fecha_titulo`
   - `texto`
3. Repetir en fila nueva del repeater.
4. Confirmar:
   - pega texto correctamente,
   - no cambia el layout de nodos/hitos,
   - no rompe otros módulos ACF.

Si se ve comportamiento antiguo: recarga dura del admin (`Cmd+Shift+R`).

## Estado
OK
