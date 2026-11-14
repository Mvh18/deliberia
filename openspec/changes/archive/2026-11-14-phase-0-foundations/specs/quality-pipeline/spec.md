## ADDED Requirements

### Requirement: Comandos de calidad unificados
El proyecto SHALL exponer scripts únicos para formato, análisis estático, tipos y tests (Composer y npm), reutilizados por el Makefile, los hooks y el CI.

#### Scenario: Ejecución local
- **WHEN** el desarrollador ejecuta `make lint` y `make test`
- **THEN** se ejecutan Pint (modo verificación), Larastan, `vp check`, `tsc` y los tests con los mismos parámetros que en CI

### Requirement: CI en cada push
Un workflow de GitHub Actions SHALL ejecutarse en cada push y en cada pull request hacia `main`, con permisos de solo lectura por defecto.

#### Scenario: Push a una rama
- **WHEN** se hace push a cualquier rama
- **THEN** se ejecutan los jobs de secretos, calidad PHP, calidad JS/TS y tests

### Requirement: El CI falla ante errores
El CI MUST fallar si hay errores de formato, de análisis estático, de tipos, tests rotos, build de frontend roto o secretos detectados.

#### Scenario: Error de formato
- **WHEN** un commit contiene código PHP sin formatear según Pint
- **THEN** el job de calidad PHP falla

#### Scenario: Error de análisis estático
- **WHEN** un commit introduce un error detectado por Larastan
- **THEN** el job de calidad PHP falla

#### Scenario: Test roto
- **WHEN** un test falla
- **THEN** el job de tests falla

### Requirement: Tests contra PostgreSQL real
Los tests SHALL ejecutarse contra PostgreSQL tanto en local como en CI.

#### Scenario: Conexión de tests
- **WHEN** se ejecuta la suite de tests
- **THEN** la conexión de base de datos utilizada es `pgsql`

### Requirement: Integraciones sustituibles
Las integraciones externas SHALL definirse detrás de interfaces registradas en el contenedor de servicios, de forma que los tests puedan sustituirlas por fakes.

#### Scenario: Convención documentada
- **WHEN** se consulta ADR-0001
- **THEN** describe la convención de interfaces + fakes para integraciones externas
