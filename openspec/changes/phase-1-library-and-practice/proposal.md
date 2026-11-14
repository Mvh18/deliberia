## Why

La Fase 0 dejó un proyecto vacío: solo hay login y un panel sin contenido. La **Fase 1 del ROADMAP ("MVP: biblioteca y práctica externa")** es la que hace útil a Deliberia en el día a día. Permite organizar lo que se estudia (videos, artículos y cursos agrupados por concepto y en rutas) y registrar lo que se practica en plataformas externas (problemas de LeetCode con intentos y diario de solución). Así se cierran los pasos *Consumir*, *Procesar* y *Practicar* del ciclo central.

## What Changes

- **Recursos de aprendizaje:**
  - Registrar videos de YouTube pegando la URL. Título, duración y miniatura se obtienen en segundo plano.
  - Registrar artículos y cursos por URL (cualquier `https`, nunca se descarga) con datos manuales.
  - Estado de cada recurso: pendiente, en progreso o completado.
- **Conceptos y patrones** en un único modelo, con tipo, prerrequisitos sin ciclos y asociación a recursos y problemas.
- **Rutas de aprendizaje:**
  - Mezclan recursos y problemas en orden y se pueden reordenar.
  - Muestran el porcentaje de avance. Un recurso cuenta con estado "completado"; un problema, con al menos un intento resuelto.
- **Problemas externos (LeetCode):**
  - Se registran por URL y se normaliza la URL para detectar duplicados (`/description/`, `/solutions/`, parámetros de consulta).
  - Título, dificultad y si es de pago se obtienen en segundo plano con el endpoint GraphQL no oficial. Es desactivable por configuración y, si falla, el problema se crea igualmente y se edita a mano.
- **Intentos y diario de solución:**
  - Cada intento guarda resultado, duración, autoevaluación 1–4, uso de pista y lenguaje.
  - El diario guarda enfoque, complejidad temporal y espacial, patrón reconocido, errores y código opcional (solo se guarda y se muestra; nunca se ejecuta).
  - Cada problema tiene su historial de intentos.
- **Notas** sobre recursos y problemas. En videos pueden llevar un segundo concreto, y al pulsarlas el reproductor embebido salta a ese momento.
- **Panel inicial:** recursos en progreso, problemas resueltos por dificultad e intentos recientes.
- **Validación de URLs externas:** lista de dominios permitidos para lo que el sistema consulta (YouTube y LeetCode), con rechazo claro de los dominios no soportados.
- **Importación de rutas:** comando artisan que crea una ruta a partir de un JSON. Sirve para cargar NeetCode 150 como contenido de validación, y el repo incluye un ejemplo pequeño del formato.
- **Autorización por usuario** en todos los modelos nuevos, mediante policies.
- **Mantenimiento:** `openspec/config.yaml` pasa a PHP 8.5 y PHPUnit 13, y se añade el ADR-0003 con las decisiones de la fase.

### Fuera de alcance

Tomado del ROADMAP: seguimiento automático del video (Fase 2), repetición espaciada (Fase 3), editor y ejecución de código (Fase 4), IA (Fase 5), estadísticas avanzadas, listas personalizadas y etiquetas libres. Tampoco entran otras plataformas aparte de LeetCode (backlog), la importación de playlists de YouTube (Fase 2) ni el almacenamiento de enunciados, casos de prueba o soluciones de plataformas externas, que está prohibido por los requisitos no negociables.

## Capabilities

### New Capabilities

- `learning-resources`: registro de videos de YouTube, artículos y cursos, con estados y deduplicación de videos por ID.
- `concepts`: conceptos y patrones (un modelo con tipo), prerrequisitos sin ciclos y asociación a recursos y problemas.
- `learning-paths`: rutas ordenadas de recursos y problemas, reordenación, cálculo de avance e importación desde JSON.
- `external-problems`: problemas de LeetCode por URL, normalización y deduplicación, marca de suscripción de pago y edición manual.
- `external-metadata`: lista de dominios permitidos, obtención de metadatos en segundo plano (YouTube Data API con respaldo oEmbed y LeetCode GraphQL opcional) detrás de interfaces con fakes.
- `practice-attempts`: intentos con autoevaluación y diario de solución, e historial por problema.
- `notes`: notas sobre recursos y problemas, con marcas de tiempo en videos ligadas al reproductor embebido.
- `dashboard`: panel inicial con el resumen de recursos, problemas e intentos.

### Modified Capabilities

Ninguna. La autorización por recurso se especifica en cada capability nueva; `user-auth` no cambia.

## Impact

- **Backend:**
  - Nuevos modelos, migraciones, policies, form requests, controladores y rutas en `routes/web.php`.
  - Dos jobs de metadatos en la cola Redis.
  - Interfaces de integración (`VideoMetadataProvider` y `ProblemMetadataProvider`) con implementaciones reales y fakes.
  - Comando `deliberia:path:import`.
- **Frontend:** nuevas páginas Inertia y React para recursos, conceptos, rutas, problemas, intentos y notas, más el panel. Se añade la IFrame Player API de YouTube (script oficial, sin dependencias npm de terceros) para las notas con marca de tiempo.
- **Configuración:**
  - Nuevas variables en `.env.example`: `YOUTUBE_API_KEY` (vacía y opcional) y `LEETCODE_METADATA_ENABLED`.
  - Lista de dominios permitidos en `config/`.
- **Datos:** todas las tablas nuevas llevan `user_id`. No hay migraciones de datos existentes, porque no hay contenido previo.
- **Documentación:** ADR-0003, actualización de `openspec/config.yaml` y de la tabla de Estado del ROADMAP.
