## 1. Preparación y decisiones

- [ ] 1.1 Crear la issue epic de la Fase 1 con la plantilla `epic` y una sub-issue por grupo de este archivo; enlazar el epic en la tabla de Estado del ROADMAP y pasar la Fase 1 a `en curso`
- [ ] 1.2 Actualizar `openspec/config.yaml`: PHP 8.5, PHPUnit 13
- [ ] 1.3 Escribir `docs/adr/0003-phase-1-domain-and-integrations.md` (polimorfismo con morph map y diario en `attempts`, lista de dominios solo para URLs consultadas, proveedores + jobs con LeetCode no oficial opcional, polling de Inertia)
- [ ] 1.4 Crear `config/deliberia.php` (hosts permitidos, `youtube.api_key`, `leetcode.metadata_enabled`) y añadir `YOUTUBE_API_KEY=` (vacía) y `LEETCODE_METADATA_ENABLED=true` a `.env.example` con comentarios
- [ ] 1.5 Añadir `Http::preventStrayRequests()` al `TestCase` base
- [ ] 1.6 Commit `docs: add ADR-0003 and phase 1 configuration`

## 2. URLs externas e integraciones

- [ ] 2.1 `App\Support\ExternalUrls\YouTubeUrl::parse()` (watch, youtu.be, shorts, embed, m., con `t`/`list`; ID de 11 caracteres) y `LeetCodeUrl::parse()` (plataforma + slug; ignora subrutas, query, fragmento, `www.`, mayúsculas del host, barra final); rechazan userinfo, esquemas no http(s) y hosts que no coinciden exactamente
- [ ] 2.2 Reglas de validación `SupportedVideoUrl` y `SupportedProblemUrl` con mensajes claros (dominio no soportado / no es un video / no es un problema)
- [ ] 2.3 Tests unitarios de los parsers con variantes válidas, engaños de host (`youtube.com.evil.example.com`, `leetcode.com@evil.example.com`) y URLs que no son video/problema
- [ ] 2.4 Interfaces `VideoMetadataProvider` y `ProblemMetadataProvider`, DTOs de solo lectura `VideoMetadata` y `ProblemMetadata` (solo campos permitidos)
- [ ] 2.5 `YouTubeDataApiProvider` (videos.list, `snippet,contentDetails`, duración ISO 8601 → segundos), `YouTubeOEmbedProvider`, `LeetCodeGraphqlProvider` (title, difficulty, isPaidOnly), `DisabledProblemMetadataProvider`; timeout 5 s; binding según config en un service provider
- [ ] 2.6 Fakes de ambos proveedores (respuesta correcta y fallo) y tests de los proveedores reales con `Http::fake()` (incluido descartar el enunciado si llega)
- [ ] 2.7 Commit `feat(integrations): add URL parsing and metadata providers`

## 3. Recursos de aprendizaje

- [ ] 3.1 Enums `ResourceType`, `ResourceStatus`, `MetadataStatus`; migración `resources` con CHECKs y `UNIQUE(user_id, youtube_video_id)`; modelo, factory y relación `User::resources()`; morph map con `resource`
- [ ] 3.2 `ResourcePolicy`; acciones `RegisterVideo` (dedup por ID → devuelve existente; despacha job) y `RegisterArticle`; form requests
- [ ] 3.3 Job `FetchVideoMetadata` (unique, 3 intentos, backoff, `failed()` → `failed`, no sobrescribe campos editados)
- [ ] 3.4 `ResourceController` (index con filtro por tipo/estado, create, store, show, edit, update, destroy) y rutas; borrado transaccional de notas/items/asociaciones
- [ ] 3.5 Páginas React: listado, alta (pestañas video / artículo-curso), detalle (miniatura, estado, metadatos), edición; `usePoll` mientras haya metadatos `pending`
- [ ] 3.6 Tests de feature: alta de video con fake (metadatos completos sin intervención — criterio 1), duplicado por variantes de URL, sin API key (oEmbed, duración vacía), fallo → `failed` editable, dominio no soportado rechazado (criterio 4), artículo sin red, cambio de estado, acceso ajeno 403/404
- [ ] 3.7 Commit(s) `feat(resources): ...`

## 4. Conceptos y prerrequisitos

- [ ] 4.1 Enum `ConceptKind`; migraciones `concepts` (índice único `(user_id, lower(name))`), `concept_prerequisites` (CHECK sin autorreferencia) y `conceptables`; modelo, factory y relaciones `morphedByMany`/`morphToMany`
- [ ] 4.2 Acción `AddPrerequisite` con CTE recursiva y transacción; `ConceptPolicy`
- [ ] 4.3 `ConceptController` + `ConceptPrerequisiteController`; asociación de conceptos al crear/editar recursos (regla `exists` acotada por usuario)
- [ ] 4.4 Páginas: listado filtrable por tipo, alta/edición, detalle con prerrequisitos y recursos/problemas asociados; selector de conceptos reutilizable
- [ ] 4.5 Tests: nombre duplicado sin mayúsculas, prerrequisito válido, ciclo directo, ciclo indirecto, autorreferencia, asociar concepto ajeno rechazado, acceso ajeno
- [ ] 4.6 Commit(s) `feat(concepts): ...`

## 5. Problemas externos e intentos

- [ ] 5.1 Enums `Platform`, `Difficulty`, `AttemptResult`; migración `problems` con `UNIQUE(user_id, platform, slug)`; modelo, factory; morph map con `problem`
- [ ] 5.2 Acción `RegisterProblem` (normaliza, dedup → existente, título desde slug, despacha job); job `FetchProblemMetadata` (mismas reglas que el de video; solo título, dificultad y pago)
- [ ] 5.3 `ProblemController` (index con filtro por dificultad/concepto, alta, detalle, edición con marca de pago, borrado transaccional) y `ProblemPolicy`
- [ ] 5.4 Migración `attempts` (diario incluido, CHECK 1–4, `pattern_id` nullOnDelete, índice); modelo, factory, `AttemptPolicy`; `AttemptController` anidado shallow con validación del patrón (concepto `pattern` propio)
- [ ] 5.5 Páginas: listado de problemas, alta, detalle con historial de intentos (más reciente primero) y formulario de intento con diario; código en `<pre><code>` escapado
- [ ] 5.6 Tests: duplicados por variantes de URL (criterio 2), metadatos fallidos → registro creado y editable (criterio 3), integración desactivada sin red, URL no-problema, solo metadatos mínimos, intento válido, autoevaluación fuera de rango, código con `<script>` escapado, historial ordenado, patrón ajeno rechazado, accesos ajenos
- [ ] 5.7 Commit(s) `feat(problems): ...` y `feat(attempts): ...`

## 6. Rutas de aprendizaje

- [ ] 6.1 Migraciones `learning_paths` y `learning_path_items` (`UNIQUE(path, item_type, item_id)`); modelos, factories, `LearningPathPolicy`
- [ ] 6.2 Acciones `AddPathItem` (sin duplicar, elemento propio) y `ReorderPathItems` (lista completa, transacción); consulta de avance con `EXISTS`
- [ ] 6.3 `LearningPathController` + `LearningPathItemController` (añadir, quitar, reordenar)
- [ ] 6.4 Instalar `@dnd-kit/sortable`; páginas: listado con % de avance, detalle con elementos mixtos, arrastrar y soltar con botones subir/bajar accesibles, buscador para añadir recursos/problemas
- [ ] 6.5 Tests: ruta mixta con % correcto (criterio 5), ruta vacía 0 %, elemento repetido, reordenar persiste, quitar no borra el elemento, elemento ajeno rechazado, acceso ajeno
- [ ] 6.6 Commit(s) `feat(paths): ...`

## 7. Notas y reproductor

- [ ] 7.1 Migración `notes` (polimórfica, CHECK `timestamp_seconds >= 0`); modelo, factory, `NotePolicy`; `NoteController` (crear/editar/borrar sobre recurso o problema propio; rechaza marca de tiempo si no es video)
- [ ] 7.2 Componente `YouTubePlayer` (IFrame API oficial cargada una vez, `youtube-nocookie.com`, `seekTo`/`getCurrentTime`) en el detalle de videos
- [ ] 7.3 Panel de notas en detalle de recurso y problema; en videos, notas con marca `mm:ss` que al pulsarse llaman a `seekTo` + `playVideo`, y botón "usar segundo actual"
- [ ] 7.4 Tests: CRUD de notas, marca de tiempo rechazada fuera de video, negativa rechazada, nota ajena; test de componente o verificación manual documentada de que la nota salta al segundo correcto (criterio 6)
- [ ] 7.5 Commit(s) `feat(notes): ...`

## 8. Panel inicial y navegación

- [ ] 8.1 `DashboardController` con recursos en progreso, problemas resueltos por dificultad e intentos recientes (consultas acotadas al usuario, sin N+1)
- [ ] 8.2 Página del panel con estados vacíos y accesos rápidos; sidebar con Panel, Recursos, Conceptos, Problemas, Rutas
- [ ] 8.3 Tests: resumen con datos, panel vacío, aislamiento respecto a otro usuario
- [ ] 8.4 Commit `feat(dashboard): ...`

## 9. Importación, validación y cierre

- [ ] 9.1 Comando `deliberia:path:import {file} --user=` reutilizando `RegisterVideo`/`RegisterProblem`; idempotente; resumen de creados/reutilizados/rechazados
- [ ] 9.2 `docs/examples/path-import.example.json` con pocos elementos; documentar el formato y el comando en el README
- [ ] 9.3 Tests del comando: importación correcta, idempotencia, URL rechazada sin abortar
- [ ] 9.4 Test transversal de autorización: con dos usuarios, recorrer todas las rutas de la fase y verificar que ninguna expone ni modifica datos ajenos (criterio 7)
- [ ] 9.5 Validación con contenido real: importar NeetCode 150 y verificar los 7 criterios de aceptación a mano en la app
- [ ] 9.6 Marcar los criterios de aceptación de la Fase 1 en el ROADMAP, actualizar la tabla de Estado (progreso, tareas, criterios) y la sección "Siguiente paso"
- [ ] 9.7 Commit(s) `feat(paths): add JSON import command` y `docs(roadmap): ...`
