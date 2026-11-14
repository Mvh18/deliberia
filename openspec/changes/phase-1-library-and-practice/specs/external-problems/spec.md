## ADDED Requirements

### Requirement: Registrar problema por URL
El sistema SHALL permitir registrar un problema de LeetCode pegando su URL, SHALL extraer la plataforma y el slug del problema, y SHALL crear el registro aunque la obtención de metadatos falle. El título inicial SHALL derivarse del slug (`two-sum` → "Two Sum").

#### Scenario: Alta con metadatos pendientes
- **WHEN** el usuario pega `https://leetcode.com/problems/two-sum/`
- **THEN** se crea el problema con slug `two-sum`, título provisional "Two Sum" y metadatos `pending`

#### Scenario: Metadatos fallidos
- **WHEN** el job de metadatos falla
- **THEN** el problema existe, queda en `failed` y el usuario puede editar título, dificultad y marca de pago

### Requirement: Detección de duplicados
El sistema SHALL normalizar la URL de un problema a (plataforma, slug) ignorando subrutas como `/description/`, `/solutions/` o `/editorial/`, parámetros de consulta, fragmentos, mayúsculas del host, `www.` y la barra final. SHALL NOT crear dos problemas con la misma plataforma y slug para el mismo usuario.

#### Scenario: Variantes del mismo problema
- **WHEN** el usuario registra `https://leetcode.com/problems/two-sum/description/` y después `https://www.leetcode.com/problems/two-sum/solutions/?envType=study-plan`
- **THEN** solo existe un problema y el segundo registro lleva al existente

#### Scenario: URL de LeetCode que no es un problema
- **WHEN** el usuario pega `https://leetcode.com/contest/`
- **THEN** el formulario muestra un error indicando que no es la URL de un problema

### Requirement: Datos mínimos y suscripción de pago
El sistema SHALL guardar de cada problema solo URL canónica, plataforma, slug, título, dificultad (`easy`, `medium`, `hard` o vacía) y si requiere suscripción de pago, además de sus asociaciones propias. SHALL permitir marcar o desmarcar a mano la suscripción de pago.

#### Scenario: Marcar de pago
- **WHEN** el usuario marca un problema como de pago
- **THEN** el problema se muestra con un indicador de suscripción requerida

### Requirement: Gestión y autorización de problemas
El sistema SHALL permitir listar (filtrando por dificultad y concepto), ver, editar y borrar los problemas propios, y SHALL impedir el acceso a problemas ajenos.

#### Scenario: Acceso ajeno
- **WHEN** un usuario solicita un problema de otro usuario
- **THEN** el sistema responde 403 o 404
