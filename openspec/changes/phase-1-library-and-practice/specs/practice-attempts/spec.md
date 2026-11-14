## ADDED Requirements

### Requirement: Registrar intento
El sistema SHALL permitir registrar intentos sobre un problema propio con: resultado (`solved`, `partial` o `failed`), duración en minutos, autoevaluación de 1 a 4 (1 = otra vez, 2 = difícil, 3 = bien, 4 = fácil), si se usó pista, lenguaje y fecha del intento (por defecto, ahora).

#### Scenario: Intento válido
- **WHEN** el usuario registra un intento resuelto de 25 minutos, autoevaluación 3, sin pista, en Python
- **THEN** el intento queda guardado y asociado al problema

#### Scenario: Autoevaluación fuera de rango
- **WHEN** se envía una autoevaluación de 0 o 5
- **THEN** el formulario la rechaza

### Requirement: Diario de solución
Cada intento SHALL admitir un diario con: enfoque en una frase, complejidad temporal, complejidad espacial, patrón reconocido (un concepto de tipo `pattern` del usuario), errores cometidos y código opcional. El código SHALL guardarse como texto y mostrarse con resaltado, y SHALL NOT ejecutarse nunca.

#### Scenario: Diario completo
- **WHEN** el usuario guarda un intento con enfoque, `O(n)`/`O(1)`, patrón "Two pointers", errores y código
- **THEN** todos los campos se muestran en el detalle del intento

#### Scenario: Código malicioso
- **WHEN** el código del diario contiene `<script>` o código PHP
- **THEN** se muestra como texto escapado y no se ejecuta ni en el servidor ni en el navegador

### Requirement: Historial de intentos
El sistema SHALL mostrar en cada problema su historial de intentos, del más reciente al más antiguo, y SHALL permitir editar y borrar intentos propios.

#### Scenario: Historial ordenado
- **WHEN** un problema tiene tres intentos de fechas distintas
- **THEN** el historial los muestra del más reciente al más antiguo

#### Scenario: Intento ajeno
- **WHEN** un usuario intenta registrar un intento sobre un problema de otro usuario, o editar un intento ajeno
- **THEN** el sistema lo rechaza
