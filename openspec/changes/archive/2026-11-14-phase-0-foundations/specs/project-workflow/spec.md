## ADDED Requirements

### Requirement: Convención de ramas y commits
El proyecto SHALL documentar GitHub Flow (rama `main` estable, ramas cortas `<tipo>/<issue>-<slug>`, squash merge) y Conventional Commits 1.0.0, y un hook `commit-msg` SHALL rechazar mensajes que no cumplan el formato.

#### Scenario: Mensaje inválido
- **WHEN** se intenta commitear con el mensaje "cambios varios"
- **THEN** el hook rechaza el commit indicando el formato esperado

#### Scenario: Mensaje válido
- **WHEN** se commitea con "feat(auth): disable public registration"
- **THEN** el hook acepta el commit

### Requirement: Registro de decisiones de arquitectura
El repositorio SHALL contener `docs/adr/` con una plantilla y al menos los ADR del stack inicial y de la estrategia de secretos y flujo de trabajo.

#### Scenario: ADR del stack
- **WHEN** se consulta `docs/adr/`
- **THEN** existe un ADR numerado que documenta Laravel 13, Inertia + React, PostgreSQL, Redis y Docker con las alternativas consideradas

### Requirement: Estado de fases visible
`ROADMAP.md` SHALL incluir una sección de Estado que liste cada fase con su estado, el/los change(s) de OpenSpec asociados y el epic de GitHub cuando exista.

#### Scenario: Retomar el trabajo
- **WHEN** alguien abre `ROADMAP.md`
- **THEN** puede identificar la fase en curso y el change de OpenSpec activo sin consultar otra fuente

### Requirement: Guía para retomar el trabajo
`docs/WORKFLOW.md` SHALL describir el ciclo fase → change OpenSpec → issues → ramas → PR → archivado, y el procedimiento para continuar el trabajo tras una pausa.

#### Scenario: Inicio de jornada
- **WHEN** el desarrollador sigue la sección "Retomar el trabajo" de `docs/WORKFLOW.md`
- **THEN** llega a la siguiente tarea pendiente del `tasks.md` del change activo

### Requirement: Plantillas de GitHub estandarizadas
El repositorio SHALL incluir plantillas de issue (epic, feat, bug, chore, spike) y de pull request, alineadas con Conventional Commits.

#### Scenario: Nueva issue
- **WHEN** se crea una issue en GitHub
- **THEN** solo se ofrecen las plantillas definidas (issues en blanco deshabilitadas)

### Requirement: Archivos de proyecto open source
El repositorio SHALL incluir `LICENSE` (MIT), `README.md` con instrucciones de arranque, `CONTRIBUTING.md` y configuración de Dependabot.

#### Scenario: README de arranque
- **WHEN** una persona sigue el README desde un clon limpio
- **THEN** levanta el proyecto, crea su usuario e inicia sesión sin pasos no documentados
