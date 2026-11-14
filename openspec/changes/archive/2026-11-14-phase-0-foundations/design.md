## Context

- Directorio con `ROADMAP.md`, `openspec/` (vacío, schema `spec-driven`) y `.claude/` (skills de OpenSpec y `release-plan` con plantillas de issue). No hay Git inicializado ni código.
- El repositorio será **público** desde el primer push. Todo lo que entre al historial de Git debe considerarse publicado para siempre.
- Desarrollador único, en Windows 11 con Docker Desktop. Herramientas en el host: PHP 8.5, Composer, Node 24, `pre-commit`. No hay gitleaks.
- Los requisitos no negociables del ROADMAP aplican desde ya: autenticación y autorización sobre cada recurso, integraciones sustituibles por fakes, IA fuera del ciclo HTTP (que implica tener colas desde la Fase 0) y CI obligatorio.

## Goals / Non-Goals

**Goals:**
- Levantar el proyecto con un solo comando, sin PHP ni Node en el host.
- Login y logout funcionales, con el registro imposible desde la web.
- Que el CI falle ante errores de formato, análisis estático, tests o secretos detectados.
- Prevención de secretos en tres capas: hook local, CI y GitHub push protection.
- Que el trabajo se pueda retomar en cualquier momento a partir del estado visible en el repo.

**Non-Goals:**
- Cualquier entidad de dominio (recursos, conceptos, problemas): eso es la Fase 1.
- Imagen de producción, despliegue y HTTPS.
- Reglas de protección de ramas, CODEOWNERS y firma de commits.
- Diseño visual propio: se usa la UI del starter kit.

## Decisions

### D1. Laravel 13 + starter kit oficial React (Inertia)
Se usa el starter kit oficial de Laravel 13 para React: Inertia, React, TypeScript, Tailwind 4 y shadcn/ui, con autenticación vía Fortify.
- **Por qué:** las fases 2, 4 y 5 necesitan mucho trabajo en el cliente (API del reproductor de YouTube, CodeMirror/Monaco, Pyodide y sql.js en web workers, un lienzo tipo Excalidraw). En React todo eso es de primera clase. Inertia evita mantener una API REST separada.
- **Alternativas:** Livewire 4 + Flux (más simple al inicio, pero con fricción en las fases 4 y 5) y Vue (ecosistema menor para editores y lienzos).
- El starter kit se genera **dentro de un contenedor** (`composer create-project` en una imagen PHP) y su contenido se mueve a la raíz del repo. Se conservan `ROADMAP.md`, `openspec/` y `.claude/`.

### D2. PostgreSQL 17 + Redis
- Postgres: jsonb para metadatos y diarios, CTE recursivas para el grafo de prerrequisitos y madurez para los insights.
- Redis: driver de colas y de caché. Se descarta `database` porque la Fase 1 ya obtiene metadatos en segundo plano y la cola en BD compite con las queries normales.
- Los tests usan Postgres real, tanto en local como en CI. No se usa SQLite en memoria, para no divergir en SQL específico.

### D3. Docker Compose propio
Servicios de `compose.yaml`:

| Servicio | Imagen | Rol |
|---|---|---|
| `app` | Dockerfile propio (php:8.4-cli + Node + Composer) | Servidor HTTP (`artisan serve`) y artisan |
| `worker` | misma imagen | `php artisan queue:work` |
| `db` | `postgres:17-alpine` | BD. Volumen con nombre; healthcheck |
| `redis` | `redis:7-alpine` | Colas y caché |
| `vite` | misma imagen que `app` (el plugin de Wayfinder ejecuta `php artisan`) | Dev server con HMR; healthcheck |

- La app se sirve con `php artisan serve` (solo desarrollo; no hay PHP-FPM ni FrankenPHP).
- Un `docker/entrypoint.sh` idempotente hace lo siguiente: crea `.env` desde `.env.example` si no existe, ejecuta `composer install`, genera `APP_KEY` si falta, espera a la BD y aplica las migraciones.
- Comando único: `docker compose up` (alias `make up`). El `Makefile` ofrece `up`, `down`, `test`, `lint`, `fix`, `shell`, `user` y `logs`. En Windows hay una alternativa documentada sin `make`, solo con `docker compose ...`.
- Los puertos se publican solo en `127.0.0.1` y son configurables; por defecto 8100 (app), 5180 (Vite), 5440 (Postgres) y 6390 (Redis) para no chocar con otras instalaciones locales.
- La versión de PHP se fija a la que soporte Laravel 13 con todas las extensiones (`pdo_pgsql`, `redis`, `intl`, `zip`). Se decide en la tarea de Dockerfile y se registra en ADR-0001.
- **Alternativa descartada:** Sail necesita `composer install` previo en el host y está pensado solo para desarrollo.

### D4. Autenticación: Fortify sin registro y comando de creación de usuario
- `Features::registration()` se desactiva en `config/fortify.php` y se eliminan la ruta, la página y los enlaces de registro de la UI.
- Se mantienen el login, el logout, el reset de contraseña (con mail en driver `log` en desarrollo), la verificación de email y 2FA, si el kit los trae. No se añade nada fuera de alcance.
- Comando `deliberia:user:create`:
  - Por defecto es interactivo: pide nombre, email y contraseña (oculta, con confirmación) y la valida con las reglas `Password::defaults()`.
  - Admite opciones `--name`, `--email` y `--password-env=VAR` para uso no interactivo. La contraseña nunca se pasa como argumento literal, porque quedaría en el historial del shell.
  - Falla si el email ya existe.
- El `DatabaseSeeder` **no crea usuarios con credenciales**. Las factories sirven solo para tests.
- Rate limiting del login: el de Fortify por defecto.
- **Autorización:** toda ruta de la app va en el grupo `auth`. Se deja preparada la convención de Policies, que se aplicará con los modelos de la Fase 1.

### D5. Calidad
| Ámbito | Herramienta | Configuración |
|---|---|---|
| Formato PHP | Laravel Pint | preset `laravel` |
| Análisis estático PHP | Larastan | nivel 8, sin baseline |
| Tests PHP | PHPUnit 12 (el del starter kit) | Feature + Unit, Postgres real (`deliberia_test`, forzada en `phpunit.xml`) |
| Formato y lint JS/TS | Vite+ (`vp check`: oxlint + oxfmt) | config del starter kit; limitado al código (no formatea docs ni `.claude/`) |
| Tipos | `tsc --noEmit` | strict |

> Decisión (implementación, 2026-11-11): se adoptan las herramientas del starter kit oficial de Laravel 13 en lugar de Pest, ESLint y Prettier, para no mantener dependencias extra ni divergir del kit.

Los scripts de `composer` (`lint:check`, `types:check`, `test`) y de `npm` (`check`, `types:check`) son la **única** fuente de verdad. CI, el Makefile y los hooks los invocan, nunca duplican flags.

### D6. Protección de datos sensibles (máxima prioridad)
Capas de defensa:
1. **Prevención por diseño**
   - `.gitignore` cubre `.env*` (excepto `.env.example`), `*.pem`, `*.key`, `*.p12`, `auth.json`, `*.sql`, `*.dump`, `*.sqlite`, `storage/logs`, `/vendor`, `/node_modules`, `.phpunit.cache`, `docker/data`, la configuración de IDE, `.claude/settings.local.json`, `.claude/synced/`, `.claude/plans/` y `.codegraph/`.
   - `.env.example` lleva solo placeholders vacíos o valores locales inofensivos (`DB_PASSWORD=deliberia` en el compose local está permitido y documentado como valor solo de desarrollo).
   - Ningún correo real en código, seeders, tests ni configuración. Los tests usan `example.com`, `MAIL_FROM_ADDRESS=hello@example.com`, etc.
2. **Identidad Git**: `git config --local user.email` con el noreply de GitHub, configurado **antes del primer commit**. Se recomienda activar en GitHub "Block command line pushes that expose my email" (README/SECURITY).
3. **Hook local (pre-commit framework)**: `gitleaks` (hook oficial, versión fijada), `check-added-large-files`, `detect-private-key` y Pint sobre los archivos staged. En el stage `commit-msg`, validación Conventional Commits con un hook ligero, sin instalar Node en el hook.
4. **CI**: job `secrets` con gitleaks sobre el historial completo (`fetch-depth: 0`). Se usa el binario de gitleaks directamente (o `gitleaks-action@v2`, gratuito en repos personales). Va primero y bloquea el resto.
5. **GitHub**: secret scanning y push protection, gratuitos en repos públicos. Su activación se documenta como paso manual del usuario.
6. **Auditoría previa a publicar**: `gitleaks detect` sobre todo el historial, más `git log --format='%ae %ce' | sort -u` para comprobar que solo aparece el noreply, más una revisión de `git ls-files`. Es una tarea explícita y bloqueante antes del primer `git push`.
- `.gitleaks.toml` extiende las reglas por defecto. Una allowlist mínima, solo si hay falsos positivos justificados, como `APP_KEY` vacío.

### D7. Flujo de trabajo y continuidad
- **GitHub Flow**: `main` siempre verde. Ramas `feat/<issue>-<slug>`, `fix/...`, `chore/...`, `docs/...`. PR con squash merge. Conventional Commits 1.0.0.
- **Jerarquía de trabajo:**
  - Fase del ROADMAP → change OpenSpec `phase-N-<slug>` (el *epic*: proposal, design, specs y tasks).
  - Grupo de tareas de `tasks.md` → issue de GitHub (sub-issue del epic) → rama → PR.
  - Al cerrar la fase: `/opsx:archive`, y las specs pasan a `openspec/specs/` como fuente de verdad viva.
  - Si una fase es grande, se divide en varios changes (`phase-1-resources`, `phase-1-problems`...), todos enlazados desde la tabla de estado.
- **Estado en `ROADMAP.md`**: una nueva sección "Estado" con una tabla fase → estado (`pendiente`, `en diseño`, `en curso`, `en uso real`, `terminada`) → change(s) OpenSpec → epic de GitHub (cuando exista) → fecha de inicio del uso real (para la regla de las dos semanas).
- **`docs/WORKFLOW.md`**: el ritual para retomar el trabajo. Consiste en ver la tabla de estado, ejecutar `openspec list` y `openspec status --change <x>`, abrir `tasks.md` y buscar la primera casilla sin marcar; luego `/opsx:apply` y, al cerrar el día, marcar las casillas y hacer commit. También cubre cómo nacen las issues (con `release-plan` y las plantillas).
- **ADRs** en `docs/adr/`, formato MADR corto y numerado: `0000-template.md`, `0001-stack.md` y `0002-secrets-and-workflow.md`.
- **Plantillas de GitHub** en `.github/ISSUE_TEMPLATE/`: `feat`, `bug`, `chore` y `spike`, adaptadas de `.claude/skills/release-plan/issue-templates/`, más una nueva `epic.md`. También `pull_request_template.md` y `config.yml` con `blank_issues_enabled: false`.
- **GitHub básico**: `LICENSE` (MIT, con el titular "Deliberia contributors" o el nombre público que elija el usuario, nunca su correo), `SECURITY.md` (reporte por GitHub Private Vulnerability Reporting, sin correo personal), `CONTRIBUTING.md` breve y `.github/dependabot.yml` (composer, npm, github-actions y docker, semanal).

### D8. Pipeline de CI (`.github/workflows/ci.yml`)
- Disparadores: `push` a cualquier rama y `pull_request` a `main`. `permissions: contents: read` a nivel de workflow.
- Jobs:
  - `secrets` (gitleaks).
  - `php-quality` (Pint `--test` y Larastan).
  - `js-quality` (`vp check` y `tsc`).
  - `tests`, que depende de `secrets` y usa un service container `postgres:17` y `redis`. Ejecuta PHPUnit y `npm run build` para detectar roturas de Vite.
- Caché de Composer y npm. Las acciones de terceros se fijan por versión mayor (o SHA en el caso de gitleaks).
- `concurrency` por rama, con cancelación de ejecuciones obsoletas.

## Risks / Trade-offs

- [Un secreto llega a un commit local antes de instalar los hooks] → `make up`/README obligan a ejecutar `pre-commit install`. El CI vuelve a escanear y la auditoría previa al primer push revisa todo el historial. Como todavía no hay remoto, cualquier fuga local se corrige reescribiendo la historia antes de publicar.
- [El correo personal ya existe en commits si se hace un commit antes de configurar el noreply] → La configuración del email es la tarea 1.x, bloqueante. La auditoría D6.6 la verifica.
- [Rendimiento de los bind mounts en Windows (vendor/node_modules)] → Volúmenes con nombre para `vendor` y `node_modules`. Se documenta la opción WSL2.
- [Starter kit con versiones de Inertia o React cambiantes] → Se fijan versiones vía `composer.lock` y `package-lock.json`, y Dependabot propone las subidas.
- [Larastan nivel 8 puede chocar con el código del starter kit] → Se corrige o se tipa. No se usa baseline, para que la deuda no empiece oculta.
- [Licencia de gitleaks-action en organizaciones] → El repo es personal. Si se mueve a una org, se cambia al binario directo.
- [Falsos positivos de gitleaks bloquean commits] → Allowlist en `.gitleaks.toml` con un comentario justificativo por entrada. Nunca se usa `--no-verify` como práctica.

## Migration Plan

No aplica: el proyecto es nuevo. La publicación sigue este orden:
1. Completar las tareas.
2. Hacer la auditoría D6.6.
3. Que el usuario cree el repo vacío en GitHub (sin README ni licencia autogenerados).
4. `git remote add` y `push`.
5. Activar secret scanning, push protection y private vulnerability reporting.
6. Verificar que el CI pasa en verde.

## Open Questions

- Correo noreply de GitHub del usuario: pendiente. Bloquea el primer commit.
- Nombre del titular de la licencia MIT: por defecto "Deliberia contributors", a confirmar.
- PHP 8.4 vs 8.5 en la imagen: se decide según la compatibilidad de Laravel 13 y de las extensiones al construir el Dockerfile.
