# ADR-0003: Dominio de la Fase 1 e integraciones externas

- **Estado:** aceptado
- **Fecha:** 2026-11-15
- **Fase / change de OpenSpec:** phase-1-library-and-practice

## Contexto

La Fase 1 añade el primer modelo de dominio (recursos, conceptos, problemas, intentos, rutas y
notas) y dos integraciones externas (YouTube y LeetCode). ADR-0001 ya fija que toda integración
va detrás de una interfaz con fake en tests. Faltaba decidir cómo se modelan las relaciones
polimórficas, qué URLs se validan contra una lista de dominios, cómo se obtienen los metadatos
y cómo se refresca la interfaz.

## Decisión

1. **Polimorfismo con morph map.** Las relaciones polimórficas (`conceptables`,
   `learning_path_items`, `notes`) usan `Relation::enforceMorphMap` con los alias `resource` y
   `problem`, así que la base de datos no guarda nombres de clase. Como no tienen clave
   foránea, borrar un recurso o problema elimina en la misma transacción sus asociaciones,
   elementos de ruta y notas. El **diario de solución vive en la tabla `attempts`**: la relación
   es 1:1 y siempre se lee junto al intento.
2. **Lista de dominios solo para URLs que se consultan.** Los hosts permitidos (YouTube y
   LeetCode) se comparan de forma exacta mediante parsers puros, no con `str_contains` ni
   sufijos, y se rechazan userinfo y esquemas distintos de http/https. Los artículos y cursos
   solo exigen `https` y **nunca se descargan**, por lo que no hay superficie de SSRF.
3. **Proveedores de metadatos y jobs.** `VideoMetadataProvider` y `ProblemMetadataProvider`
   tienen implementaciones reales y fakes. YouTube usa la Data API si hay clave y oEmbed si no.
   LeetCode usa su endpoint GraphQL **no oficial**, desactivable por configuración; nunca es
   imprescindible y el registro del problema no depende de él. Los proveedores solo llaman a
   hosts fijos con el ID o slug ya validado y los DTO solo contienen campos permitidos (nunca
   enunciados). Se ejecutan en jobs de cola con 3 intentos y sin sobrescribir lo editado a mano.
4. **Refresco por polling de Inertia.** Mientras haya elementos con metadatos `pending`, las
   páginas recargan parcialmente con `usePoll` cada 3 s. Sin WebSockets.

## Alternativas consideradas

- **Tabla de diario aparte:** solo añade un join en una relación 1:1.
- **Guardar nombres de clase en las polimórficas:** acopla los datos al código y rompe al
  renombrar clases.
- **Aplicar la lista de dominios también a artículos:** no aporta seguridad porque nunca se
  les hacen peticiones, y limitaría el uso.
- **Reverb o WebSockets para los metadatos:** complejidad desproporcionada para un solo usuario.

## Consecuencias

- Las fases 2, 3 y 5 pueden extender el modelo (progreso de video, repetición sobre intentos,
  dominio por concepto) sin rehacerlo.
- El endpoint de LeetCode puede cambiar sin aviso; la degradación a edición manual lo cubre.
- Hay que mantener los tests de borrado en cascada, porque las polimórficas no tienen FK.
- Si el worker de colas no corre, los metadatos quedan en `pending`; los campos siguen siendo editables.
