## ADDED Requirements

### Requirement: Registrar video de YouTube
El sistema SHALL permitir registrar un video de YouTube pegando su URL. SHALL extraer el ID del video de las variantes habituales (`watch?v=`, `youtu.be/`, `/shorts/`, `/embed/`, con o sin parámetros como `t` o `list`) y SHALL obtener sus metadatos en segundo plano. El sistema SHALL guardar solo el ID y metadatos, nunca el video.

#### Scenario: Alta sin intervención manual
- **WHEN** el usuario pega `https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=42` y guarda
- **THEN** se crea un recurso de tipo video con el ID `dQw4w9WgXcQ`, y tras procesarse el job tiene título, duración y miniatura sin que el usuario escriba nada más

#### Scenario: Video duplicado
- **WHEN** el usuario registra `https://youtu.be/dQw4w9WgXcQ` y ya tiene un recurso con ese ID de video
- **THEN** no se crea un duplicado y se le muestra el recurso existente

#### Scenario: URL de YouTube sin ID válido
- **WHEN** el usuario pega `https://www.youtube.com/feed/trending`
- **THEN** el formulario muestra un error indicando que no es la URL de un video

### Requirement: Registrar artículo o curso
El sistema SHALL permitir registrar artículos, documentación y cursos con una URL `https` de cualquier dominio, un título obligatorio y campos opcionales introducidos a mano. El sistema SHALL NOT descargar ni consultar esas URLs.

#### Scenario: Alta manual
- **WHEN** el usuario registra un artículo con URL `https://docs.example.org/indexes` y título "Índices compuestos"
- **THEN** se crea el recurso de tipo artículo sin ninguna petición de red

#### Scenario: Esquema no permitido
- **WHEN** la URL usa `javascript:` o `ftp://`
- **THEN** el formulario la rechaza

### Requirement: Estado del recurso
Cada recurso SHALL tener un estado `pending`, `in_progress` o `completed`, que el usuario puede cambiar en cualquier momento.

#### Scenario: Cambiar estado
- **WHEN** el usuario marca un recurso como completado
- **THEN** el recurso queda en `completed` y cuenta como completado en las rutas que lo contienen

### Requirement: Gestión y autorización de recursos
El sistema SHALL permitir listar, ver, editar y borrar los recursos propios, y SHALL impedir que un usuario vea o modifique recursos de otro.

#### Scenario: Acceso ajeno
- **WHEN** un usuario solicita ver, editar o borrar un recurso de otro usuario
- **THEN** el sistema responde 403 o 404 sin revelar el contenido

#### Scenario: Edición tras fallo de metadatos
- **WHEN** los metadatos de un video quedaron en `failed` o sin duración
- **THEN** el usuario puede editar título, duración y miniatura a mano
