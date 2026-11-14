## Context

Tras la Fase 0, Deliberia es un starter kit de Laravel 13 + Inertia 3 + React 19 con login (Fortify, sin registro), un panel vacío, PostgreSQL 17, Redis 7 como cola y un worker en Docker. Todo cuelga de `routes/web.php`, bajo el grupo `auth` + `verified`. ADR-0001 fija la convención de que **toda integración externa va detrás de una interfaz con fake en tests**.

La Fase 1 añade el primer modelo de dominio completo y dos integraciones externas (YouTube y LeetCode). Las decisiones de producto que siguen las tomó el usuario en la entrevista de diseño:

- un único change para toda la fase;
- YouTube Data API con respaldo oEmbed;
- LeetCode GraphQL opcional;
- artículos con cualquier URL `https`;
- una sola tabla de conceptos;
- metadatos siempre en segundo plano;
- un problema completado cuando tiene un intento resuelto;
- NeetCode 150 cargado por comando de importación.

## Goals / Non-Goals

**Goals:**
- Modelo de datos de la Fase 1 que las fases 2, 3 y 5 puedan extender sin rehacerlo: el progreso de video, la repetición espaciada sobre intentos y el dominio por concepto.
- Que ninguna petición HTTP dependa de un servicio externo.
- Aislamiento por usuario verificable en tests para cada recurso (criterio 7).
- Cumplir los 7 criterios de aceptación de la fase con tests automatizados.

**Non-Goals:**
- Seguimiento del video, playlists, repetición espaciada, editor y ejecución de código, IA y estadísticas avanzadas.
- Plataformas distintas de LeetCode. El diseño las admite, pero no se implementan.
- API pública o JSON aparte de Inertia.
- Búsqueda de texto completo. Basta un filtro simple por título.

## Decisions

### 1. Modelo de datos

Todas las tablas llevan `user_id` (FK a `users`, `cascadeOnDelete`). Así cada policy se resuelve sin joins y las consultas se acotan con `$user->resources()`. Los enums son **backed enums de PHP** sobre columnas `string` con `CHECK` en PostgreSQL.

| Tabla | Columnas clave | Restricciones |
|---|---|---|
| `resources` | `type` (video/article/course), `url`, `youtube_video_id?`, `title`, `author?`, `duration_seconds?`, `thumbnail_url?`, `status` (pending/in_progress/completed), `metadata_status` (pending/fetched/failed/not_applicable) | `UNIQUE(user_id, youtube_video_id)` |
| `concepts` | `kind` (concept/pattern), `name`, `description?` | índice único funcional `(user_id, lower(name))` |
| `concept_prerequisites` | `concept_id`, `prerequisite_id` | PK compuesta; `CHECK(concept_id <> prerequisite_id)` |
| `conceptables` | `concept_id`, `conceptable_type`, `conceptable_id` | PK compuesta (polimórfica: resource/problem) |
| `problems` | `platform` (leetcode), `slug`, `url` canónica, `title`, `difficulty?` (easy/medium/hard), `is_paid_only`, `metadata_status` | `UNIQUE(user_id, platform, slug)` |
| `attempts` | `problem_id`, `result` (solved/partial/failed), `duration_minutes`, `self_rating` 1–4, `used_hint`, `language`, `attempted_at`, diario: `approach?`, `time_complexity?`, `space_complexity?`, `pattern_id?` (FK concepts, `nullOnDelete`), `mistakes?`, `code?` | `CHECK(self_rating BETWEEN 1 AND 4)`; índice `(problem_id, attempted_at)` |
| `learning_paths` | `name`, `description?` | — |
| `learning_path_items` | `learning_path_id`, `item_type`, `item_id`, `position` | `UNIQUE(learning_path_id, item_type, item_id)` |
| `notes` | `notable_type`, `notable_id`, `body`, `timestamp_seconds?` | `CHECK(timestamp_seconds >= 0)` |

- **El diario va en la tabla `attempts`.** La relación es 1:1 y siempre se lee junto al intento; una tabla aparte solo añadiría un join.
- **Polimorfismo con `Relation::enforceMorphMap`** (`resource` y `problem`), para no guardar nombres de clase en la BD. Como las polimórficas no tienen FK, el borrado de un recurso o problema elimina, en la misma transacción, sus `conceptables`, `learning_path_items` y `notes`. Hay tests de ello.
- **Fase 3:** la repetición espaciada podrá colgar de `problems` y de las tarjetas futuras sin tocar `attempts`. `self_rating` 1–4 ya coincide con las cuatro respuestas de SM-2/FSRS.

### 2. Prerrequisitos sin ciclos

Antes de insertar "A requiere B" se comprueba con una **CTE recursiva** de PostgreSQL si A es alcanzable desde B siguiendo prerrequisitos. Si lo es, se rechaza. La comprobación y la inserción van en una transacción, y el `CHECK` de la tabla cubre el caso de un concepto consigo mismo. *Alternativa descartada:* hacerlo en PHP cargando el grafo, que es más código y empeora a medida que crece el grafo.

### 3. URLs: lista de dominios permitidos y normalización

- La configuración vive en `config/deliberia.php`. Los hosts permitidos se comparan de forma exacta: `youtube.com`, `www.youtube.com`, `m.youtube.com`, `youtu.be` y `leetcode.com`, con `www.` normalizado.
- Hay parsers puros en `App\Support\ExternalUrls\`: `YouTubeUrl::parse()` devuelve el ID de video y `LeetCodeUrl::parse()` devuelve plataforma y slug. Analizan con `parse_url`, rechazan userinfo (`user@host`) y esquemas distintos de http/https, y comparan el host completo, nunca un sufijo ni un `str_contains`.
- Los form requests usan reglas de validación (`SupportedVideoUrl` y `SupportedProblemUrl`) que delegan en esos parsers.
- Los artículos y cursos solo validan `url:https`. **El sistema nunca hace peticiones a ellos**, así que no hay superficie de SSRF y no aplican la lista.
- Los proveedores solo llaman a hosts fijos (`www.googleapis.com`, `www.youtube.com/oembed` y `leetcode.com/graphql`) con el ID o slug ya validado. Nunca reenvían la URL del usuario.

### 4. Integraciones y jobs de metadatos

```
VideoMetadataProvider (interface) ── fetch(videoId): VideoMetadata
  ├─ YouTubeDataApiProvider   (si YOUTUBE_API_KEY)
  └─ YouTubeOEmbedProvider    (sin key; duration = null)
ProblemMetadataProvider (interface) ── fetch(slug): ProblemMetadata
  ├─ LeetCodeGraphqlProvider  (si LEETCODE_METADATA_ENABLED)
  └─ DisabledProblemMetadataProvider (lanza "disabled" → failed, sin red)
```

- La elección de la implementación se hace en un service provider, según la configuración.
- Los DTO son de solo lectura y contienen solo los campos permitidos, de modo que nunca se persiste un enunciado.
- Las llamadas usan el cliente HTTP de Laravel con timeout de 5 s.
- La duración ISO 8601 (`PT1H2M3S`) se convierte a segundos con `CarbonInterval`.
- **Jobs:** `FetchVideoMetadata` y `FetchProblemMetadata` (`ShouldQueue`, `ShouldBeUnique` por ID del modelo, `tries=3`, `backoff=[10, 60]`). En `failed()` ponen `metadata_status=failed`. El job **no sobrescribe** campos que el usuario ya editó a mano: solo rellena los vacíos o los provisionales, como el título derivado del slug.
- **Tests:** los fakes se registran en el contenedor y `Http::preventStrayRequests()` va en el `TestCase` base, así que cualquier petición real hace fallar el test.

### 5. Refresco de metadatos en la interfaz

Las páginas de detalle y de listado usan `usePoll` de Inertia, una recarga parcial cada 3 s, mientras haya elementos con `metadata_status=pending`, y paran cuando no queda ninguno. Es la opción más simple, sin WebSockets ni Reverb, y basta para un solo usuario.

### 6. Rutas de aprendizaje: orden y avance

- `position` es un entero. Para reordenar, el cliente envía la lista ordenada completa de IDs de item y el servidor reescribe las posiciones en una transacción. Con tamaños de ruta de unos 300 elementos no compensa usar un índice fraccional.
- El avance se calcula en una consulta con dos `EXISTS`: si el recurso está `completed` y si el problema tiene un intento `solved`. No se guarda ningún contador desnormalizado que pueda quedar desfasado. Aparece en el listado de rutas y en el detalle.
- La reordenación usa arrastrar y soltar con `@dnd-kit/sortable` y tiene botones de subir y bajar como alternativa accesible. Es la única dependencia npm nueva.

### 7. Notas con marca de tiempo

- El componente `YouTubePlayer` carga el script oficial `https://www.youtube.com/iframe_api` una sola vez, con un hook y una promesa compartida, y usa el host `youtube-nocookie.com`.
- Expone `seekTo(seconds)` y `getCurrentTime()`. Al pulsar una nota se llama a `seekTo` y luego a `playVideo`. Al crear una nota, el botón "usar segundo actual" lee `getCurrentTime()`.
- Se respeta el requisito no negociable: solo reproductor embebido y API oficial.
- La validación en el servidor rechaza `timestamp_seconds` cuando el elemento notable no es un video.

### 8. Autorización

- Hay una policy por modelo (`Resource`, `Concept`, `Problem`, `Attempt`, `LearningPath`, `Note`) y todas comparan `user_id`.
- Los controladores usan `authorize` o `Gate` en cada acción.
- Las asociaciones (concepto↔recurso, item de ruta, patrón de un intento, nota sobre un elemento) validan además que el elemento destino pertenece al usuario, con reglas `exists` acotadas por `user_id`.
- Hay una batería de tests con dos usuarios que recorre cada ruta y verifica 403/404.

### 9. Estructura del código y frontend

- **Backend:** controladores *resource* por modelo (`ResourceController`, `ConceptController`, `ConceptPrerequisiteController`, `ProblemController`, `AttemptController` anidado y *shallow*, `LearningPathController`, `LearningPathItemController`, `NoteController` y `DashboardController`), form requests por acción y lógica no trivial en clases de acción en `app/Actions/`, siguiendo el patrón que ya usa el kit. Ejemplos de acciones: `RegisterVideo`, `RegisterProblem`, `AddPrerequisite`, `ReorderPathItems` e `ImportLearningPath`.
- **Frontend:** páginas en `resources/js/pages/{resources,concepts,problems,paths}/` con componentes shadcn/ui ya presentes y rutas tipadas con Wayfinder. El código del diario se muestra en `<pre><code>` escapado por React, nunca con `dangerouslySetInnerHTML`. El resaltado de sintaxis queda para la Fase 4.
- **Navegación:** el sidebar del kit pasa a tener Panel, Recursos, Conceptos, Problemas y Rutas.

### 10. Importación de rutas

```
php artisan deliberia:path:import <archivo.json> --user=<email>
```

```json
{ "name": "NeetCode 150", "description": "...",
  "groups": [ { "concept": "Two Pointers", "kind": "pattern",
                "items": [ { "url": "https://www.youtube.com/watch?v=..." },
                           { "url": "https://leetcode.com/problems/valid-palindrome/" } ] } ] }
```

- El comando reutiliza las mismas acciones `RegisterVideo` y `RegisterProblem`. Crea o reutiliza el concepto de cada grupo y le asocia los elementos.
- Añade los elementos a la ruta en el orden del archivo y es idempotente: identifica la ruta por nombre y usuario, y los elementos por su clave única.
- Al final muestra un resumen de creados, reutilizados y rechazados.
- El repo incluye `docs/examples/path-import.example.json` con unos pocos elementos. La lista completa la prepara el usuario y no se versiona.

### 11. Documentación

Las decisiones 1 (polimorfismo y diario en `attempts`), 3 (lista de dominios solo para lo que se consulta), 4 (proveedores, jobs y LeetCode no oficial opcional) y 5 (polling) se registran en **ADR-0003**. `openspec/config.yaml` pasa a PHP 8.5 y PHPUnit 13.

## Risks / Trade-offs

- **El endpoint GraphQL de LeetCode no es oficial** y puede cambiar o bloquear peticiones sin aviso. Mitigación: está desactivable, el registro nunca depende de él, el título sale del slug y hay edición manual. Nunca es una funcionalidad central.
- **Las polimórficas no tienen FK.** Mitigación: borrado transaccional en las acciones, `enforceMorphMap` y tests de borrado en cascada.
- **Cuota de YouTube:** 10.000 unidades al día frente a 1 unidad por video es irrelevante para un solo usuario. Si se agota, el job falla y el video queda editable a mano.
- **El polling cada 3 s** genera peticiones mientras haya elementos pendientes. Es aceptable con un solo usuario, y se corta sola en cuanto los jobs terminan.
- **Si el worker de colas no está en marcha**, los metadatos se quedan en `pending`. Mitigación: Docker Compose ya levanta `worker`, y la interfaz deja editar los campos aunque estén pendientes.
- **Una fase grande en un solo change** implica un PR por grupo de tareas, para que cada uno siga siendo revisable.
