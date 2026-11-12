# Deliberia

> *Práctica deliberada para desarrolladores.*

Sistema personal para gestionar cursos, recursos de aprendizaje y práctica para entrevistas
técnicas (algoritmos, SQL, code review y system design). Conecta lo que se estudia con lo que
se practica y mide el **dominio real de conceptos**, no los videos vistos.

El producto, sus fases y sus criterios de aceptación están en [ROADMAP.md](ROADMAP.md), que es el
documento guía del proyecto. El estado actual de cada fase está en su sección **Estado**.

**Stack:** Laravel 13 · Inertia 3 + React 19 + TypeScript · Tailwind 4 + shadcn/ui · PostgreSQL 17 ·
Redis 7 · Docker Compose. Detalles y alternativas descartadas en [docs/adr/](docs/adr/).

## Requisitos

- [Docker](https://docs.docker.com/get-docker/) con Compose v2
- Git
- [pre-commit](https://pre-commit.com) (solo para contribuir; instala los hooks de seguridad)

No necesitas PHP, Composer ni Node en tu máquina: todo corre en contenedores.

## Puesta en marcha

```bash
git clone https://github.com/Mvh18/deliberia.git
cd deliberia
docker compose up -d --build --wait   # o, si tienes make: make up
```

El primer arranque tarda un par de minutos: instala dependencias, crea el `.env` desde
`.env.example`, genera la clave de la aplicación y aplica las migraciones. Los arranques
posteriores son rápidos y no tocan tus datos ni tu `.env`.

Cuando termina, abre <http://localhost:8100>.

### Crear tu usuario

El registro público está **deshabilitado**. Crea tu usuario desde la terminal:

```bash
docker compose exec app php artisan deliberia:user:create   # o: make user
```

Te pedirá nombre, correo y contraseña (oculta). Para scripts, usa
`--name`, `--email` y `--password-env=NOMBRE_DE_LA_VARIABLE`; la contraseña nunca se pasa
como argumento.

### Puertos

Todos se publican solo en `127.0.0.1` y se pueden cambiar en tu `.env`
(`APP_PORT`, `VITE_PORT`, `DB_FORWARD_PORT`, `REDIS_FORWARD_PORT`).

| Servicio | Puerto por defecto |
|---|---|
| Aplicación | 8100 |
| Vite (HMR) | 5180 |
| PostgreSQL | 5440 |
| Redis | 6390 |

## Comandos

| Comando | Qué hace |
|---|---|
| `make up` / `make down` | Levanta / detiene el entorno (los datos se conservan) |
| `make test` | Pint, Larastan y tests (lo mismo que el CI) |
| `make lint` | Además, `vp check` y `tsc` del frontend |
| `make fix` | Corrige el formato automáticamente |
| `make shell` | Abre una shell en el contenedor de la app |
| `make logs` | Sigue los logs de todos los servicios |
| `make fresh` | Borra los datos y vuelve a migrar |
| `make hooks` | Instala los hooks de pre-commit |

`make` es opcional (en Windows no viene instalado): cada comando es un envoltorio de
`docker compose ...`, y el [Makefile](Makefile) muestra el equivalente de cada uno.

## Contribuir y trabajar en el proyecto

Antes del primer commit instala los hooks (escanean secretos y validan los mensajes):

```bash
pre-commit install --hook-type pre-commit --hook-type commit-msg   # o: make hooks
```

- Flujo de trabajo, cómo retomar el trabajo y checklist de publicación: [docs/WORKFLOW.md](docs/WORKFLOW.md)
- Guía de contribución: [CONTRIBUTING.md](CONTRIBUTING.md)
- Política de seguridad: [SECURITY.md](SECURITY.md)
- Decisiones de arquitectura: [docs/adr/](docs/adr/)

## Licencia

[MIT](LICENSE)
