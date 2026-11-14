## ADDED Requirements

### Requirement: Conceptos y patrones
El sistema SHALL permitir crear, editar, listar y borrar conceptos, cada uno con nombre, descripción opcional y tipo `concept` o `pattern`. El nombre SHALL ser único por usuario sin distinguir mayúsculas.

#### Scenario: Crear patrón
- **WHEN** el usuario crea "Two pointers" con tipo `pattern`
- **THEN** el patrón aparece en la lista de conceptos filtrable por tipo

#### Scenario: Nombre repetido
- **WHEN** el usuario crea "two pointers" y ya existe "Two pointers"
- **THEN** el formulario muestra un error de nombre duplicado

### Requirement: Prerrequisitos sin ciclos
El sistema SHALL permitir definir prerrequisitos entre conceptos del mismo usuario y SHALL rechazar cualquier prerrequisito que cree un ciclo, incluido el de un concepto consigo mismo.

#### Scenario: Prerrequisito válido
- **WHEN** el usuario indica que "Sliding window" requiere "Two pointers"
- **THEN** la relación se guarda y se muestra en ambos conceptos

#### Scenario: Ciclo indirecto
- **WHEN** A requiere B, B requiere C, y el usuario intenta que C requiera A
- **THEN** el sistema rechaza la relación con un mensaje que indica el ciclo

### Requirement: Asociación a recursos y problemas
El sistema SHALL permitir asociar cada recurso y cada problema a cero o más conceptos, y ver desde un concepto sus recursos y problemas.

#### Scenario: Ver contenido de un concepto
- **WHEN** el usuario abre el patrón "Two pointers"
- **THEN** ve los recursos y problemas asociados a él

#### Scenario: Concepto ajeno
- **WHEN** un usuario intenta asociar su recurso a un concepto de otro usuario
- **THEN** el sistema rechaza la asociación
