## 1. Repositorio e identidad segura (BLOQUEANTE antes de cualquier commit)

- [x] 1.1 Obtener del usuario su correo noreply de GitHub (`<ID>+<usuario>@users.noreply.github.com`) y el nombre público para commits/licencia
- [x] 1.2 `git init -b main` y configurar `git config --local user.name` / `user.email` con el noreply; verificar con `git config --local --get user.email`
- [x] 1.3 Crear `.gitignore` robusto (Laravel + Node + `.env*` excepto `.env.example`, claves, `auth.json`, dumps `*.sql`/`*.dump`, `*.sqlite`, logs, `docker/data`, IDE, `.claude/settings.local.json`, `.claude/synced/`, `.claude/plans/`, `.codegraph/`) y `.gitattributes` (LF, binarios)
- [x] 1.4 Revisar `.claude/` en busca de rutas personales, correos o tokens antes de versionarlo; eliminar la carpeta duplicada `openspec-apply-change copy`
- [x] 1.5 Crear `.pre-commit-config.yaml` con gitleaks (versión fijada), `detect-private-key`, `check-added-large-files`, `check-merge-conflict`, `end-of-file-fixer` y hook `commit-msg` de Conventional Commits; crear `.gitleaks.toml` extendiendo reglas por defecto
- [x] 1.6 `pre-commit install --hook-type pre-commit --hook-type commit-msg` y verificar: un token falso de GitHub en un archivo de prueba bloquea el commit; el mensaje "cambios varios" es rechazado
- [x] 1.7 Primer commit `chore: initialize repository with security baseline` (solo ROADMAP, openspec, .claude, ignores y hooks) y verificar autor con `git log --format='%ae %ce'`

## 2. Esqueleto Laravel 13 + Inertia React

- [x] 2.1 Generar el starter kit oficial de React de Laravel 13 dentro de un contenedor temporal (sin depender del PHP del host) en un directorio aparte y moverlo a la raíz conservando ROADMAP, openspec y .claude
- [x] 2.2 Configurar PostgreSQL y Redis como defaults (`DB_CONNECTION=pgsql`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `SESSION_DRIVER=redis` o `database`) y revisar `.env.example`: sin secretos, `APP_KEY` vacío, `MAIL_MAILER=log`, `MAIL_FROM_ADDRESS=hello@example.com`, comentarios en valores solo-dev
- [x] 2.3 Fijar nombre de app "Deliberia" en config y UI básica del kit
- [x] 2.4 Commit `feat: scaffold Laravel 13 with Inertia React starter kit`

## 3. Entorno Docker de un solo comando

- [x] 3.1 Crear `docker/php/Dockerfile` (PHP compatible con Laravel 13 con `pdo_pgsql`, `redis`, `intl`, `zip`, `pcntl`, Composer) usuario no-root; decidir PHP 8.4 vs 8.5 y anotarlo para ADR-0001
- [x] 3.2 Crear `docker/entrypoint.sh` idempotente: copiar `.env.example` → `.env` si falta, `composer install`, `key:generate` solo si `APP_KEY` vacío, esperar BD, `migrate --force`
- [x] 3.3 Crear `compose.yaml` con `app`, `worker`, `db` (postgres:17-alpine, healthcheck, volumen nombrado), `redis`, `vite` (node:24, `npm ci` + `npm run dev`); volúmenes nombrados para `vendor` y `node_modules`; todos los puertos en `127.0.0.1`
- [x] 3.4 Configurar Vite para HMR dentro de Docker (host `0.0.0.0`, `hmr.host=localhost`)
- [x] 3.5 Crear `Makefile` (`up`, `down`, `logs`, `shell`, `test`, `lint`, `fix`, `user`, `fresh`) que solo envuelva `docker compose`
- [x] 3.6 Verificar desde clon limpio (`git clone` a carpeta temporal): `docker compose up` deja el login accesible; segundo arranque no sobrescribe `.env` ni `APP_KEY`
- [x] 3.7 Verificar que un job de prueba despachado se procesa en el worker (test manual documentado) y que HMR funciona
- [x] 3.8 Commit `build(docker): add one-command development environment`

## 4. Autenticación sin registro

- [x] 4.1 Desactivar `Features::registration()` en Fortify, eliminar rutas/páginas/enlaces de registro del frontend (login, welcome, layouts)
- [x] 4.2 Asegurar que todas las rutas de app están en el grupo `auth` y que existe una ruta de salud pública (`/up`)
- [x] 4.3 Implementar comando `deliberia:user:create` (interactivo con `password()` oculto + confirmación; opciones `--name`, `--email`, `--password-env`; validación email único y `Password::defaults()`)
- [x] 4.4 Eliminar la creación de usuarios con credenciales del `DatabaseSeeder`; factories con `example.com`
- [x] 4.5 Tests Pest: login válido, login inválido con mensaje genérico, rate limiting, logout, `GET/POST /register` → 404 sin crear usuario, login sin enlace de registro, ruta protegida redirige a login
- [x] 4.6 Tests Pest del comando: creación interactiva, por variable de entorno, email duplicado, contraseña débil
- [x] 4.7 Commit(s) `feat(auth): ...`

## 5. Herramientas de calidad

- [ ] 5.1 Instalar y configurar Pint (preset laravel) y añadir hook local de Pint en pre-commit (vía contenedor o binario de vendor)
- [ ] 5.2 Instalar Larastan nivel 8 sin baseline y corregir hallazgos del código del kit
- [ ] 5.3 Migrar/asegurar Pest 4; `phpunit.xml` apuntando a Postgres (`deliberia_test`) creado por el entorno Docker
- [ ] 5.4 Revisar ESLint, Prettier y `tsc --noEmit` del kit; scripts npm `lint`, `format:check`, `types`
- [ ] 5.5 Scripts composer `lint`, `analyse`, `test` como fuente única; `make lint` / `make test` los invocan
- [ ] 5.6 Documentar en ADR-0001 la convención de integraciones externas detrás de interfaces con fakes en tests
- [ ] 5.7 Commit `chore(quality): configure formatting, static analysis and tests`

## 6. CI en GitHub Actions

- [ ] 6.1 Crear `.github/workflows/ci.yml`: `on: push` + `pull_request` a main, `permissions: contents: read`, `concurrency` por rama
- [ ] 6.2 Job `secrets`: checkout `fetch-depth: 0` + gitleaks sobre historial completo (versión fijada)
- [ ] 6.3 Job `php-quality` (Pint `--test`, Larastan) y job `js-quality` (Prettier check, ESLint, tsc) con caché
- [ ] 6.4 Job `tests` con service containers postgres:17 y redis; Pest + `npm run build`
- [ ] 6.5 Validar localmente el workflow (p. ej. `act` si disponible o revisión con `actionlint`) y dejar comprobación real para tras el primer push
- [ ] 6.6 Commit `ci: add quality and secret scanning pipeline`

## 7. Documentación, ADRs y archivos open source

- [ ] 7.1 `docs/adr/0000-template.md`, `0001-stack.md` (Laravel 13, Inertia React, Postgres, Redis, Docker propio, versión PHP, calidad, integraciones con fakes; alternativas) y `0002-secrets-and-workflow.md` (capas de protección, noreply, GitHub Flow, Conventional Commits, fase→change→epic)
- [ ] 7.2 `docs/WORKFLOW.md`: jerarquía fase → change OpenSpec → issue epic → sub-issues → ramas → PR → archivado; sección "Retomar el trabajo"; sección "Cerrar la jornada"; checklist de auditoría previa a publicar; pasos manuales en GitHub (secret scanning, push protection, private vulnerability reporting, bloquear emails en push)
- [ ] 7.3 Añadir sección "Estado" a `ROADMAP.md` (tabla fase / estado / change OpenSpec / epic GitHub / inicio uso real) con Fase 0 "en curso" → `phase-0-foundations`, y enlace a `docs/WORKFLOW.md` en "Cómo usar este documento"
- [ ] 7.4 `README.md`: qué es Deliberia, requisitos (Docker), arranque en un comando, crear usuario, comandos `make`, alternativa sin make para Windows, instalar hooks, enlaces a ROADMAP/WORKFLOW/ADRs
- [ ] 7.5 `LICENSE` (MIT, titular confirmado por el usuario, sin correo), `SECURITY.md` (Private Vulnerability Reporting, sin correo), `CONTRIBUTING.md` breve
- [ ] 7.6 `.github/ISSUE_TEMPLATE/` (epic, feat, bug, chore, spike adaptadas de `.claude/skills/release-plan/issue-templates/`, `config.yml` con `blank_issues_enabled: false`), `.github/pull_request_template.md`, `.github/dependabot.yml` (composer, npm, github-actions, docker; semanal)
- [ ] 7.7 Rellenar `openspec/config.yaml` con contexto del proyecto (stack, convenciones, idioma, requisitos no negociables)
- [ ] 7.8 Commit `docs: add ADRs, workflow guide and open source files`

## 8. Auditoría previa a la publicación (BLOQUEANTE antes del primer push)

- [ ] 8.1 `gitleaks git` sobre el historial completo y `gitleaks dir` sobre el árbol de trabajo: cero hallazgos
- [ ] 8.2 Autores/committers de todo el historial: solo noreply de GitHub / co-autoría noreply
- [ ] 8.3 Búsqueda de correos en archivos versionados (`git grep -E '[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}'`): solo dominios reservados/noreply
- [ ] 8.4 Revisión manual de `git ls-files`: sin `.env`, dumps, claves, logs ni archivos locales de herramientas
- [ ] 8.5 Verificación final desde clon limpio: arranque en un comando, `make user`, login/logout, `make lint` y `make test` en verde
- [ ] 8.6 Entregar al usuario los pasos para crear el repo vacío en GitHub, `git remote add origin` + `git push -u origin main`, y activar secret scanning, push protection y private vulnerability reporting
- [ ] 8.7 Tras el push: confirmar CI verde en GitHub y actualizar la tabla de Estado (Fase 0 → "en uso real" con fecha)
