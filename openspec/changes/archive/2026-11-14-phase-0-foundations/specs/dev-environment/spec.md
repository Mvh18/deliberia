## ADDED Requirements

### Requirement: Arranque con un solo comando
El proyecto SHALL poder levantarse desde un clon limpio con un único comando documentado (`docker compose up` o `make up`), sin requerir PHP, Composer ni Node instalados en el host.

#### Scenario: Primer arranque desde clon limpio
- **WHEN** una persona con Docker clona el repo y ejecuta el comando documentado
- **THEN** se instalan dependencias, se crea `.env` desde `.env.example`, se genera `APP_KEY`, se aplican migraciones y la página de login responde en `http://localhost:<puerto>`

#### Scenario: Arranques posteriores idempotentes
- **WHEN** se ejecuta el comando de nuevo con el entorno ya inicializado
- **THEN** no se sobrescribe `.env` ni `APP_KEY` y no se pierden datos de la base de datos

### Requirement: Servicios del entorno
El entorno SHALL incluir aplicación web, worker de colas, PostgreSQL, Redis y servidor de Vite con recarga en caliente.

#### Scenario: Worker procesa trabajos
- **WHEN** se despacha un job a la cola por defecto
- **THEN** el servicio worker lo procesa sin intervención manual

#### Scenario: Recarga de frontend
- **WHEN** se modifica un componente React con el entorno levantado
- **THEN** el navegador refleja el cambio sin reiniciar contenedores

### Requirement: Servicios no expuestos a la red
Los puertos publicados por el entorno de desarrollo SHALL enlazarse solo a `127.0.0.1`.

#### Scenario: Puertos locales
- **WHEN** se inspecciona la configuración de puertos de `compose.yaml`
- **THEN** cada puerto publicado usa la forma `127.0.0.1:<host>:<container>`

### Requirement: Configuración por entorno
Toda configuración sensible o dependiente del entorno SHALL leerse de variables de entorno, y `.env.example` SHALL documentar cada variable sin contener secretos reales.

#### Scenario: Plantilla de entorno
- **WHEN** se revisa `.env.example`
- **THEN** contiene todas las variables necesarias con valores vacíos o valores locales de desarrollo inofensivos y comentados
