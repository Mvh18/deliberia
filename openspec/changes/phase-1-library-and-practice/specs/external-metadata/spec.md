## ADDED Requirements

### Requirement: Lista de dominios permitidos
El sistema SHALL validar contra una lista configurable de dominios permitidos toda URL que vaya a consultar (videos de YouTube y problemas de LeetCode). SHALL rechazar las URLs de dominios no soportados con un mensaje de validación claro y SHALL aceptar únicamente el esquema `https` (o `http`, normalizado a `https`).

#### Scenario: Dominio soportado
- **WHEN** se registra un video con `https://www.youtube.com/watch?v=dQw4w9WgXcQ` o `https://youtu.be/dQw4w9WgXcQ`
- **THEN** la URL pasa la validación

#### Scenario: Dominio no soportado
- **WHEN** se intenta registrar un problema con `https://evil.example.com/problems/two-sum/`
- **THEN** el formulario muestra un error que indica que el dominio no está soportado y no se crea ningún registro

#### Scenario: Engaño de subdominio
- **WHEN** se envía `https://youtube.com.evil.example.com/watch?v=x` o `https://leetcode.com@evil.example.com/problems/a/`
- **THEN** la URL se rechaza porque el host real no está en la lista

### Requirement: Metadatos en segundo plano
El sistema SHALL obtener los metadatos externos (de videos y de problemas) en un job de cola, nunca dentro de la petición HTTP que crea el registro. El registro SHALL crearse de inmediato con un estado de metadatos `pending` que pasa a `fetched` o `failed`.

#### Scenario: Creación no bloqueante
- **WHEN** el usuario registra un video o un problema
- **THEN** la respuesta HTTP devuelve el registro creado con metadatos `pending` sin haber llamado a ningún servicio externo, y se despacha un job de metadatos

#### Scenario: Fallo del proveedor
- **WHEN** el job de metadatos falla tras agotar sus reintentos
- **THEN** el registro conserva sus datos, pasa a metadatos `failed` y sus campos se pueden editar a mano

### Requirement: Metadatos de YouTube con respaldo
El sistema SHALL usar YouTube Data API v3 (oficial) para obtener título, duración y miniatura cuando haya una API key configurada. Sin key, SHALL usar el endpoint oEmbed oficial para obtener título y miniatura, y dejar la duración vacía para editarla a mano.

#### Scenario: Con API key
- **WHEN** `YOUTUBE_API_KEY` está configurada y se procesa un video
- **THEN** el recurso queda con título, duración en segundos y URL de miniatura, y metadatos `fetched`

#### Scenario: Sin API key
- **WHEN** `YOUTUBE_API_KEY` está vacía y se procesa un video
- **THEN** el recurso queda con título y miniatura de oEmbed, duración vacía y metadatos `fetched`

### Requirement: Metadatos de LeetCode opcionales
El sistema SHALL obtener título, dificultad y marca de pago de un problema de LeetCode mediante su endpoint GraphQL no oficial solo si `LEETCODE_METADATA_ENABLED` está activo. SHALL guardar únicamente esos metadatos mínimos y nunca enunciados, casos de prueba, editoriales ni soluciones.

#### Scenario: Integración desactivada
- **WHEN** `LEETCODE_METADATA_ENABLED` es falso y se registra un problema
- **THEN** no se hace ninguna llamada externa y el problema queda con el título derivado del slug, dificultad vacía y metadatos `failed` (editable)

#### Scenario: Solo metadatos mínimos
- **WHEN** el proveedor devuelve contenido adicional (por ejemplo el enunciado)
- **THEN** el sistema persiste solo título, dificultad y marca de pago

### Requirement: Integraciones sustituibles
Cada proveedor de metadatos SHALL definirse como una interfaz registrada en el contenedor de servicios, y los tests SHALL usar fakes que no hacen llamadas de red.

#### Scenario: Tests sin red
- **WHEN** se ejecuta la suite de tests
- **THEN** ningún test llama a YouTube ni a LeetCode, y los fakes simulan respuestas correctas y fallos
