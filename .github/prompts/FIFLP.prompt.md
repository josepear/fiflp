---
name: FIFLP
description: Describe when to use this prompt
---

<!-- Tip: Use /create-prompt in chat to generate content with agent assistance -->

# FIFLP workspace instructions

Cuando trabajes en este proyecto, respeta siempre este flujo operativo.

## Contexto del proyecto
- Proyecto WordPress con ACF Flexible Content
- Repo oficial: `/Volumes/RAID/Repos/web/fiflp`
- Local vivo actual de trabajo: `/Users/josemendoza/Local Sites/fiflp-defi2/app/public`
- Local de contraste si algo parece perdido: `/Users/josemendoza/Local Sites/fiflp-recuperado/app/public`

## Reglas principales
- Si el problema afecta a WordPress real, ACF, admin, bloques o frontend, piensa primero en el Local vivo.
- El repo guarda theme, `acf-json`, PHP, CSS, JS y documentación.
- No asumas que un cambio hecho en el repo ya existe en el Local.
- Si modificas un archivo versionable en el repo, indica explícitamente si luego hay que copiarlo al Local para probarlo.
- No uses `fiflp` antiguo como fuente principal para migraciones o recuperaciones.
- Usa `fiflp-recuperado` solo para comparar o recuperar cosas que falten.

## Estilo de trabajo esperado
- Prioriza diagnóstico corto + cambio mínimo correcto.
- No hagas refactors ni mejoras laterales si no se piden.
- No toques archivos no relacionados.
- Ignora ruido del repo como archivos `Icon` u otros cambios ajenos.
- Si el caso es delicado, diagnostica primero y no edites todavía.
- No hagas commit ni push salvo petición explícita.

## Cuando propongas o hagas cambios
Devuelve siempre, de forma concreta:
1. qué ruta estás usando como base (`repo` o `Local`)
2. qué archivo(s) exactos tocarías
3. si el cambio afecta solo a admin o también a frontend
4. cómo se valida en `fiflp-defi2`
5. si después hay que copiar algo del repo al Local

## Alcance por defecto
- Cambios de ACF/admin: mantener el alcance lo más pequeño posible.
- Cambios de frontend: no mezclar con cambios de admin si no es necesario.
- Cambios de documentación: hacerlos en el repo.
