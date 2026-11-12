# ADR-0002: Protección de datos sensibles y flujo de trabajo

- **Estado:** aceptado
- **Fecha:** 2026-11-11
- **Fase / change de OpenSpec:** phase-0-foundations

## Contexto

El repositorio es **público y open source** desde su primer push. Todo lo que entra en el
historial de Git queda publicado de forma permanente: correos personales, tokens,
credenciales y volcados de datos no se pueden retirar de verdad una vez publicados. A la vez,
el trabajo es incremental y diario, y debe poder retomarse a partir de lo que hay en el
repositorio.

## Decisión

### Protección de datos sensibles (defensa en capas)

1. **Prevención por diseño.** `.gitignore` excluye entornos (`.env*` salvo `.env.example`),
   claves, certificados, `auth.json`, volcados (`*.sql`, `*.dump`, `*.sqlite`), logs y
   configuración local de herramientas (`.claude/settings.local.json`, `.claude/plans/`,
   `.codegraph/`). `.env.example` solo contiene marcadores y valores locales de desarrollo,
   comentados como tales. Los tests y fixtures usan dominios reservados (`example.com`).
2. **Identidad de Git.** Los commits usan el correo `noreply` de GitHub, configurado con
   `git config --local` en este repositorio (nunca en la configuración global).
3. **Hook local** (framework `pre-commit`): gitleaks, detección de claves privadas, archivos
   grandes, Pint y validación de Conventional Commits.
4. **CI:** el job `secrets` ejecuta gitleaks sobre todo el historial y bloquea los tests.
5. **GitHub:** secret scanning y push protection (gratis en repositorios públicos), y el
   bloqueo de pushes que exponen el correo personal. Son ajustes manuales del propietario.
6. **Auditoría previa a publicar:** gitleaks sobre todo el historial, revisión de los correos
   de autor y committer, búsqueda de direcciones de correo y revisión de `git ls-files`
   (checklist en [docs/WORKFLOW.md](../WORKFLOW.md)).

Los hooks se pueden saltar con `--no-verify`; por eso el CI y GitHub existen como segunda y
tercera barrera. No se usa `--no-verify` como práctica.

### Flujo de trabajo

- **GitHub Flow:** `main` siempre estable; ramas cortas `<tipo>/<issue>-<slug>`; PR con squash
  merge. Commits en **Conventional Commits 1.0.0**.
- **Una fase del ROADMAP = un change de OpenSpec = una issue epic.** Los grupos de tareas del
  `tasks.md` del change se convierten en sub-issues. Una fase grande puede dividirse en varios
  changes, todos enlazados desde la tabla de estado del ROADMAP.
- El **estado de las fases** vive en la sección "Estado" de `ROADMAP.md`, y el estado de las
  tareas en las casillas del `tasks.md` del change activo. Al terminar una fase se archiva su
  change y sus specs pasan a `openspec/specs/`.
- Las decisiones relevantes se registran como ADR en `docs/adr/`.

## Alternativas consideradas

- **Solo confiar en `.gitignore`:** insuficiente; no detecta un secreto pegado en código.
- **Escaneo solo en CI:** detecta tarde, cuando el secreto ya está en un commit; con el
  repositorio público basta un push para publicarlo.
- **Reglas de protección de rama complejas, CODEOWNERS y commits firmados:** excesivos para un
  proyecto personal de un solo desarrollador; se pueden añadir si hay colaboradores.
- **GitFlow:** más ceremonia (`develop`, ramas de release) de la que necesita este proyecto.
- **Issues de GitHub como única fuente del estado:** acopla el trabajo a la plataforma y exige
  tener el repositorio remoto desde el primer día.

## Consecuencias

- Un commit puede tardar unos segundos más por los hooks.
- Si un secreto llega a publicarse, hay que **revocarlo** (reescribir el historial no basta).
- El estado de las fases hay que mantenerlo al día en el ROADMAP al empezar y cerrar cada fase.
