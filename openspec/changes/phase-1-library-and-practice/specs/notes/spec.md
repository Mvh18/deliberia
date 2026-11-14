## ADDED Requirements

### Requirement: Notas sobre recursos y problemas
El sistema SHALL permitir crear, editar y borrar notas de texto asociadas a un recurso o a un problema propio, y mostrarlas en el detalle de ese elemento.

#### Scenario: Nota en un problema
- **WHEN** el usuario añade una nota a un problema
- **THEN** la nota aparece en el detalle del problema

#### Scenario: Nota ajena
- **WHEN** un usuario intenta ver o editar una nota de otro usuario, o crearla sobre un elemento ajeno
- **THEN** el sistema lo rechaza

### Requirement: Notas con marca de tiempo en videos
En recursos de tipo video, una nota SHALL poder llevar un segundo concreto (entero, no negativo). La página del video SHALL mostrar el reproductor embebido oficial de YouTube, y al pulsar una nota con marca de tiempo el reproductor SHALL saltar a ese segundo.

#### Scenario: Saltar al segundo
- **WHEN** el usuario pulsa una nota con marca de tiempo 754
- **THEN** el reproductor embebido salta a 12:34 y reproduce desde ahí

#### Scenario: Capturar el segundo actual
- **WHEN** el usuario crea una nota mientras ve el video
- **THEN** puede usar el segundo actual del reproductor como marca de tiempo

#### Scenario: Marca de tiempo fuera de un video
- **WHEN** se envía una marca de tiempo en una nota de un artículo o de un problema
- **THEN** el sistema la rechaza
