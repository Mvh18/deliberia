## ADDED Requirements

### Requirement: Archivos sensibles ignorados
El repositorio SHALL ignorar archivos de entorno, claves, credenciales, dumps de base de datos, logs y configuración local de herramientas.

#### Scenario: Archivo .env creado
- **WHEN** existe `.env` en la raíz
- **THEN** `git check-ignore .env` lo reporta como ignorado y `.env.example` no está ignorado

#### Scenario: Dumps y claves
- **WHEN** se crean archivos `*.sql`, `*.dump`, `*.pem`, `*.key` o `auth.json`
- **THEN** git los ignora

### Requirement: Escaneo de secretos antes del commit
Un hook pre-commit SHALL ejecutar gitleaks y la detección de claves privadas sobre los cambios staged, bloqueando el commit si encuentra un secreto.

#### Scenario: Token en un commit
- **WHEN** se intenta commitear un archivo con un token con formato de credencial (por ejemplo, un token de GitHub)
- **THEN** el commit se bloquea indicando el hallazgo

### Requirement: Escaneo de secretos en CI
El CI SHALL escanear el historial completo con gitleaks y MUST fallar si encuentra secretos.

#### Scenario: Secreto en el historial
- **WHEN** cualquier commit del historial contiene un secreto detectable
- **THEN** el job `secrets` falla

### Requirement: Identidad Git sin correo personal
Los commits del repositorio SHALL usar el correo noreply de GitHub del autor, configurado a nivel local del repositorio antes del primer commit.

#### Scenario: Auditoría de autores
- **WHEN** se listan los correos de autor y committer de todo el historial
- **THEN** solo aparecen direcciones `@users.noreply.github.com`, `noreply@github.com` o `noreply@anthropic.com` (co-autoría)

### Requirement: Sin datos personales en el código
El código, configuración, seeders, tests y documentación MUST NOT contener correos reales, tokens ni credenciales; se usan dominios reservados (`example.com`).

#### Scenario: Revisión de archivos versionados
- **WHEN** se buscan direcciones de correo en los archivos versionados
- **THEN** todas pertenecen a dominios reservados (`example.com`, `example.org`), a `users.noreply.github.com` o son placeholders documentados

### Requirement: Auditoría previa a la publicación
Antes del primer push al remoto público SHALL ejecutarse una auditoría completa (gitleaks sobre todo el historial, autores de commits, búsqueda de correos y lista de archivos versionados) y su resultado MUST ser limpio.

#### Scenario: Primer push
- **WHEN** se va a ejecutar el primer `git push`
- **THEN** la checklist de auditoría de `docs/WORKFLOW.md` está completada sin hallazgos

### Requirement: Política de seguridad
El repositorio SHALL incluir `SECURITY.md` indicando cómo reportar vulnerabilidades mediante GitHub Private Vulnerability Reporting, sin publicar correos personales.

#### Scenario: Archivo de seguridad
- **WHEN** se abre `SECURITY.md`
- **THEN** describe el canal de reporte privado y no contiene direcciones de correo personales
