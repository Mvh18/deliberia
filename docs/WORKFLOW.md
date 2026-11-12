# Flujo de trabajo de Deliberia

Cómo se trabaja en este repositorio y cómo retomar el trabajo después de una pausa.
Las razones de estas decisiones están en [ADR-0002](adr/0002-secrets-and-workflow.md).

## Jerarquía: de la fase a la tarea

```
ROADMAP.md  ── fase ──────────────►  change de OpenSpec  ──►  issue epic (GitHub)
                                          │
                                          ├─ proposal.md   por qué
                                          ├─ design.md     cómo
                                          ├─ specs/        qué debe cumplirse
                                          └─ tasks.md      grupos de tareas  ──►  sub-issues ──► rama ──► PR
```

| Nivel | Dónde vive | Quién lo mantiene |
|---|---|---|
| Fase (qué y por qué) | `ROADMAP.md` | documento guía; solo cambia por decisión de producto |
| Estado de cada fase | tabla "Estado" de `ROADMAP.md` | se actualiza al empezar y al cerrar la fase |
| Epic (spec + diseño + tareas) | `openspec/changes/<change>/` | se crea con `/opsx:propose-deep` |
| Tarea pequeña | casillas de `tasks.md` y, cuando exista el remoto, sub-issues | se marcan al terminarlas |
| Decisión técnica | `docs/adr/` | un ADR por decisión relevante |

Reglas del ROADMAP que mandan: se trabaja **fase a fase**, se respeta el alcance de cada fase y
cada fase se usa **con contenido real durante dos semanas** antes de empezar la siguiente.

## Retomar el trabajo

1. Abre la tabla **Estado** de [ROADMAP.md](../ROADMAP.md): la fase "en curso" indica el change activo.
2. Comprueba el estado del change:
   ```bash
   openspec list
   openspec status --change <change>
   ```
3. Abre `openspec/changes/<change>/tasks.md` y busca la **primera casilla sin marcar** (`- [ ]`).
4. Lee el `design.md` y las specs relacionadas con esa tarea.
5. Levanta el entorno si no está en marcha: `make up`.
6. Ejecuta `/opsx:apply <change>` (o pídele a Claude que continúe) y trabaja hasta cerrar el grupo.

## Cerrar la jornada

1. Marca en `tasks.md` las casillas terminadas (`- [x]`). Un grupo sin terminar se deja tal cual.
2. Pasa `make test` (Pint, Larastan y tests) y `make lint` (también el frontend).
3. Haz commit con Conventional Commits (`feat(scope): ...`, `fix: ...`, `docs: ...`). Los hooks
   escanean secretos y validan el mensaje.
4. Si hubo una decisión relevante, añade un ADR en `docs/adr/`.
5. Si cambió el estado de una fase, actualiza la tabla de **Estado**.

## Empezar una fase nueva

1. Confirma que la anterior cumple sus criterios de aceptación y se usó dos semanas con datos reales.
2. Crea el change con `/opsx:propose-deep <fase>`: investiga, te entrevista y genera proposal,
   design, specs y tasks.
3. Añade el change a la tabla de **Estado** (estado "en diseño").
4. Cuando exista el repositorio remoto: crea la issue **epic** (plantilla `epic`) y una sub-issue por
   grupo de tareas. `/release-plan` puede ayudar a preparar issues, ramas y PR.
5. Al terminar: `/opsx:archive`, las specs pasan a `openspec/specs/` y se actualiza la tabla.

## Ramas, commits y PR

- **Ramas:** `<tipo>/<issue>-<slug>` desde `main`, por ejemplo `feat/12-resource-model`.
  Tipos: `feat`, `fix`, `docs`, `chore`, `refactor`, `test`, `ci`, `build`, `perf`.
- **Commits:** [Conventional Commits 1.0.0](https://www.conventionalcommits.org/). El hook
  `commit-msg` rechaza los mensajes que no lo cumplen.
- **PR:** contra `main`, con squash merge y la plantilla del repositorio. El CI debe estar en verde.
- No se hace `git push --force` sobre `main` ni se usa `--no-verify`.

## Comandos habituales

| Comando | Qué hace |
|---|---|
| `make up` | Levanta todo (equivale a `docker compose up -d --build --wait`) |
| `make down` | Detiene el entorno y conserva los datos |
| `make test` | Pint, Larastan y tests (igual que el CI) |
| `make lint` | Comprobaciones de PHP y de frontend |
| `make fix` | Corrige el formato automáticamente |
| `make user` | Crea un usuario de forma interactiva |
| `make hooks` | Instala los hooks de pre-commit |

Sin `make` (por ejemplo en Windows sin Git Bash), usa los comandos `docker compose ...` que
muestra el `Makefile`.

## Auditoría previa a publicar (primer push)

El repositorio es público: ejecuta esta checklist **antes del primer `git push`** y de cualquier
publicación grande. Debe quedar todo limpio.

- [ ] Identidad de los commits solo con correos noreply:
      `git log --format='%ae%n%ce' | sort -u`
- [ ] Gitleaks sobre todo el historial sin hallazgos:
      `docker run --rm -v "$PWD:/repo" ghcr.io/gitleaks/gitleaks:v8.30.1 git /repo --config /repo/.gitleaks.toml --no-banner --redact`
- [ ] Gitleaks sobre el árbol de trabajo sin hallazgos (mismo comando con `dir` en lugar de `git`)
- [ ] Búsqueda de correos en archivos versionados; solo deben aparecer dominios reservados
      (`example.com`, `example.org`) o noreply:
      `git grep -InE '[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}'`
- [ ] Revisión manual de `git ls-files`: sin `.env`, volcados, claves, logs ni archivos locales
- [ ] Sin rutas personales (`C:\Users\...`, nombres de usuario del sistema) en archivos versionados
- [ ] Prueba desde un clon limpio: `make up`, crear usuario, login y logout, `make test`

## Ajustes manuales en GitHub (una vez, tras crear el repositorio)

Todo es gratuito en repositorios públicos.

1. **Settings → Emails:** activa *Block command line pushes that expose my email*.
2. **Settings → Code security:** activa *Secret scanning* y *Push protection*.
3. Activa *Private vulnerability reporting* (lo usa [SECURITY.md](../SECURITY.md)).
4. Activa *Dependabot alerts* (las actualizaciones ya están configuradas en `.github/dependabot.yml`).
5. Crea el repositorio **vacío** (sin README ni licencia autogenerados) y enlázalo:
   ```bash
   git remote add origin git@github.com:<usuario>/deliberia.git   # omite este paso si `git remote -v` ya muestra origin
   git push -u origin main
   ```
6. Comprueba que el workflow **CI** pasa en verde y marca el criterio correspondiente en el change.
