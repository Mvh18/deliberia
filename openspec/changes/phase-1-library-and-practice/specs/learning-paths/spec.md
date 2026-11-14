## ADDED Requirements

### Requirement: Rutas mixtas y ordenadas
El sistema SHALL permitir crear rutas de aprendizaje con nombre y descripción que contengan recursos y problemas mezclados en un orden definido. Un mismo elemento SHALL aparecer como máximo una vez en una ruta.

#### Scenario: Ruta mixta
- **WHEN** el usuario crea "Algoritmos para entrevistas" y añade un video, un problema y un artículo
- **THEN** la ruta muestra los tres elementos en el orden en que se añadieron

#### Scenario: Elemento repetido
- **WHEN** el usuario añade a una ruta un problema que ya contiene
- **THEN** el sistema no lo duplica

### Requirement: Reordenar elementos
El sistema SHALL permitir cambiar el orden de los elementos de una ruta y quitar elementos sin borrar el recurso o problema subyacente.

#### Scenario: Reordenar
- **WHEN** el usuario mueve el tercer elemento a la primera posición
- **THEN** el nuevo orden se guarda y se mantiene al recargar

#### Scenario: Quitar elemento
- **WHEN** el usuario quita un problema de la ruta
- **THEN** el problema sigue existiendo en su biblioteca

### Requirement: Avance de la ruta
El sistema SHALL calcular el avance de una ruta como el porcentaje de elementos completados. Un recurso cuenta como completado si su estado es `completed`; un problema, si tiene al menos un intento con resultado `solved`.

#### Scenario: Porcentaje de avance
- **WHEN** una ruta tiene 4 elementos, un recurso completado y un problema con un intento resuelto
- **THEN** la ruta muestra un avance del 50 %

#### Scenario: Ruta vacía
- **WHEN** una ruta no tiene elementos
- **THEN** el avance es 0 % sin error

### Requirement: Importar ruta desde JSON
El sistema SHALL ofrecer un comando artisan que, para un usuario dado, cree una ruta a partir de un archivo JSON con grupos por patrón que contienen URLs de videos y de problemas. SHALL reutilizar recursos, problemas y conceptos existentes (sin duplicar), SHALL despachar los jobs de metadatos y SHALL informar de las URLs rechazadas sin abortar el resto.

#### Scenario: Importación idempotente
- **WHEN** se ejecuta dos veces el comando con el mismo archivo
- **THEN** no se duplican rutas, recursos, problemas ni conceptos

#### Scenario: URL inválida en el archivo
- **WHEN** el archivo contiene una URL de un dominio no soportado
- **THEN** el comando la reporta como rechazada y continúa con las demás

### Requirement: Autorización de rutas
El sistema SHALL impedir que un usuario vea o modifique rutas de otro, o que añada a sus rutas elementos ajenos.

#### Scenario: Elemento ajeno
- **WHEN** un usuario intenta añadir a su ruta un problema de otro usuario
- **THEN** el sistema lo rechaza
