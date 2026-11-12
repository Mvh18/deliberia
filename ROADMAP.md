# Deliberia — Roadmap del producto

> *Práctica deliberada para desarrolladores.*
>
> Sistema personal para gestionar cursos, recursos de aprendizaje y práctica para entrevistas técnicas (algoritmos, SQL, code review y system design).

---

## Cómo usar este documento (instrucciones para Claude Code)

Este documento describe **qué** se va a construir y **por qué**. No define **cómo**.

- **Diseño y arquitectura:** antes de implementar cada fase, usa la skill de diseño y arquitectura del proyecto para definir el modelo de datos, la estructura del código y las decisiones técnicas de esa fase. No tomes decisiones arquitectónicas a partir de este documento.
- **Trabaja fase por fase.** No empieces una fase hasta que la anterior cumpla todos sus criterios de aceptación.
- **Respeta el alcance.** Cada fase indica lo que queda fuera. Si una tarea parece requerir algo de una fase futura, señálalo en lugar de implementarlo.
- **Las restricciones de la sección "Requisitos no negociables" aplican a todas las fases.**
- Si una decisión no está cubierta aquí ni en la skill de diseño, pregunta antes de asumir.
- **Estado y continuidad:** el estado de cada fase está en la tabla de abajo. Para retomar el trabajo, ver [docs/WORKFLOW.md](docs/WORKFLOW.md). Cada fase se convierte en un change de OpenSpec (el epic) con su `tasks.md`.

---

## Estado

Fuente de verdad del avance. Actualizar al empezar y al cerrar cada fase.
Estados: `pendiente` → `en diseño` → `en curso` → `en uso real` (se usa con contenido real durante dos semanas) → `terminada`.

| Fase | Estado | Change de OpenSpec | Epic en GitHub | Inicio de uso real |
|---|---|---|---|---|
| 0 — Fundaciones | en curso | `phase-0-foundations` | — | — |
| 1 — MVP: biblioteca y práctica externa | pendiente | — | — | — |
| 2 — Seguimiento real del video | pendiente | — | — | — |
| 3 — Retención | pendiente | — | — | — |
| 4 — Práctica interactiva | pendiente | — | — | — |
| 5 — Avanzado | pendiente | — | — | — |

---

## Sobre el nombre

**Deliberia** viene de *práctica deliberada* (*deliberate practice*, Anders Ericsson): práctica enfocada en las propias debilidades, con feedback y medición. Es la filosofía del producto frente al consumo pasivo de contenido.

- Nombre del repositorio sugerido: `deliberia` (o `deliberia-app`).
- Usar "Deliberia" en la interfaz, documentación, ADRs y mensajes de commit.

---

## Visión

Ver videos tutoriales genera **ilusión de aprendizaje**: se siente avance porque se consume contenido, pero la retención es baja sin práctica activa.

Deliberia cierra ese ciclo. No es solo un lugar para guardar videos: es una herramienta que conecta lo que se estudia con lo que se practica y mide el **dominio real de conceptos**, que es lo que se evalúa en una entrevista técnica.

### El ciclo central

Toda funcionalidad debe encajar en al menos uno de estos pasos:

1. **Consumir** — videos, artículos y cursos organizados en rutas de aprendizaje.
2. **Procesar** — notas, resúmenes en palabras propias, diario de soluciones.
3. **Practicar** — problemas de algoritmos, SQL, code review y diseño de sistemas.
4. **Recordar** — repetición espaciada de conceptos y problemas.
5. **Medir** — dominio por concepto y detección de puntos débiles.

Si una funcionalidad propuesta no encaja en el ciclo, probablemente no pertenece al producto.

---

## Principios de producto

- **Conceptos por encima de contenido.** El progreso se mide por concepto o patrón dominado (por ejemplo "sliding window"), no por videos vistos.
- **Herramienta personal, no plataforma comunitaria.** Nada de rankings, foros, concursos públicos ni gamificación social.
- **Complementar, no replicar.** Las plataformas externas (LeetCode, HackerRank, NeetCode, YouTube) se enlazan y se les hace seguimiento; no se copian.
- **Uso real antes de avanzar.** Cada fase debe usarse con contenido real durante al menos dos semanas antes de empezar la siguiente.
- **Simplicidad primero.** Preferir la solución más simple que resuelva el problema actual. Nada de construir para requisitos hipotéticos.

---

## Requisitos no negociables

Aplican a todas las fases.

### Legales y de términos de servicio

- **YouTube:** solo reproductor embebido y API oficial. Nunca descargar ni almacenar videos.
- **LeetCode y otras plataformas:** guardar únicamente URL, identificador, título, dificultad y metadatos mínimos. **Nunca almacenar enunciados, casos de prueba, editoriales ni soluciones oficiales.**
- No depender de APIs no oficiales para funcionalidades centrales. Si se usan, deben ser opcionales y con alternativa manual.

### Seguridad

- El código escrito por el usuario **nunca** se ejecuta en el mismo proceso, servidor o contenedor que la aplicación.
- Toda URL externa que el sistema procese debe validarse contra una lista de dominios permitidos (prevención de SSRF).
- Autenticación obligatoria y autorización sobre cada recurso, aunque el sistema tenga un solo usuario.

### Calidad

- Toda funcionalidad nueva incluye tests automatizados.
- El pipeline de CI (formato, análisis estático y tests) debe pasar antes de integrar cambios.
- Las integraciones externas (YouTube, LLM, ejecutores de código) deben poder sustituirse por fakes en los tests.

### IA

- Las funcionalidades con IA son **opcionales**: el sistema debe funcionar completo sin ellas.
- Las llamadas a modelos de IA nunca se hacen dentro del ciclo de una petición HTTP.

---

## Fase 0 — Fundaciones

**Objetivo:** un proyecto vacío pero profesional, listo para construir encima.

### Alcance

- Repositorio con flujo de trabajo Git definido (ramas, convenciones de commits).
- Entorno de desarrollo reproducible con Docker.
- Autenticación de usuario (registro deshabilitado o restringido, ya que es de uso personal).
- Herramientas de calidad: formateo de código, análisis estático y framework de testing.
- Pipeline de CI que ejecute las herramientas de calidad en cada push.
- Carpeta de ADRs (Architecture Decision Records) para documentar decisiones.

### Criterios de aceptación

- [ ] Cualquier persona puede levantar el proyecto con un solo comando siguiendo el README.
- [ ] Se puede iniciar y cerrar sesión.
- [ ] El CI falla si hay errores de formato, de análisis estático o tests rotos.
- [ ] Existe al menos un ADR con las decisiones iniciales del stack.

---

## Fase 1 — MVP: biblioteca y práctica externa

**Objetivo:** organizar el aprendizaje y registrar la práctica en plataformas externas. Al terminar esta fase el sistema ya debe ser útil en el día a día.

### Funcionalidades

**Recursos de aprendizaje**
- Registrar un video de YouTube pegando su URL; el sistema obtiene título, duración y miniatura automáticamente.
- Registrar artículos, documentación o cursos por URL con datos introducidos manualmente.
- Marcar el estado de un recurso: pendiente, en progreso, completado.

**Conceptos y patrones**
- Crear conceptos (por ejemplo "índices compuestos") y patrones de algoritmos (por ejemplo "two pointers").
- Definir prerrequisitos entre conceptos.
- Asociar recursos y problemas a uno o varios conceptos.

**Rutas de aprendizaje**
- Crear rutas (por ejemplo "Algoritmos para entrevistas") que mezclen recursos y problemas en un orden definido.
- Reordenar los elementos de una ruta.
- Ver el avance de una ruta.

**Problemas externos**
- Registrar un problema pegando su URL (empezando por LeetCode).
- Detectar duplicados aunque la URL tenga variaciones (`/description/`, `/solutions/`, parámetros de consulta).
- Obtener título y dificultad automáticamente en segundo plano, con edición manual si falla.
- Marcar problemas que requieren suscripción de pago en la plataforma de origen.

**Intentos y diario de solución**
- Registrar cada intento de un problema: resultado, duración, autoevaluación (1–4), si se usó pista y lenguaje.
- Diario por intento: enfoque en una frase, complejidad temporal y espacial, patrón reconocido, errores cometidos y código opcional.
- Ver el historial de intentos de cada problema.

**Notas**
- Crear notas sobre recursos y problemas.
- En videos, las notas pueden ligarse a un segundo concreto; al pulsarlas, el video salta a ese momento.

**Panel inicial**
- Resumen simple: recursos en progreso, problemas resueltos por dificultad e intentos recientes.

### Criterios de aceptación

- [ ] Pegar una URL de YouTube crea el recurso con sus metadatos sin intervención manual.
- [ ] Registrar dos URLs distintas del mismo problema de LeetCode no crea un duplicado.
- [ ] Si la obtención de metadatos de un problema falla, el registro se crea igualmente y se puede editar.
- [ ] Una URL de un dominio no soportado se rechaza con un mensaje claro.
- [ ] Se puede crear una ruta con videos y problemas mezclados y ver su porcentaje de avance.
- [ ] Una nota con marca de tiempo lleva el reproductor al segundo correcto.
- [ ] Un usuario no puede ver ni modificar datos de otro usuario.

### Contenido de validación

Cargar como primera ruta real una lista curada y gratuita (por ejemplo NeetCode 150): los videos explicativos como recursos y los problemas de LeetCode como problemas externos, ligados al mismo patrón.

### Fuera de alcance

Seguimiento automático del video, repetición espaciada, editor de código, IA, estadísticas avanzadas, listas personalizadas y etiquetas libres.

---

## Fase 2 — Seguimiento real del video

**Objetivo:** medir cuánto de cada video se ha visto realmente, no solo si se marcó como completado.

### Funcionalidades

- El reproductor reporta periódicamente los segmentos vistos.
- El sistema combina segmentos solapados y calcula el porcentaje real visto.
- Retomar un video desde la última posición.
- Marcar como completado automáticamente al superar un umbral configurable (por ejemplo 90%).
- Importar una playlist completa de YouTube como recursos ligados a una ruta.
- Panel: tiempo de estudio por semana y por concepto.

### Criterios de aceptación

- [ ] Saltar partes del video no cuenta como tiempo visto.
- [ ] Ver dos veces el mismo segmento no duplica el porcentaje.
- [ ] Al volver a abrir un video, se reanuda donde se dejó.
- [ ] El envío de progreso no satura el servidor (frecuencia limitada).
- [ ] Importar una playlist de 50 videos no bloquea la interfaz.
- [ ] La lógica de combinación de segmentos tiene tests con casos límite (segmentos solapados, contiguos, contenidos y desordenados).

### Fuera de alcance

Análisis de comportamiento de visualización más allá del porcentaje y la posición.

---

## Fase 3 — Retención

**Objetivo:** que lo aprendido se recuerde a largo plazo.

### Funcionalidades

**Resúmenes en palabras propias (técnica Feynman)**
- Al completar un recurso, el sistema invita a escribir un resumen breve del concepto.
- Los resúmenes quedan asociados al recurso y a sus conceptos.

**Flashcards**
- Crear tarjetas manualmente, opcionalmente a partir de una nota.
- Asociar tarjetas a conceptos.

**Repetición espaciada**
- Un único motor de repaso aplicable a distintos tipos de elemento: flashcards y problemas.
- **Re-resolver problemas:** según la autoevaluación del último intento, el problema vuelve a programarse (antes si costó, más tarde si fue fluido).
- Cola diaria de repaso: "lo que toca hoy".
- Algoritmo de programación: empezar con uno simple (SM-2) con la posibilidad de migrar a FSRS.

**Problema diario personalizado**
- Sugerir un problema por día priorizando patrones débiles o problemas pendientes de repaso.

### Criterios de aceptación

- [ ] Un problema autoevaluado como difícil vuelve a la cola antes que uno autoevaluado como fácil.
- [ ] La cola diaria muestra flashcards y problemas en un mismo flujo.
- [ ] El cálculo de fechas de repaso tiene tests unitarios independientes de la base de datos.
- [ ] Añadir un nuevo tipo de elemento repasable no requiere modificar el algoritmo.

### Fuera de alcance

Generación automática de flashcards con IA (fase 5) y estadísticas de retención avanzadas.

---

## Fase 4 — Práctica interactiva

**Objetivo:** practicar dentro del propio sistema, especialmente lo que las plataformas externas no cubren bien.

### Etapa 4A — Ejecución en el navegador

- Editor de código integrado con resaltado de sintaxis.
- Problemas propios con plantilla inicial, casos de prueba y código envoltorio (*harness*) por lenguaje.
- Ejecución en el navegador para JavaScript y Python.
- Resultado por caso de prueba: entrada, salida esperada y salida obtenida.
- Cada envío queda registrado como un intento (reutilizando el diario de la fase 1).

### Etapa 4B — Práctica de SQL

- Problemas de SQL sobre esquemas de ejemplo, ejecutados en el navegador.
- Validación comparando el resultado de la consulta con el esperado.
- Problemas de optimización: analizar un plan de ejecución y proponer índices.

### Etapa 4C — Code review katas

- Fragmentos de código (inicialmente Laravel/PHP) con problemas introducidos a propósito: N+1, asignación masiva, autorización ausente, lógica mal ubicada, etc.
- El usuario identifica los problemas y luego compara con la lista de referencia.

### Etapa 4D — Práctica intercalada

- Modo que selecciona problemas de patrones ya estudiados **sin mostrar el patrón ni las etiquetas** hasta terminar.
- Al finalizar, el usuario indica qué patrón reconoció y el sistema registra si acertó.

### Etapa 4E (opcional) — Ejecución en servidor

- Solo si se necesita: más lenguajes (PHP, Java, C++), métricas fiables de tiempo y memoria, o tests realmente ocultos.
- Ejecutor aislado, sin red, con límites de CPU, memoria y tiempo, procesado de forma asíncrona.

### Criterios de aceptación

- [ ] Un bucle infinito en el código del usuario no bloquea la interfaz ni afecta al servidor.
- [ ] El mismo conjunto de casos de prueba sirve para todos los lenguajes de un problema.
- [ ] Los problemas propios no reproducen enunciados de plataformas externas.
- [ ] En el modo intercalado, el patrón no es visible en ningún punto antes de terminar.
- [ ] Añadir la etapa 4E no requiere cambiar la estructura de problemas ni de intentos.

### Fuera de alcance

Concursos, comparación con otros usuarios y soporte de más lenguajes sin necesidad real.

---

## Fase 5 — Avanzado

**Objetivo:** cubrir system design, simular entrevistas y obtener información accionable.

### Funcionalidades

**System design**
- Lienzo para diagramar arquitecturas.
- Plantilla estructurada al estilo de una entrevista: requisitos funcionales y no funcionales, estimaciones, API, modelo de datos, cuellos de botella y escalado.
- Calculadora de estimaciones rápidas (QPS, almacenamiento, ancho de banda).
- Rúbrica de autoevaluación.

**Entrevista simulada**
- Sesión cronometrada con uno o varios problemas de patrones débiles, sin pistas.
- Autoevaluación al final con rúbrica: comunicación, enfoque, correctitud y complejidad.

**Feedback con IA (opcional)**
- Evaluación de resúmenes Feynman: huecos o errores conceptuales.
- Revisión de soluciones de system design contra la rúbrica.
- Sugerencia de flashcards a partir de notas.

**Insights**
- Dominio por concepto y patrón, cruzando consumo, práctica y retención.
- Alerta de "aprendizaje ilusorio": mucho consumo de un concepto con bajo rendimiento en su práctica.
- Errores recurrentes detectados en los diarios de solución.
- Revisión semanal automática con recomendaciones de qué reforzar.

### Criterios de aceptación

- [ ] El sistema funciona completo con la IA desactivada.
- [ ] Un fallo del proveedor de IA no afecta al resto de funcionalidades.
- [ ] Los insights se basan en datos reales del usuario, no en valores estimados.
- [ ] El coste de IA es visible o está limitado por configuración.

---

## Fuera de alcance global

No se construirá en ninguna fase salvo decisión explícita:

- Rankings, concursos públicos, foros o funciones sociales.
- Monedas virtuales, tiendas o gamificación basada en competencia.
- Datos de "preguntas por empresa".
- Copia o scraping de contenido de plataformas externas.
- Aplicación móvil nativa.

---

## Backlog de ideas (sin priorizar)

- Listas personalizadas ("Repaso pre-entrevista", "Me costaron").
- Soporte para más plataformas externas (HackerRank, Codewars, NeetCode).
- Exportación de datos (notas, diarios, progreso).
- Problemas de depuración: código con un bug que hay que encontrar.
- Grabación de voz en entrevistas simuladas para practicar explicar en voz alta.
- Modo "explicar al patito": explicar una solución por escrito antes de verla funcionar.

---

## Glosario

| Término | Significado |
|---|---|
| **Concepto** | Unidad de conocimiento a dominar (por ejemplo "índices compuestos", "CQRS"). |
| **Patrón** | Tipo de concepto específico de algoritmos (por ejemplo "sliding window", "BFS"). |
| **Recurso** | Material de estudio que se consume: video, artículo, curso. |
| **Ruta de aprendizaje** | Secuencia ordenada de recursos y problemas con un objetivo. |
| **Problema externo** | Problema alojado en otra plataforma; el sistema solo guarda el enlace y el seguimiento. |
| **Problema propio** | Problema creado en el sistema y ejecutable en su editor. |
| **Intento** | Cada vez que se trabaja un problema, con su resultado y diario de solución. |
| **Diario de solución** | Registro del enfoque, complejidad, patrón y errores de un intento. |
| **Repaso** | Elemento programado por el motor de repetición espaciada. |
| **Harness** | Código oculto que envuelve la solución del usuario para ejecutar los casos de prueba. |

---

## Definición de "terminado" (aplica a toda tarea)

- [ ] Cumple los criterios de aceptación de su fase.
- [ ] Tiene tests automatizados y el CI pasa.
- [ ] Respeta los requisitos no negociables.
- [ ] Las decisiones relevantes están documentadas en un ADR.
- [ ] Se ha probado con datos reales, no solo con datos de ejemplo.
