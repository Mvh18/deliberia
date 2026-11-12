# ADR-0001: Stack inicial de Deliberia

- **Estado:** aceptado
- **Fecha:** 2026-11-11
- **Fase / change de OpenSpec:** phase-0-foundations

## Contexto

Deliberia es una herramienta personal (un solo usuario) para estudiar y practicar para
entrevistas técnicas. Las fases posteriores del [ROADMAP](../../ROADMAP.md) exigen:

- trabajo pesado en el cliente: API del reproductor de YouTube (F2), editor de código,
  Pyodide y SQL en el navegador con web workers (F4) y un lienzo de diagramas (F5);
- trabajos en segundo plano (metadatos de problemas en F1, IA fuera de la petición HTTP);
- consultas de grafos (prerrequisitos entre conceptos) y analítica (insights, F5);
- que cualquiera pueda levantar el proyecto con un solo comando.

El desarrollador conoce Laravel/PHP y las katas de code review de la Fase 4C son de Laravel.

## Decisión

| Capa | Elección |
|---|---|
| Backend | **Laravel 13** (PHP 8.4 en la imagen de desarrollo) |
| Autenticación | **Laravel Fortify**, con el registro deshabilitado y usuarios creados con `php artisan deliberia:user:create` |
| Frontend | **Inertia 3 + React 19 + TypeScript + Tailwind 4 + shadcn/ui** (starter kit oficial de Laravel 13) |
| Base de datos | **PostgreSQL 17** |
| Colas, caché y sesiones | **Redis 7** |
| Entorno de desarrollo | **Docker Compose propio** (`app`, `worker`, `vite`, `db`, `redis`) |
| Calidad PHP | Laravel Pint, **Larastan nivel 8** sin baseline, PHPUnit 12 |
| Calidad frontend | **Vite+** (`vp check`: formato y lint) y `tsc --noEmit` |
| CI | GitHub Actions |

### Convención: integraciones externas detrás de interfaces

Toda integración externa (YouTube, LLM, ejecutores de código, plataformas de problemas)
se define como una **interfaz** de la aplicación y se registra en el contenedor de
servicios. La implementación real vive aparte y los tests registran un **fake** que
implementa la misma interfaz. Ningún test llama a un servicio externo. Esto cumple el
requisito del ROADMAP de que las integraciones puedan sustituirse por fakes.

### Detalles del entorno de desarrollo

- `compose.yaml` publica todos los puertos solo en `127.0.0.1` y son configurables. Por
  defecto: 8100 (app), 5180 (Vite), 5440 (Postgres) y 6390 (Redis), elegidos para no
  chocar con instalaciones locales habituales.
- La app se sirve con `php artisan serve`: es un entorno de desarrollo, no de producción.
  La imagen de producción queda fuera del alcance de la Fase 0.
- La imagen incluye Node porque el plugin de Wayfinder ejecuta `php artisan` desde Vite.
- Los tests usan **PostgreSQL real** (`deliberia_test`, forzada en `phpunit.xml`), nunca
  SQLite en memoria, para no divergir en SQL específico y para que nunca toquen la base de
  datos de desarrollo.

## Alternativas consideradas

- **Livewire 4 + Flux:** más simple al principio, pero las fases 2, 4 y 5 necesitan islas de
  JavaScript con web workers, y React tiene mejor ecosistema para editores y lienzos.
- **Inertia + Vue:** equivalente, con un ecosistema algo menor para editores y lienzos.
- **Node/TypeScript (Next.js, NestJS) o Python/Django:** más piezas que integrar a mano
  (autenticación, colas, ORM) y se alejan de la experiencia del autor y de las katas de F4C.
- **MySQL/MariaDB:** menos herramientas para grafos y JSON. **SQLite:** limita colas y
  concurrencia.
- **Laravel Sail:** necesita `composer install` en el host antes del primer arranque, así
  que no cumple "un solo comando".
- **Pest 4, ESLint y Prettier:** se evaluaron en el diseño inicial y se descartaron para
  usar las herramientas que trae el starter kit (PHPUnit y Vite+), sin dependencias extra ni
  divergencia respecto al kit.

## Consecuencias

- Dos lenguajes (PHP y TypeScript) en el mismo repositorio.
- El starter kit nos fija las versiones de Inertia, React y Vite+; Dependabot propone las
  actualizaciones.
- `vp check` formatea solo el código: la documentación, `.claude/` y OpenSpec se excluyen en
  `vite.config.ts` para no reformatear documentos escritos a mano.
- Hace falta una imagen de producción cuando exista una fase de despliegue.
