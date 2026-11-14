## ADDED Requirements

### Requirement: Inicio de sesión
El sistema SHALL permitir iniciar sesión con email y contraseña a un usuario existente y SHALL rechazar credenciales inválidas sin revelar si el email existe.

#### Scenario: Credenciales válidas
- **WHEN** un usuario existente envía su email y contraseña correctos en `/login`
- **THEN** el sistema crea la sesión y redirige al panel autenticado

#### Scenario: Credenciales inválidas
- **WHEN** se envía un email inexistente o una contraseña incorrecta
- **THEN** el sistema muestra el mismo mensaje genérico de error y no crea sesión

#### Scenario: Limitación de intentos
- **WHEN** se superan los intentos de login permitidos por el limitador
- **THEN** el sistema rechaza nuevos intentos temporalmente

### Requirement: Cierre de sesión
El sistema SHALL permitir al usuario autenticado cerrar sesión, invalidando la sesión y regenerando el token CSRF.

#### Scenario: Logout
- **WHEN** un usuario autenticado ejecuta el cierre de sesión
- **THEN** la sesión se invalida y un acceso posterior a una ruta protegida redirige a `/login`

### Requirement: Registro deshabilitado
El sistema MUST NOT permitir crear cuentas desde la interfaz web ni desde ningún endpoint HTTP.

#### Scenario: Acceso a la ruta de registro
- **WHEN** se solicita `GET /register` o `POST /register`
- **THEN** el sistema responde 404 y no se crea ningún usuario

#### Scenario: Sin enlaces de registro
- **WHEN** se renderiza la página de login o de bienvenida
- **THEN** no aparece ningún enlace ni botón de registro

### Requirement: Creación de usuario por comando
El sistema SHALL proporcionar el comando `deliberia:user:create` para crear usuarios, validando email único y la política de contraseñas, sin aceptar la contraseña como argumento literal.

#### Scenario: Creación interactiva
- **WHEN** se ejecuta el comando y se introducen nombre, email válido y contraseña confirmada que cumple la política
- **THEN** se crea el usuario con la contraseña hasheada y el comando termina con código 0

#### Scenario: Email duplicado
- **WHEN** se ejecuta el comando con un email ya registrado
- **THEN** el comando termina con código distinto de 0 y no modifica el usuario existente

#### Scenario: Contraseña desde variable de entorno
- **WHEN** se ejecuta con `--name`, `--email` y `--password-env=VAR` y la variable `VAR` existe
- **THEN** se crea el usuario sin interacción usando el valor de la variable

#### Scenario: Contraseña débil
- **WHEN** la contraseña no cumple `Password::defaults()`
- **THEN** el comando muestra los errores y no crea el usuario

### Requirement: Rutas protegidas
Toda ruta de la aplicación, salvo las de autenticación y la de salud, SHALL requerir un usuario autenticado.

#### Scenario: Acceso anónimo
- **WHEN** un visitante no autenticado solicita una ruta protegida
- **THEN** el sistema redirige a `/login`
