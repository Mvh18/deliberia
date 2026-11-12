## Why

Deliberia no tiene código todavía y será un repositorio **público y open source** desde el primer push. Antes de construir cualquier funcionalidad (Fase 1+) necesitamos una base reproducible, con calidad automatizada y —máxima prioridad— una línea base que impida filtrar datos sensibles (correos personales, tokens, credenciales, dumps) al historial de Git, donde serían permanentes.

Además, el trabajo será incremental y diario: el ROADMAP.md debe funcionar como documento guía y el estado de cada fase debe ser visible en el repo para poder retomar el trabajo en cualquier momento.

## What Changes

- Esqueleto de aplicación **Laravel 13** con el starter kit oficial **Inertia + React + TypeScript + Tailwind 4 + shadcn/ui**, sobre **PostgreSQL** y **Redis**.
- **Docker Compose propio** (app, worker de colas, Postgres, Redis, Vite) que levanta todo con un solo comando, sin requerir PHP/Node en el host.
- **Autenticación** con Fortify: login/logout, registro **deshabilitado**, primer usuario creado con el comando `php artisan deliberia:user:create` (interactivo o vía variables de entorno de un `.env` no versionado).
- **Línea base de protección de datos sensibles**: `.gitignore` robusto, `.env.example` sin valores reales, gitleaks en hook pre-commit y en CI, correo noreply de GitHub en la configuración local del repo, auditoría completa del historial antes de publicar, `SECURITY.md`.
- **Herramientas de calidad**: Pint, Larastan, Pest 4 (backend); ESLint, Prettier, `tsc` (frontend); hooks con el framework `pre-commit` (gitleaks, formato, commit-msg con Conventional Commits).
- **CI en GitHub Actions** en cada push y PR: secretos, formato, análisis estático y tests contra Postgres.
- **GitHub básico**: licencia MIT, Dependabot, plantillas de issue (feat/bug/chore/spike/epic) y de PR, instrucciones para activar secret scanning y push protection. Sin reglas de protección de rama complejas.
- **Flujo de trabajo documentado**: GitHub Flow + Conventional Commits, `docs/adr/` con ADR-0001 (stack) y ADR-0002 (secretos y flujo de trabajo), `docs/WORKFLOW.md` (cómo retomar el trabajo), tabla de **Estado** en ROADMAP.md, y la convención "una fase = un change de OpenSpec = una issue epic".

## Capabilities

### New Capabilities
- `user-auth`: inicio y cierre de sesión, registro deshabilitado, creación del usuario mediante comando, protección de rutas autenticadas.
- `dev-environment`: entorno reproducible con Docker levantable con un solo comando, configuración por variables de entorno.
- `quality-pipeline`: formato, análisis estático y tests ejecutables localmente y en CI; el CI falla ante cualquier error.
- `secrets-protection`: prevención, detección y auditoría de datos sensibles en el repositorio y su historial.
- `project-workflow`: convenciones de ramas y commits, ADRs, estado de fases en el ROADMAP, mapeo fase → change OpenSpec → epic de GitHub.

### Modified Capabilities
<!-- Ninguna: no existen specs previas en openspec/specs/. -->

## Impact

- Repositorio nuevo: se inicializa Git, se crea toda la estructura de la aplicación.
- Dependencias: Laravel 13, Fortify, Inertia, React, Pest, Larastan, Pint, ESLint, Prettier; imágenes Docker de PHP, Postgres, Redis y Node; gitleaks y pre-commit como herramientas de desarrollo.
- `ROADMAP.md` se modifica solo para añadir la sección de Estado (el contenido de producto no cambia).
- El usuario debe proporcionar su correo noreply de GitHub antes del primer commit y crear el repositorio remoto en GitHub.
- Fuera de alcance: cualquier funcionalidad de Fase 1, despliegue a producción, reglas de protección de rama, UI más allá del starter kit.
