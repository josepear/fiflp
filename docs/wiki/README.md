# Wiki del proyecto

## Objetivo
`fiflp` es la web y base editorial/técnica del proyecto FIFLP.

## Ruta oficial de trabajo
`/Volumes/RAID/Repos/web/fiflp`

## Rama recomendada de trabajo
`dev` para cambios importantes y de integración.

## Fuente de verdad del proyecto
La web real depende de tres patas:
- repo oficial
- instalación viva en Local del MacMini
- base de datos real del sitio WordPress

## Qué va al repo
- `wp-content/themes/generatepress-child`
- plantillas PHP
- CSS y JS
- `acf-json`
- assets propios del theme
- documentación técnica

## Qué no va al repo
- `wp-content/uploads`
- cachés
- thumbnails generados
- backups
- logs
- archivos temporales de WordPress

## Nota sobre imágenes editoriales
Varias imágenes recuperadas de la web real viven en `wp-content/uploads/...` y deben mantenerse en la instalación viva de WordPress, no en Git.

Ejemplos ya restaurados en Local:
- `david-garcia.jpeg`
- `FERNANDO-CLAVIJO.jpg`
- `ANTONIO-MORALES.jpg`
- `POLI-SUAREZ.jpg`

## Qué revisar siempre antes de tocar
- tema hijo activo
- ACF y grupos de campos
- portada configurada
- menús asignados
- assets del theme
- `uploads` usados realmente por el frontend
- posibles diferencias entre copia viva y repo

## Qué conviene documentar mejor
- URL local y/o pública actual
- cómo se arranca
- dónde está el contenido crítico
- qué carpetas son runtime y cuáles son código

## Nota operativa de túneles en el MacMini
En el host del proyecto conviven al menos dos túneles `cloudflared` distintos:

- FIFLP publica `https://fiflp.pearandco.es`
- Nextcloud publica `https://cloud.pearandco.es`

Para el incidente revisado hoy, la configuración real verificada de Nextcloud es esta:

- túnel: `nextcloud`
- tunnel ID: `efd22938-a1cd-43a7-a9ad-5d68820fe98b`
- backend local: `http://localhost:8080`
- gestión del conector: `launchd`

Conclusión operativa útil:

- un `Error 1033` en `cloud.pearandco.es` no implica por sí solo un problema de DNS
- tras actualizar o reiniciar Docker puede aparecer un `1033` transitorio mientras `cloudflared` recupera un conector sano y el contenedor `nextcloud_app` vuelve a estar disponible
- si `localhost:8080` responde y el túnel `nextcloud` vuelve a tener conexiones activas en edge, no conviene rehacer DNS ni recrear el túnel sin más evidencia

## Guías operativas
- [Local Site, Base de Datos y Flujo Real de Trabajo](./LOCAL-SITE-Y-FLUJO.md)

## Estado operativo actual
- Local de trabajo actual: `fiflp-defi2` (`http://localhost:10018`)
- Local de contraste: `fiflp-recuperado`
- Nota: la migración correcta salió de `fiflp-recuperado` y el bloque `Imagen` se recuperó en repo durante la sesión del 2026-04-05
