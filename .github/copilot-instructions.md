# Copilot instructions

## Repo purpose

This repository contains the versioned code and documentation for the FIFLP website.
The real site depends on three sources of truth:
- the official repo
- the live Local installation on MacMini
- the real WordPress database

## Workflow rules

- Work on `dev`, not on `main`, unless explicitly asked.
- Do not invent a new homepage or editorial structure.
- Preserve the current editorial design, menus, ACF logic and typography.
- If the live site and the repo differ, explain which source is correct before changing code.

## Editing rules

- Treat `wp-content/themes/generatepress-child` as the main versioned theme layer.
- Keep ACF, templates, CSS, JS and theme assets aligned.
- Do not commit `wp-content/uploads`, caches, logs, backups or generated WordPress files.
- Be careful with menus, page templates, Local-specific behavior and database-backed content.
- Prefer restoring the real implementation over improvising a visually similar one.

## Validation

Before finishing, check:
- the homepage structure is correct
- menus still work
- ACF-backed sections still render
- styles and typography still load
- changed files belong in the repo and not in WordPress uploads
