## ADDED Requirements

### Requirement: Panel inicial
El panel SHALL mostrar, solo con datos del usuario autenticado: los recursos en progreso, el número de problemas resueltos (con al menos un intento `solved`) por dificultad, y los intentos más recientes con su problema y resultado.

#### Scenario: Resumen con datos
- **WHEN** el usuario tiene 2 recursos en progreso, 3 problemas fáciles y 1 medio resueltos, y 5 intentos
- **THEN** el panel muestra los 2 recursos, los contadores 3 / 1 / 0 por dificultad y los intentos recientes

#### Scenario: Panel vacío
- **WHEN** el usuario no tiene datos
- **THEN** el panel muestra estados vacíos con accesos para registrar el primer recurso o problema

#### Scenario: Aislamiento de datos
- **WHEN** otro usuario tiene recursos y problemas
- **THEN** no aparecen ni cuentan en el panel del usuario autenticado
