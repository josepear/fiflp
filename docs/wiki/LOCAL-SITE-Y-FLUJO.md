# Local Site, Base de Datos y Flujo Real de Trabajo

## Resumen corto
En `fiflp` hay que distinguir entre tres capas:

1. **Instalación viva de WordPress en Local (MacMini)**
2. **Repo oficial en GitHub**
3. **URL pública publicada por túnel**

La regla práctica es esta:

- si un cambio afecta a WordPress real, ACF, admin, bloques o frontend: **primero Local Site**
- cuando ya funciona y está validado: **después repo**
- cuando ya está en el repo bueno: **commit y push a `dev`**

## Mapa real del proyecto

### 1. Instalación viva en Local
Esta es la copia que manda de verdad para WordPress:

- MacMini: `/Users/josemendoza/Local Sites/fiflp/app/public`
- Desde otras máquinas montadas por volumen: `/Volumes/josemendoza/Local Sites/fiflp/app/public`

Aquí viven:
- el WordPress real
- el tema hijo activo
- el admin real
- los grupos ACF que usa la web
- la base de datos conectada por Local
- el contenido que se ve en local y en la pública

### 2. Repo oficial versionado
Esta es la copia para Git y GitHub:

- `/Volumes/RAID/Repos/web/fiflp`

Aquí deben vivir:
- `wp-content/themes/generatepress-child`
- `acf-json`
- PHP, CSS, JS y assets propios del theme
- documentación técnica

Aquí **no** vive el WordPress en ejecución.

### 3. Web pública
La URL pública es:

- `https://fiflp.pearandco.es`

Esa web pública sale de la instalación viva del MacMini publicada por túnel.

## Cómo entrar según la máquina

### Si estás en el MacMini
Estas son las URLs buenas para trabajar:

- web local: `http://localhost:10003`
- admin local: `http://localhost:10003/wp-admin`
- pública: `https://fiflp.pearandco.es`

### Si estás en el MacBook u otra máquina
Puedes hacer dos cosas:

- abrir la pública: `https://fiflp.pearandco.es`
- editar archivos si tienes montado el volumen de Local Site

Pero **no** debes usar `localhost:10003` desde otra máquina, porque ese servicio corre en el MacMini.

## Qué URL usar para cada cosa

### Para trabajar en WordPress y ACF
Usar preferentemente:

- `http://localhost:10003/wp-admin`

### Para comprobar cómo se ve de cara fuera
Usar:

- `https://fiflp.pearandco.es`

### Nota sobre túneles compartidos en el MacMini
El MacMini no publica solo FIFLP. También mantiene un túnel independiente para Nextcloud.

Datos operativos verificados del túnel de Nextcloud:

- nombre del túnel: `nextcloud`
- tunnel ID: `efd22938-a1cd-43a7-a9ad-5d68820fe98b`
- hostname público: `https://cloud.pearandco.es`
- backend local: `http://localhost:8080`
- arranque del conector: `launchd`

Esto importa porque, después de una actualización o reinicio de Docker Desktop, puede aparecer temporalmente un `Error 1033` en `cloud.pearandco.es` aunque DNS y la asociación del hostname sean correctos.

Interpretación práctica:

- el `1033` suele indicar pérdida temporal de conector sano en Cloudflare
- primero hay que confirmar que `launchd` sigue levantando `cloudflared`
- después hay que confirmar que `nextcloud_app` ha recuperado el backend en `localhost:8080`
- si ambas cosas vuelven a estar sanas, no hay evidencia para tocar DNS ni rehacer el túnel

### Nota sobre `fiflp.local`
Durante esta sesión se comprobó que `fiflp.local` no era la URL más fiable para trabajo diario.

Mientras no se estabilice del todo, la URL local de referencia debe ser:

- `http://localhost:10003`

## Base de datos: qué manda y qué no
La base de datos real de `fiflp` está gestionada por **Local** en el MacMini.

Eso implica:
- el contenido de WordPress no está en el repo
- ACF en admin y el contenido editorial real dependen de la instalación viva y su DB
- Git no sustituye la DB
- copiar solo el repo no reconstruye el sitio completo

En la práctica:
- tema y `acf-json` sí van al repo
- contenido, menús, páginas, opciones y estado del admin viven en la DB de Local

## Configuración operativa de `wp-config.php`
La lección importante de esta sesión es:

- no conviene improvisar `wp-config.php` sin validar
- los cambios de `DB_HOST`, caché o URLs pueden romper local aunque la pública siga viva
- la base de datos local funcionó con `DB_HOST = 'localhost'`
- la URL local fiable para trabajo fue `localhost:10003`

Regla práctica:
- si `localhost:10003` funciona, no tocar `wp-config.php` “por limpiar” sin necesidad
- cualquier cambio en `wp-config.php` hay que validarlo inmediatamente en local y en la pública

## Flujo correcto de trabajo para FIFLP

### Caso 1: cambios de WordPress real, ACF, tema o frontend
1. Abrir la instalación viva en el MacMini
2. Trabajar en `Local Site`
3. Validar en:
   - `http://localhost:10003`
   - `http://localhost:10003/wp-admin`
   - `https://fiflp.pearandco.es`
4. Copiar al repo **solo** los archivos buenos y versionables
5. Ir al repo oficial
6. `git status`
7. `git add ...`
8. `git commit -m "mensaje claro"`
9. `git push origin dev`

### Caso 2: cambios puramente de código o documentación
Si no afectan al WordPress vivo, se puede trabajar directamente en el repo oficial:

- `/Volumes/RAID/Repos/web/fiflp`

## Qué sí se sube al repo
- `app/public/wp-content/themes/generatepress-child`
- `acf-json`
- PHP del theme
- CSS del theme
- JS del theme
- assets propios del theme
- documentación técnica

## Qué no se sube al repo
- `uploads`
- cachés
- logs
- backups
- temporales
- estado de la base de datos

## Lo que aprendimos al corregir el cambio de alineación
El cambio de alineación del subtítulo dejó una lección útil para futuros cambios:

- el repo no era la copia viva que estaba usando WordPress
- el sitio real estaba corriendo desde Local Site en el MacMini
- validar antes de commit evitó guardar una solución en la copia equivocada
- primero hubo que dejar estable la instalación viva
- solo después se pasaron al repo los archivos buenos

## Regla de oro
Para `fiflp`:

**Local Site primero. Repo después. GitHub al final.**

## Checklist rápido antes de tocar nada
- confirmar si estás en MacMini o en otra máquina
- confirmar si el cambio afecta al WordPress real
- si afecta a WordPress real: abrir `localhost:10003`
- comprobar que el theme activo correcto es `generatepress-child`
- validar en admin antes de commit
- subir al repo solo archivos versionables

## Prompt base para retomar trabajo en el MacMini
Usa este prompt como punto de partida para futuros cambios en `fiflp`:

```text
Trabaja sobre la instalación viva de Local de FIFLP en el MacMini.

Ruta viva:
/Users/josemendoza/Local Sites/fiflp/app/public

Repo oficial:
/Volumes/RAID/Repos/web/fiflp

Reglas:
- si el cambio afecta a WordPress, ACF, admin, bloques o frontend real, trabaja primero en Local Site
- valida en http://localhost:10003 y en https://fiflp.pearandco.es
- no uses el repo como fuente principal para cambios de WordPress vivo
- cuando el cambio funcione, dime exactamente qué archivos versionables hay que pasar al repo oficial
- no hagas commit hasta que yo valide el cambio

Devuélveme siempre:
1. qué ruta viva estás usando
2. qué archivos has tocado en Local
3. cómo lo has validado
4. qué archivos hay que copiar al repo
5. riesgos o diferencias detectadas entre Local y repo
```

## Estado tras la migración a `fiflp-defi2` (2026-04-05)
Durante esta sesión se confirmó que la copia de origen correcta para reconstruir el Local definitivo no era `fiflp`, sino `fiflp-recuperado`.

Estado operativo útil a partir de ahora:

- Local de trabajo actual: `/Users/josemendoza/Local Sites/fiflp-defi2/app/public`
- URL local actual de trabajo: `http://localhost:10018`
- Copia de referencia para contrastar contenido y admin: `/Users/josemendoza/Local Sites/fiflp-recuperado/app/public`
- La instalación `fiflp` antigua se conserva solo como referencia adicional, no como fuente principal para migraciones nuevas.

### Lecciones prácticas de esta migración
- `fiflp-definitivo` se descartó como intento intermedio porque la fuente usada no conservaba bien el estado útil de ACF.
- La base funcional buena salió de `fiflp-recuperado`.
- Para validar una migración de WordPress no basta con ver el frontend: también hay que comprobar en admin que los layouts ACF y sus campos siguen apareciendo correctamente.
- Los enlaces de descarga del hero pueden quedar bien en la DB y aun así fallar si los ficheros físicos no existen en `uploads`.

### Recuperaciones confirmadas en repo durante esta sesión
Se recuperaron y consolidaron en el repo dos piezas clave del bloque `Imagen`:

- campos avanzados del layout `Imagen` en `acf-json/group_bloques_editoriales.json`
- comportamiento `a sangre` en `style.css`

Referencia útil en historial Git:

- commit de recuperación reciente: `338cd2a` `restore advanced image block fields and full-bleed behavior`
- commit histórico que sirvió de referencia para reconstruir el bloque: `2d729b0` `construccion-elementos-imagen`

## Prompt recomendado para ChatGPT / GitHub Chat / Copilot
Si vas a pedir ayuda para seguir trabajando sobre FIFLP, usa una instrucción base como esta:

```text
Trabaja sobre FIFLP distinguiendo claramente entre repo y Local vivo.

Repo oficial:
/Volumes/RAID/Repos/web/fiflp

Local de trabajo actual:
/Users/josemendoza/Local Sites/fiflp-defi2/app/public

Local de referencia para contrastar si algo parece perdido:
/Users/josemendoza/Local Sites/fiflp-recuperado/app/public

Reglas:
- si el cambio afecta a WordPress real, ACF, bloques, admin o frontend, toma como base `fiflp-defi2`
- usa `fiflp-recuperado` solo para comparar y recuperar cosas que falten
- no uses `fiflp` antiguo como fuente principal de migración
- antes de dar por buena una migración o recuperación, valida también en ACF/admin, no solo en frontend
- si detectas diferencias entre Local y repo, dime exactamente qué archivos versionables hay que pasar al repo
- no hagas commit hasta que yo valide el resultado

Devuélveme siempre:
1. qué ruta Local estás usando
2. si estás comparando contra `fiflp-recuperado`
3. qué archivos has tocado
4. cómo lo has validado
5. qué debería guardarse en repo
```
