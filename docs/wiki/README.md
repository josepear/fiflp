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
