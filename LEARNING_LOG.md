📘 LEARNING LOG - Proyecto 1: Gestión de Biblioteca
Fecha: 17 Enero 2026 Estado: Configuración de BD y Seeding completado.

1. Diseño de Base de Datos (Schema)
Aprendí que el diseño inicial es crítico. Un error aquí (como una mala relación) causa deuda técnica inmediata.

Relación Muchos a Muchos (N:M):

Caso: Libros <-> Autores.

Solución: Se requiere una Tabla Pivote.

Convención Laravel: Orden alfabético de los modelos en singular (author_book).

Migración: Usar foreignId()->constrained()->onDelete('cascade') para evitar registros huérfanos.

Integridad de Datos:

Usar unsignedInteger para stocks (no existen stocks negativos).

Usar timestamp nullable (returned_at) en lugar de un campo de estado string (status). Si es null, está prestado; si tiene fecha, se devolvió.

2. Eloquent ORM & Modelos
Naming Conventions:

Si la relación devuelve uno: singular (ej. book()).

Si la relación devuelve colección: plural (ej. books(), loans()).

Configuración de Relaciones:

belongsToMany: Usado en Book y Author (gracias a la tabla pivote).

hasMany / belongsTo: Usado para Préstamos.

3. Factories & Faker
Errores corregidos al generar datos falsos:

Magnitud: randomNumber(20) genera 20 dígitos. Para rangos (0-20) se usa numberBetween(0, 20).

Tipos de Datos: No mezclar objetos DateTime en campos definidos como integer (años). Usar $this->faker->year().

Nombres: Usar firstName() en lugar de name() para evitar prefijos como "Mr." o "Dr.".

4. Seeding Avanzado (Lógica de Negocio)
Aprendí a no depender siempre de la "magia" de los factories, sino a escribir lógica PHP en el DatabaseSeeder para casos complejos.

Seed de Relación N:M:

PHP
// Crear libros y adjuntar autores aleatorios al vuelo
$books = Book::factory(15)->create()->each(function ($book) use ($authors) {
    $book->authors()->attach($authors->random(rand(1, 3)));
});
Seed Condicional (Préstamos):

Iteramos sobre estudiantes creados.

Usamos rand() para decidir si crear préstamos o no.

Controlamos manualmente returned_at para simular libros pendientes vs. devueltos.

5. Herramientas
Git: La interfaz gráfica de VS Code muestra el Staging Area, no el historial. Para ver el historial real: git log --oneline o extensión "Git Graph".

Comando de Reinicio: php artisan migrate:fresh --seed (Borra todo, migra y siembra).

## 📅 [19-01-2026] - Finalización del CRUD de Libros y Testing Automatizado

### 1. 🛠️ Configuración y Corrección del Entorno de Testing
- **Instalación de Pest PHP:** Configuración inicial y resolución de conflictos de dependencias con PHPUnit y Collision en el `composer.json`.
- **Corrección de `Pest.php`:** Se habilitó la carga del entorno de Laravel (App) en los tests unitarios (`Unit`), ya que por defecto solo estaba habilitado para `Feature`. Esto solucionó el error `Call to member function connection() on null`.
- **Faker en Factories:** Se estandarizó el uso de `$this->faker->name()` para evitar errores de `InvalidArgumentException` por configuraciones de idioma (Locale) faltantes en el entorno de testing.

### 2. ✅ TDD: Tests Unitarios de Modelos
Se crearon pruebas para asegurar la integridad de la base de datos antes de construir la API:
- **`BookTest`:** Verificación de la relación "Muchos a Muchos" (N:M) con Autores usando `hasAttached`.
- **`StudentTest`:** Validación de la restricción `unique` en el email, asegurando que se lance una `QueryException` al intentar duplicados.
- **`LoanTest`:** Verificación del *Casting* de fechas (`loaned_at` como instancia de `Carbon`) y la relación `belongsTo` con estudiantes.

### 3. 🚀 Desarrollo API RESTful (Módulo Libros)
Implementación completa del controlador `BookController` con arquitectura profesional:

#### A. Creación (Store)
- **Validación (`StoreBookRequest`):** Reglas para ISBN único, año como entero de 4 dígitos y validación de array de autores existentes (`exists:authors,id`).
- **Transacciones:** Uso de `DB::transaction` para asegurar que el libro y sus relaciones se guarden atómicamente.
- **Relaciones:** Uso de `sync()` para vincular autores en la tabla pivote.

#### B. Lectura (Index & Show)
- **Optimización:** Solución del problema **N+1** usando *Eager Loading* (`with('authors')`).
- **Paginación:** Implementación de `paginate(10)` en lugar de `all()` para proteger la memoria del servidor.
- **Recursos (`BookResource`):** Transformación de datos y anidación de `AuthorResource` para respuestas JSON limpias.

#### C. Actualización (Update)
- **Validación Condicional (`UpdateBookRequest`):** Implementación de `Rule::unique(...)->ignore($this->book)` para permitir guardar el mismo ISBN si pertenece al libro que se está editando.

#### D. Eliminación (Destroy)
- **Limpieza:** Desvinculación previa de relaciones con `detach()` dentro de una transacción.
- **Estándar HTTP:** Retorno de código **204 No Content** al eliminar exitosamente.

### 4. 🐛 Debugging y Herramientas
- **Postman:** Solución de error `ECONNREFUSED` ajustando el puerto (8001 vs 80) y configuración del Header `Accept: application/json` para ver errores de validación en lugar de HTML.
- **DBeaver:** Corrección de la conexión a la base de datos correcta (`sisgesbiblioteca` en lugar de `postgres`) para visualizar las tablas migradas.

## 📅 [20-01-2026] - Feature Testing y CRUD de Estudiantes

### 1. Testing de API (Feature Tests)
Aprendí a probar endpoints HTTP completos en lugar de solo clases aisladas.
- **Simulación de Peticiones:** Usar `postJson`, `putJson`, `deleteJson` para asegurar que Laravel maneje las cabeceras `Accept: application/json` correctamente.
- **Asserts Clave:**
  - `assertCreated()` (201) para creaciones.
  - `assertNoContent()` (204) para eliminaciones.
  - `assertJsonCount(10, 'data')` para verificar que la paginación realmente corta los resultados.
- **RefreshDatabase:** Fundamental usar `uses(RefreshDatabase::class)` para limpiar la BD entre tests y evitar datos basura.

### 2. Errores Comunes y Soluciones
- **Validación en Controlador:** Diferencia crítica entre `$request->validate()` (ejecuta validación, retorna void/redirección) y `$request->validated()` (retorna el array de datos limpios).
- **Rutas de Update:** Siempre requieren el ID en la URL (`/api/students/{id}`).
- **Modelos en Tests:** Los modelos en memoria no se actualizan solos. Si cambio algo en la BD, debo usar `$student->refresh()` para ver los cambios en la variable PHP.

### 3. Estándares REST
- **Delete:** No se devuelve JSON de confirmación, se devuelve un status 204 (No Content).

## 📅 [20-01-2026] - Lógica de Negocio Avanzada y Servicios

### 1. 🏗️ Patrón de Servicios (Service Layer)
Aprendí a desacoplar la lógica de negocio de los Controladores.
- **Cuándo usarlos:** Cuando hay lógica compleja, validaciones de negocio múltiples o transacciones que tocan varias tablas.
- **Beneficio:** El Controlador solo "orquesta" (recibe petición -> llama servicio -> devuelve respuesta), manteniéndose limpio ("Skinny Controller").
- **Inyección:** Se inyectan en el constructor del controlador (`__construct(LoanService $service)`).

### 2. 📦 Optimización de API Resources
- **Solución N+1:** Evitar hacer consultas (`Book::find`) dentro de un `JsonResource`.
- **Eager Loading:** Cargar las relaciones previamente en el Servicio (`$loan->load('book')`) y acceder a ellas en el recurso (`$this->book->title`).

### 3. 🧪 Estrategias de Testing
- **Test After:** Escribir la lógica primero y los tests después para validar flujos críticos (como stock 0).
- **Factories Avanzados:** Uso de `configure()` y `afterCreating` para manejar relaciones complejas en factories.
- **Unit vs Feature:** Testear la clase Servicio aislada (Unit) para reglas de negocio y el Controlador (Feature) para códigos HTTP (409 vs 200).
- 
## 📅 [21-01-2026] - Debugging, Namespaces y Route Model Binding

### 1. 📂 Refactorización y Namespaces
Aprendí que mover archivos físicamente no basta. PHP requiere que el `namespace` dentro del archivo coincida con la estructura de carpetas.
- **Error:** `Class not found` al mover un Request.
- **Solución:**
  1. Actualizar `namespace App\Http\Requests\Book;` en el archivo.
  2. Actualizar el `use` en el Controlador.
  3. Ejecutar `sail composer dump-autoload` si persiste.

### 2. 🤖 Route Model Binding y Errores 404
- Descubrí que al inyectar el modelo en el método (`show(Book $book)`), Laravel busca el registro automáticamente **antes** de entrar al método.
- **No hace falta try-catch:** Si no existe, Laravel lanza `ModelNotFoundException` y devuelve 404 automáticamente.
- **Mantener controladores limpios:** Delegar el manejo de errores estándar al Framework.

### 3. 🌐 Headers HTTP
- **Accept: application/json**: Obligatorio en Postman/Clientes API.
  - Sin esto, Laravel cree que es un navegador y devuelve HTML (o redirecciona) cuando hay errores (404, 422).
  - Con esto, Laravel devuelve errores en formato JSON.
---
**PROYECTO 1 COMPLETADO: Sistema de Biblioteca**

**Proyecto 2 COMENZADO : E-COMMERCE**
[22-01-2026] - Inicio Proyecto 2: Mini E-commerce (Digital Products)
1. 🏗️ Diseño de Base de Datos y Tipos de Datos
Aprendí que las decisiones de tipos de datos afectan la lógica de negocio futura.

Precios: Abandoné float/decimal. Usamos unsignedInteger para guardar precios en centavos (evita errores de redondeo financiero).

Fechas: Cambié date por timestamp en bought_at. Si necesito calcular expiraciones en minutos (ej: links de descarga), date no sirve.

Soft Deletes: Implementado en Productos para mantener la integridad histórica de las compras de los usuarios, incluso si el producto se deja de vender.

2. 🔗 Relaciones Avanzadas y Datos en Pivote (CRÍTICO)
Este fue el concepto más importante de la fase de modelado.

El Problema: Si un producto cambia de precio, las órdenes viejas no pueden cambiar su valor.

La Solución: Guardar el price_at_purchase en la tabla intermedia (order_item).

Implementación:

Forzar nombre de tabla: belongsToMany(..., 'order_item') cuando no seguimos la convención alfabética (order_product).

Recuperar datos: Usar withPivot('price_at_purchase'). Sin esto, Eloquent descarta los datos de la tabla intermedia y solo devuelve los modelos relacionados.

3. 🧠 Lógica de Seeding (Desafío de Lógica)
Me enfrenté a problemas de lógica al intentar crear órdenes y calcular totales dentro de bucles.

Error Inicial: Intentar crear la orden dentro del bucle de productos o intentar leer el precio de la pivote ($order->pivot) inmediatamente después de guardarlo.

Aprendizaje:

Crear la instancia de la Orden antes del bucle.

Iterar para adjuntar productos (attach).

Sumar los precios usando las variables en memoria ($product->price), no consultando la BD repetidamente.

Hacer un update final al total de la orden.

Conclusión: A veces la solución "compleja" en mi cabeza se resuelve simplificando el flujo paso a paso.

4. 🧪 TDD con Archivos y Storage
Aprendí a probar subidas de archivos sin ensuciar el disco duro local.

Herramientas: Storage::fake('public') y UploadedFile::fake()->image(...).

Flujo: El test intercepta la llamada al disco y valida que el controlador intente guardar el archivo, sin necesidad de verificar su existencia física real.

5. 🛡️ Seguridad y UX (Middleware & Services)
Middleware Personalizado: Creé IsAdmin para proteger rutas críticas. Aprendí a registrar su alias en bootstrap/app.php (Laravel 11).

Refactorización de Servicio: Mejoré el SlugService. En lugar de lanzar una Excepción (Error 500) cuando un nombre está duplicado, implementé un while que agrega un contador incremental (slug-1, slug-2). Esto mejora la experiencia de usuario (UX) automáticamente.

Transacciones: Uso de DB::transaction al crear productos para asegurar que o se guarda todo (BD + Archivos) o no se guarda nada.

## 📅 [23-01-2026] - Archivos, Seguridad y Debugging

### 1. 📂 Subida y Descarga Segura de Archivos
- **Arquitectura de Controladores:** Aprendí a separar responsabilidades.
  - `ProductController` (API): Gestiona la lógica de negocio y genera permisos (JSON).
  - `SignedStorageController` (Web/Invokable): Se encarga exclusivamente de servir el archivo binario (`Storage::download`).
- **Signed URLs:** Implementé `URL::temporarySignedRoute`.
  - Permite crear enlaces con fecha de caducidad y firma criptográfica.
  - No requiere autenticación de usuario en la ruta final, ya que la seguridad va incrustada en la firma del link.
- **Rutas con Regex:** Aprendí a usar `->where('path', '.*')` en rutas web para permitir que los parámetros incluyan barras inclinadas (`/`) sin romper el ruteo de Laravel.

### 2. 🐛 Debugging y Herramientas (Postman & Tinker)
- **Error de Puertos:** Entendí la diferencia entre el puerto de la App (80/8000) y el de la BD (5432). Enviar peticiones HTTP al puerto de Postgres causa `socket hang up`.
- **Form-Data:** Para subir archivos en Postman, el Body debe ser `form-data`, las keys deben ser tipo `File` y **no** se deben usar comillas en los strings.
- **Tinker Trait:** Si `User::createToken` falla, es porque falta el trait `HasApiTokens` en el modelo. Tinker requiere reiniciarse (`exit`) para detectar cambios en el código.

### 3. 🧪 Testing de Integración
- **Simulación de Compra:** Para probar la descarga, el test debe crear primero una `Order` en base de datos.
- **Validación de JSON:** Usar `assertJsonStructure(['url'])` para verificar respuestas dinámicas sin conocer el valor exacto del hash.
[24-01-2026] - Cierre Proyecto 2: Eloquent Avanzado (Scopes & Attributes)
1. 🔍 Scopes (Ámbitos de Consulta)
Aprendí a limpiar mis controladores encapsulando lógica de SQL dentro del Modelo.

Concepto: En lugar de repetir where('status', 'published') en todos lados, creo un método scopePublished.

Uso: Product::published()->search('termino')->get(). Hace el código más legible y mantenible.

Scopes Dinámicos: Pueden recibir parámetros (como el término de búsqueda) para construir queries complejas al vuelo.

2. 🗑️ Soft Deletes (Papelera de Reciclaje)
Implementé un sistema de borrado seguro.

Mecánica: Al borrar, no se elimina la fila, solo se llena el campo deleted_at.

Recuperación: Aprendí que find() ignora los borrados por defecto. Para restaurar, debo usar withTrashed()->find($id)->restore().

3. 🧬 Accessors & Mutators (Casting Moderno)
La diferencia entre "Gafas de Realidad Aumentada" y "Cirugía Plástica".

Accessor (get): Transforma el dato al salir (Lectura).

Ejemplo: Convertir 6292 (int) a "$62.92 USD" (string) automáticamente.

Importante: Se debe agregar al JsonResource para que la API lo envíe.

Mutator (set): Transforma el dato antes de entrar (Escritura).

Ejemplo: Capitalizar nombres automáticamente al guardar. Garantiza integridad de datos.

[24-01-2026] - Cierre Proyecto 2: Eloquent Avanzado (Scopes & Attributes)
1. 🔍 Scopes (Ámbitos de Consulta)
Aprendí a limpiar mis controladores encapsulando lógica de SQL dentro del Modelo.

Concepto: En lugar de repetir where('status', 'published') en todos lados, creo un método scopePublished.

Uso: Product::published()->search('termino')->get(). Hace el código más legible y mantenible.

Scopes Dinámicos: Pueden recibir parámetros (como el término de búsqueda) para construir queries complejas al vuelo.

2. 🗑️ Soft Deletes (Papelera de Reciclaje)
Implementé un sistema de borrado seguro.

Mecánica: Al borrar, no se elimina la fila, solo se llena el campo deleted_at.

Recuperación: Aprendí que find() ignora los borrados por defecto. Para restaurar, debo usar withTrashed()->find($id)->restore().

3. 🧬 Accessors & Mutators (Casting Moderno)
La diferencia entre "Gafas de Realidad Aumentada" y "Cirugía Plástica".

Accessor (get): Transforma el dato al salir (Lectura).

Ejemplo: Convertir 6292 (int) a "$62.92 USD" (string) automáticamente.

Importante: Se debe agregar al JsonResource para que la API lo envíe.

Mutator (set): Transforma el dato antes de entrar (Escritura).

Ejemplo: Capitalizar nombres automáticamente al guardar. Garantiza integridad de datos.

[27-01-2026] - Inicio Proyecto 3: Helpdesk & Arquitectura Asíncrona
1. 🏗️ Diseño de Base de Datos y PostgreSQL
Aprendí que el diseño relacional estricto es vital antes de tirar código.

Relaciones Polimórficas: Implementación de una única tabla files para adjuntar archivos tanto a Tickets (evidencia inicial) como a Messages (respuestas), usando $table->morphs('fileable').

Indices y Rendimiento: En tablas de alto tráfico (Tickets), los campos de filtrado común (status, priority) DEBEN tener índices (->index()).

Redundancia: Aprendí que foreignId()->constrained() ya crea índices automáticamente en PostgreSQL; agregarlos manualmente es redundante.

Convenciones Postgres: Cuidado con los tipos de datos y mayúsculas. Postgres es más estricto que MySQL.

2. 🧪 Testing: De "Risky" a "Passing"
El error "No Assertions": Un test que corre código pero no verifica nada (expect, assertDatabaseHas) es un test "Risky" y no aporta valor.

Estructura AAA:

Arrange: Preparar datos (Factories).

Act: Ejecutar la acción (Crear ticket/Asignar agente).

Assert: Validar que la BD cambió (agent_id no es null) y que los objetos tienen los datos esperados.

3. 🏭 Factories Inteligentes y Enums (PHP 8.1)
Dejamos de usar "Magic Strings" ('open', 'urgent') esparcidos por el código.

Casting en Modelos: Usar protected $casts vinculando columnas a PHP Enums. Laravel hidrata automáticamente el string de la BD a una instancia del Enum.

Factory States: En lugar de pasar arrays manuales, creamos métodos fluidos en el Factory:

PHP

// Mucho más legible y mantenible
Ticket::factory()->urgent()->assignedTo($agent)->create();
Esto encapsula la lógica de "qué significa ser urgente" dentro del Factory, no en el Test.
27-01-2026] - Eventos, Colas y Arquitectura Asíncrona
1. 📡 Eventos y Listeners (Patrón Observador)
Aprendí a desacoplar la lógica principal (crear ticket) de las secundarias (enviar email).

Wiring Manual: Aunque Laravel tiene auto-discovery, en entornos de testing a veces falla. Aprendí a registrar explícitamente la relación en AppServiceProvider:

PHP

Event::listen(TicketCreated::class, SendTicketCreatedEmail::class);
Testing de Eventos: Usar Event::fake() para verificar que el evento se disparó sin ejecutar la lógica real. Event::assertDispatched.

2. ⚡ Colas (Queues) y Testing Asíncrono
El error del queue:work: Aprendí que al usar Queue::fake() en los tests, los jobs se interceptan en un array en memoria. NO es necesario correr sail artisan queue:work porque el job nunca llega a Redis.

Importación de Facades: Un error común es importar la interfaz (Contracts\Queue) en lugar de la Facade (Facades\Queue), lo que causa el error Call to undefined method fake().

3. 💣 La Trampa de la Transacción (Critical Knowledge)
Uno de los errores más complejos de depurar.

El Problema: Disparar un evento (Event::dispatch) DENTRO de una transacción de base de datos (DB::transaction).

La Consecuencia: En los tests (que usan RefreshDatabase), la transacción nunca hace "commit" real, por lo que el Job encolado espera datos que técnicamente "no existen" aún para el proceso de cola, o el Fake no lo detecta correctamente por el aislamiento.

La Solución: Siempre disparar los eventos DESPUÉS de que la transacción se haya confirmado (fuera del closure).

PHP

// Mal
DB::transaction(function() { ... Event::dispatch(); });

// Bien
DB::transaction(function() { ... });
Event::dispatch();
4. 📂 Storage Testing
Paths Reales: No concatenar objetos UploadedFile con strings. Usar el path (hash) que retorna el método $file->store().

Mocking: Siempre usar Storage::fake('public') para evitar llenar el disco duro real y poder usar assertExists.

## 📅 [27-01-2026] - Arquitectura de Eventos, Testing Avanzado y Relaciones N:M

### 1. 🧪 Testing de Arquitectura vs. Testing de Framework
Aprendí a no pelear contra `Queue::fake()` cuando algo no funciona como espero.
- **El Problema:** Intentar probar que Laravel encola un Job a veces falla por configuraciones de entorno o "wrappers" internos (`CallQueuedListener`).
- **La Solución Senior:** Probar la **Arquitectura** en lugar del mecanismo.
    - Usar `Event::assertListening(Evento::class, Listener::class)` para verificar la conexión.
    - Usar `ReflectionClass` para verificar que el listener implementa `ShouldQueue`.
    - Esto garantiza que el código funcionará sin depender de la simulación compleja del framework.

### 2. ⚡ Optimización de Eloquent (Relaciones N:M)
- **Attach Masivo:** Evitar bucles `foreach` al guardar relaciones.
    - *Mal:* `foreach ($ids as $id) { $model->attach($id); }` (N Queries).
    - *Bien:* `$model->attach($ids_array);` (1 Query).
- **Naming Conventions:** La tabla pivote debe seguir orden alfabético estricto de los modelos en singular.
    - `Label` + `Ticket` = `label_ticket`.
- **Testing de Pivotes:** Usar `$this->assertDatabaseHas('label_ticket', [...])` para asegurar que la relación se persistió físicamente.

### 3. 🐛 Debugging de Tests y Tipos de Datos
- **Error `Nested arrays`:** Las APIs REST y los métodos de validación (`exists`) esperan **IDs primitivos** (int/string), no Objetos/Modelos.
    - *Solución:* Usar `$collection->pluck('id')->toArray()` antes de enviar datos a `postJson`.
- **Sintaxis de Validación:**
    - `exists:table.column` ❌ (Laravel busca tabla `table` y esquema `column` o falla).
    - `exists:table,column` ✅ (Correcto).
- **Validación de Arrays:** Usar la notación de punto (`files.*`, `labels.*`) para validar cada item dentro de un array.

### 4. 🚀 Modernización de Eventos (Laravel 11/12)
- **Atributo `#[Listen]`:** En lugar de registrar eventos manualmente en el `EventServiceProvider`, usar el atributo PHP sobre el método `handle` del listener. Esto hace el código más limpio y facilita el auto-descubrimiento.

### 5. 🛡️ Seguridad en Controladores
- **Middleware:** Usar `auth:sanctum` para proteger endpoints.
- **User Injection:** Nunca confiar en el `user_id` que viene del request. Siempre inyectarlo desde el token autenticado: `$request->user()->id`.

[28-01-2026] - Seguridad, Automatización y Arquitectura Asíncrona (SLA)
1. 🛡️ Seguridad y Autorización (Policies)
Aprendí a blindar la aplicación usando Policies en lugar de llenar los controladores de if/else.

Concepto: Una Policy encapsula la lógica de autorización de un Modelo específico.

Implementación:

Uso de authorize('view', $ticket) en el controlador.

Lógica de Negocio: Un Agente puede ver tickets "Abiertos" aunque no sean suyos, pero un Cliente solo ve los propios.

Gotcha (Error Común): Comparación estricta de Enums.

Error: Comparar $ticket->status (Casteado a Enum Object) === 'open' (String).

Solución: Comparar Enum con Enum (Status::OPEN) o acceder al valor (->value).

Testing: Uso de actingAs($user) y assertForbidden() (403) para verificar brechas de seguridad.

2. 🤖 Comandos de Consola y Rendimiento
Creación del comando tickets:check-sla para detectar tickets urgentes olvidados.

Manejo de Memoria: Aprendí a usar ->cursor() en lugar de ->get().

get(): Carga 50,000 registros en RAM (riesgo de crash).

cursor(): Usa un generador de PHP para traerlos uno a uno (memoria plana).

Time Travel Testing:

En lugar de esperar 2 horas reales, usamos $this->travelTo(now()->subHours(3)) en los tests para simular el paso del tiempo instantáneamente.

3. 📡 Arquitectura Orientada a Eventos (Event-Driven)
Implementación del flujo completo de escalación de tickets. Entendí la responsabilidad única de cada pieza:

Command (Sensor): Detecta la condición (Query a BD) y dispara la alarma (Event::dispatch). NO envía correos.

Event (Mensajero): DTO tonto que solo transporta el objeto $ticket.

Listener (Obrero): Escucha el evento y ejecuta la tarea pesada (Enviar Email). Implementa ShouldQueue para no bloquear el sistema.

Mail (Formato): Define el contenido visual.

4. 🧪 Estrategias de Testing Avanzado
Aprendí a no mezclar niveles de testing.

Feature Test (Comando):

Probamos que el comando dispare el evento: Event::assertDispatched.

Usamos un Closure para asegurar que el evento lleva el Ticket ID correcto.

Unit Test (Listener):

Probamos el Listener de forma aislada sin disparar el evento globalmente.

Instanciamos manualmente: $listener->handle($event).

Mocking de Mail: Mail::assertSent verificando que el correo lleva el ticket adjunto.

5. 🐛 Debugging de Mailables
Error Crítico: Undefined property $ticket.

Causa: El constructor del Mailable estaba vacío. Aunque le pasábamos datos, no los guardaba.

Solución: Definir la propiedad como pública en el constructor (public Ticket $ticket). Esto permite que la vista (Blade) y los Tests accedan a los datos del ticket.
[28-01-2026] - Finalización de Colas y Reto de Arquitectura
1. ⚙️ El Worker (Obrero) de Laravel
Aprendí por las malas que un Job encolado (ShouldQueue) no se ejecuta solo.

En Local: Se requiere ejecutar sail artisan queue:work para procesar los jobs pendientes.

El Flujo: El código PHP termina rápido enviando el trabajo a la BD (tabla jobs), y el worker lo recoge en segundo plano.

2. 📧 Mailables y Datos Públicos
Los Mailables actúan como "sobres". Si el constructor no asigna los datos a propiedades public, la vista y los tests no pueden acceder a ellos.

3. 🛡️ Prevención de Solapamiento (Overlapping)
withoutOverlapping(): Vital para comandos programados (Cron). Crea un archivo "candado" (mutex) que impide que una segunda instancia del comando arranque si la primera no ha terminado (evita duplicidad de correos y colapso de RAM).
📅 [29-01-2026] - Módulo de Respuestas, Optimización y Debugging Avanzado
1. 🏗️ Implementación de Respuestas (Answers)
Implementé el flujo completo para que Agentes y Clientes puedan interactuar en un ticket.

Arquitectura: Controller → Request (Validación) → DTO (Transporte estricto) → Service (Lógica DB + Transacción) → Event → Listener/Mail.

Relación: Actualización automática de last_reply_at en el ticket padre al crear una respuesta.

2. 🐛 Debugging: Errores Críticos y Soluciones
Hoy me enfrenté a una serie de errores en cadena que reforzaron mi atención al detalle:

Error 404 (Routing): Mi test fallaba porque definí la ruta en singular (answer) pero el test llamaba al plural (answers).

Lección: Estandarizar rutas API siempre en plural.

Error 500 (Sintaxis PHP): Array callback must have exactly two elements.

Causa: Intenté acceder a un array validado usando paréntesis $data('key') como si fuera función.

Solución: Usar corchetes $data['key'].

TypeError (DTOs): Intenté pasar un objeto User completo a una propiedad del DTO definida como int.

Lección: Los DTOs obligan a ser estricto con los tipos de datos.

Policy Authorization (La "Trampa"):

Problema: $this->authorize('create', $ticket) invocaba a TicketPolicy, permitiendo acceso incorrecto.

Solución: Para verificar permisos de creación de un modelo hijo (Answer) basado en un padre (Ticket), debo pasar un array: $this->authorize('create', [Answer::class, $ticket]). Esto fuerza a Laravel a usar AnswerPolicy.

Tip: optimize:clear fue necesario para limpiar la caché de policies.

3. 🚀 Optimización de Rendimiento (Batch Processing)
Refactoricé la lógica de asignación de etiquetas (Labels) en CreateTicketService.

El Problema (N+1): Un bucle foreach que hacía un SELECT y un INSERT por cada etiqueta. (10 etiquetas = 20 queries).

La Solución Senior:

whereIn('name', $nombres)->pluck('id'): Una sola consulta para obtener todos los IDs.

$ticket->labels()->attach($ids): Una sola consulta para insertar todas las relaciones.

Resultado: Reducción drástica de queries a la base de datos (O(1) constante).

🗺️ HOJA DE RUTA: Finalización del Proyecto (Helpdesk)
📌 Módulo A: Ciclo de Vida y Visualización (PRIORIDAD ALTA)
Tarea A1: Hilo de Conversación Completo (Thread View)
Descripción: El endpoint GET /tickets/{id} debe devolver toda la historia.

Criterios de Aceptación (AC):

La respuesta JSON debe incluir una llave thread o answers.

Debe incluir al Usuario que respondió (nombre, rol) y los Archivos adjuntos de cada respuesta.

El orden debe ser cronológico (Lo más viejo arriba).

Uso estricto de Eager Loading (with()) para evitar consultas N+1.

Los created_at deben ser legibles (o timestamps estándar).

Tarea A2: Flujo de Estados (RPC Endpoints)
Descripción: Acciones explícitas para cambiar el estado del ticket.

Endpoints:

POST /tickets/{ticket}/resolve (Agentes).

POST /tickets/{ticket}/close (Dueño/Admin).

Criterios de Aceptación:

Validar con Policies que un Cliente no pueda resolver (solo cerrar).

Validar que no se pueda re-abrir un ticket cerrado (opcional, o definir regla).

Registrar la fecha de resolución (resolved_at).

📌 Módulo B: Buscador Avanzado (Scopes)
Descripción: Permitir filtrar la lista de tickets.

Criterios de Aceptación:

Implementar scopeStatus, scopePriority y scopeSearch en el Modelo.

El buscador debe ser insensible a mayúsculas (ILIKE en Postgres).

URL soportada: ?status=open&search=impresora.

📌 Módulo C: Métricas (Dashboard)
Descripción: Endpoint para ver la salud del sistema.

Criterios de Aceptación:

Uso de agregaciones SQL (count, group by). Prohibido procesar arrays en PHP.

JSON de respuesta: { total_open: X, by_priority: { high: Y, low: Z } }.

📌 Módulo D: Audit Logs (Plus Profesional)
Descripción: Historial de cambios invisible al usuario común pero visible al admin.

Criterios de Aceptación:

Tabla polimórfica o dedicada activities.

Registrar cambios de estado y prioridad automáticamente (Observers o Events).

👨‍💻 Siguiente Paso Inmediato:
Comenzar con Tarea A1: Hilo de Conversación.

Acción: Modificar TicketController@show y TicketResource.

Reto: Investigar Eager Loading anidado (answers.user).
## 📅 [30-01-2026] - Optimización, Scopes y Dashboard

### 1. 🚀 Rendimiento en API Resources (Fix N+1)
Aprendí a no desperdiciar la memoria cargada por Eager Loading.
- **Error:** Usar `User::find($id)` dentro de un Resource (`toArray`), lo que causaba consultas repetitivas a la BD aunque ya hubiera usado `with()` en el controlador.
- **Solución:** Acceder directamente a las relaciones cargadas (`$this->user->name`). Laravel "incrusta" los objetos, evitando viajes extra a la base de datos.

### 2. 🔍 Buscador y Filtros Avanzados (Local Scopes)
Implementé un sistema de filtrado limpio encapsulando la lógica SQL en el Modelo `Ticket`.
- **Scopes:** `scopeStatus`, `scopePriority` y `scopeSearch`.
- **Postgres Tip:** Aprendí a usar `ILIKE` (`$q->where('title', 'ilike', "%{$term}%")`) para hacer búsquedas insensibles a mayúsculas/minúsculas, mejorando la UX.
- **URL Parameters:** Manejo de espacios en la URL (Enums como `in progress` viajan como `in%20progress` o deben mapearse a snake_case).

### 3. 📊 Dashboard de Métricas
Creé un endpoint de estadísticas sin cargar modelos en memoria PHP.
- **Estrategia:** Delegar los cálculos a la base de datos.
- **Técnica:** Uso de `Ticket::count()` y `groupBy` con `selectRaw` para obtener la distribución de tickets por prioridad en una sola consulta eficiente.
- **Naming:** Corregí la semántica de `average_priority` a `tickets_by_priority` (o distribución), ya que es un conteo, no un promedio matemático.

---
[02-02-2026] - Refinamiento del CRUD, Traits y Restricciones de Tiempo
1. ⏳ Restricciones Temporales (Time-Based Logic)
Implementé reglas de negocio para limitar la edición y eliminación de contenido, asegurando la integridad histórica del chat.

Lógica: Los usuarios solo pueden editar o eliminar sus Tickets, Respuestas y Archivos dentro de un periodo de tiempo específico (ej. 10 minutos desde su creación).

Abstracción con Traits: Creé un Trait reutilizable (ej. HasTimeLimit o similar) y lo apliqué a los modelos Ticket, Answer y File.

Beneficio: Evito duplicar la lógica de created_at->diffInMinutes() > X en múltiples Policies o Controladores. Mantengo el código DRY (Don't Repeat Yourself).

2. 🔄 Reasignación de Agentes
Completé la funcionalidad para cambiar el agente responsable de un ticket.

Flujo: Implementación del endpoint PUT para actualizar el agent_id.

Validación: Aseguré que el nuevo usuario asignado tenga el rol de Agente antes de guardar los cambios.

3. 🧹 Limpieza del CRUD (Update & Delete)
Cerré los ciclos pendientes de gestión de contenido:

Tickets & Answers: Implementación completa de update (solo campos permitidos) y delete (Soft Deletes donde aplica), respetando las nuevas restricciones de tiempo.

Archivos: Capacidad de eliminar adjuntos específicos sin borrar todo el ticket, validando permisos de propiedad.

[03-02-2026] - Race Conditions y Bloqueo Pesimista

Bloqueo Pesimista (lockForUpdate): Aprendí a evitar que dos procesos modifiquen el mismo registro simultáneamente.

Importante: Siempre debe ir dentro de una transacción de BD (DB::transaction).

Tip Senior: Es vital recargar el modelo desde la BD al aplicar el lock para asegurar que tenemos los datos más recientes justo antes de validar.

Testing de Excepciones: No solo se testea el "camino feliz". Usar toThrow en Pest permite asegurar que nuestras reglas de negocio disparan los errores correctos ante datos inválidos.

Refactorización de Servicios: Separar la lógica de "Asignación" (cambiar de agente) de la de "Adición" (poner el primer agente) permite reglas de validación distintas y más claras.

# [03-02-2026] - Auditoría de Datos y JSON en PostgreSQL

## 📝 Aprendizajes del Día

**1. Patrón Observer**
* Aprendí a usar **Observers** para desacoplar la lógica de registro (logs) de la lógica de negocio.
* El Observer "espía" los eventos del modelo (`updated`) sin ensuciar el controlador.

**2. Manejo de JSON en Eloquent**
* **Problema:** PostgreSQL espera un string JSON, pero PHP envía un array.
* **Solución:** Usar el casting en el modelo. Esto automatiza la serialización (Array -> JSON) y deserialización (JSON -> Array).
    ```php
    protected $casts = [
        'campo' => 'array'
    ];
    ```

**3. Testing de JSON**
* Aprendí a validar valores específicos dentro de una columna JSON usando la sintaxis de array en **Pest**:
    ```php
    expect($audit->old_values['status'])->toBe(...);
    ```
* Esto evita "falsos positivos" donde el registro se crea pero guarda datos vacíos.


## 🗺️ Hoja de Ruta: Finalización del Proyecto (The Polish Phase)

Aquí tienes las tareas restantes para dejar el sistema listo para producción, clasificadas según si es aplicación de conocimientos previos o teoría nueva.

### 1. Completar el Ciclo de Auditoría
- [ ] **Descripción:** Tu Observer actual solo maneja `updated`. Si se crea un ticket o se elimina, no se registra nada.
- **Tarea:** Implementar los métodos `created` y `deleted` (o `restored` si usas SoftDeletes) en el `AuditObserver`.
- **Reto:** En `created`, `old_values` es *null*. En `deleted`, `new_values` es *null*.
- **Tipo:** 🔨 Aplicación (Lógica condicional básica).
- **Dificultad:** 🟢 Baja.

### 2. Sistema de Notificaciones (The Laravel Way)
- [ ] **Descripción:** Laravel tiene una capa superior llamada **Notifications** que permite enviar el mismo mensaje por Email, Slack, SMS o guardarlo en Base de Datos con una sola clase, reemplazando el uso manual de Mailables/Events.
- **Tarea:** Crear una notificación `TicketUpdatedNotification` que se envíe cuando el ticket cambie de estado o se asigne un agente.
- **Tipo:** 🧠 Nuevo Conocimiento (Clase Notification vs Mailable).
- **Dificultad:** 🟡 Media.

### 3. Gestión de Adjuntos (Archivos)
- [ ] **Descripción:** Ya tienes el modelo `File` y la relación polimórfica, pero falta la API para subir y descargar.
- **Tarea:**
    * Endpoint `POST /tickets/{id}/files`: Subir evidencia extra.
    * Endpoint `GET /files/{uuid}`: Descarga segura (Signed URLs).
- **Tipo:** 🔨 Aplicación (Recuperar conocimientos del Proyecto 2: E-commerce).
- **Dificultad:** 🟡 Media (Repaso de Storage y Signed URLs).

### 4. Estandarización de Errores (Exception Handling)
- [ ] **Descripción:** Si un usuario pide el ticket 9999, Laravel devuelve un 404 HTML por defecto. Una API profesional debe devolver JSON.
- **Tarea:** Configurar `bootstrap/app.php` (Laravel 11) para capturar `ModelNotFoundException` y devolver un JSON estandarizado:
    ```json
    { "error": "Recurso no encontrado", "code": 404 }
    ```
- **Tipo:** 🧠 Nuevo Conocimiento (Global Exception Handler).
- **Dificultad:** 🟢 Baja/Media.

Actualización del LEARNING_LOG.md
Vamos a registrar el cierre del módulo de Auditoría. Agrega esto a tu bitácora:

[03-02-2026] - Auditoría Completa y Ciclo de Vida del Modelo

Ciclo de Vida de Eloquent: Implementé la auditoría para todos los eventos del modelo:

created: Registra valores iniciales en new_values.

updated: Registra el delta (cambio) en old y new.

deleted: Registra lo que se perdió en old_values.

restored: Registra la recuperación del registro.

Manejo de Variables: Aprendí que cada método del Observer tiene su propio alcance (scope). Las variables no se comparten entre métodos; deben definirse explícitamente en cada función para evitar errores de Undefined variable.

# [04-02-2026] - Notificaciones Multicanal y Lógica de Negocio

## 📝 Aprendizajes del Día

**1. Canales de Notificación**
* Aprendí a usar el método `via()` para enviar alertas por múltiples canales (ej. **mail** y **database**) simultáneamente.

**2. Notificaciones en Base de Datos**
* **Configuración:** Se genera la tabla necesaria con el comando:
    ```bash
    php artisan notifications:table
    ```
* **Estructura:** El método `toArray` define el estructura JSON que se guarda en la columna `data`.
* **Uso:** Ideal para alimentar componentes de UI como la "campanita de notificaciones" en el frontend.

**3. Lógica de "Contraparte" (Counterparty)**
* Implementé una lógica para determinar el destinatario de la notificación dinámicamente según quién realiza la acción:
    * Si edita **Cliente** -> Notificar al **Agente**.
    * Si edita **Agente** -> Notificar al **Cliente**.

### 💡 Lección Clave
* **Constructor:** Al instanciar la Notificación, pasar siempre el **Actor** (quien realiza la acción) para tener contexto.
* **Método `toMail`:** Usar la variable `$notifiable` (inyectada automáticamente por Laravel) para saludar al destinatario correcto, en lugar de intentar adivinarlo desde el constructor.

# [04-02-2026] - Manejo Global de Excepciones (Laravel 11)

## 📝 Aprendizajes del Día

**1. Configuración Centralizada (Laravel 11)**
* **Archivo clave:** `bootstrap/app.php`.
* Aprendí que en esta nueva versión, las excepciones ya no van en un "Handler" separado, sino que se configuran fluidamente aquí usando el método `->withExceptions()`.

**2. Renderable Exceptions**
* Utilicé el método `render` dentro de la configuración para capturar excepciones específicas como `NotFoundHttpException` y personalizar su respuesta.

**3. Negociación de Contenido**
* **Problema:** No se debe devolver una respuesta JSON cruda a un usuario que navega vía web (navegador).
* **Solución:** Diferenciar clientes usando condicionales en el request:
    ```php
    if ($request->is('api/*') || $request->expectsJson()) { ... }
    ```

**4. HTTP Status Codes**
* **Regla de oro:** El cuerpo del JSON no es suficiente. Siempre asegurar que el *header* HTTP coincida con el error.
* *Ejemplo:* Pasar el código explícitamente como segundo argumento:
    ```php
    response()->json(['error' => '...'], 404);
    ```

## 📅 [04-02-2026] - Extensión de Proyecto: Fase de Consolidación y Maestría

He decidido extender el Proyecto 3 para reforzar las bases y convertir los conocimientos teóricos en memoria muscular. El objetivo no es solo "terminar", sino dominar el flujo de trabajo.

### 🗺️ Hoja de Ruta de Consolidación

#### 1. 📂 Gestión Avanzada de Archivos (Polimorfismo Completo)
**Meta:** Dejar de temerle al `Storage` y manejar archivos como un recurso completo.
* **Upload:** Implementar subida de archivos adjuntos en Tickets y Respuestas (usando la relación polimórfica existente).
* **Download:** Implementar descarga segura (Signed URLs) para agentes y dueños.
* **Delete:** Permitir eliminar un adjunto (con validación de permisos: solo el dueño puede borrar su archivo).
* **Testing:** Probar la subida y eliminación usando `Storage::fake()`.

#### 2. 🔔 Ecosistema de Notificaciones
**Meta:** Que el sistema se sienta "vivo" y reactivo.
* **Mapa de Eventos:** Identificar todos los disparadores faltantes:
    * `TicketCreated` -> Email de confirmación al cliente + Aviso a Admin.
    * `TicketClosed` -> Email de encuesta/cierre al cliente.
    * `TicketAssigned` -> Email al Agente asignado.
* **Implementación:** Usar el sistema de Notificaciones (BD + Mail) para todos estos casos.

#### 3. 🧠 "The Developer Playbook" (Documentación Conceptual)
**Meta:** Crear mi propia "Biblia de Conceptos" para no olvidar los fundamentos.
* Crear un documento (o sección aquí) que explique **CUÁNDO** y **POR QUÉ** usar cada herramienta, no solo el "cómo".
    * *Ejemplo:* "¿Cuándo uso un Accessor? -> Cuando quiero cambiar el formato visual sin tocar la BD."
    * *Ejemplo:* "¿Por qué TDD? -> Para definir la meta antes de correr."

### 🛡️ Regla de Oro para esta Fase
**"Strict TDD Mode":** Prohibido escribir una sola línea de lógica en el Controlador o Servicio sin haber visto fallar un test primero. Esto es para forzar el hábito de pensar antes de codificar.

### Preparación para Frontend y despliegue
**"Asegurar que la api sea consumible por una IA o un Frontend Real"**
    * *Estandarización Json: Respuestas de error y éxito uniformes.
    * * Preparaicón para Docker/Railway: Revisar variables de entorno y configuraciones para despliegue en Free Tier.

Fecha: [05-02-2026] Estado: Rate Limiting y Blindaje de API completado.

1. Rate Limiting (Limitación de Frecuencia) 🛡️

Concepto: Aprendí a proteger la API contra abusos (fuerza bruta o scripts) limitando el número de peticiones por usuario o IP [cite: 30-01-2026].

Implementación:

Definición en AppServiceProvider usando RateLimiter::for [cite: 30-01-2026].

Uso de Limit::perMinute(60)->by(...) para identificar al usuario por su ID o IP [cite: 30-01-2026].

Aplicación en rutas mediante el middleware throttle:api [cite: 30-01-2026].

Testing: Creé un test que simula un "ataque" con 100 peticiones seguidas, verificando que la petición 61 devuelva un error 429 Too Many Requests [cite: 20-01-2026].

2. Estandarización de Respuestas 🧬

Global Exception Handling: Configuré bootstrap/app.php para capturar errores de modelo no encontrado (404) y devolver JSON en lugar de HTML [cite: 04-02-2026].

## 🏁 Hito Completado: Estabilización del Core (MVP)
**Fecha:** 09 de Febrero, 2026
**Estado:** ✅ Core Funcional (~80%)

### 📝 Resumen del Progreso
Se ha finalizado la estabilización de la arquitectura base **Laravel API + Vue.js Frontend**. El sistema ahora permite el ciclo de vida completo de un ticket con reglas de negocio y permisos (ACL) funcionales.

### 🛠️ Correcciones Críticas Implementadas
1.  **Sincronización de Estructuras de Datos (Data Shape):**
    * Se estandarizó la respuesta de `UserResource` para incluir relaciones anidadas (`user.rol.name`).
    * Se ajustó `Pinia AuthStore` para leer correctamente los roles y calcular permisos (`isAdmin`, `isAgent`).
2.  **Lógica de Asignación de Agentes:**
    * **Backend:** Corrección de colisión de nombres en Eloquent Scopes (`scopeAll` -> `scopeByRole`).
    * **Frontend:** Implementación de lógica condicional en la UI: Dropdown para Admins vs. Botón "Tomar Ticket" para Agentes.
    * **Routing:** Resolución de error `405 Method Not Allowed` separando verbos HTTP (`PUT` para asignar, `POST` para auto-asignar).
3.  **Políticas de Acceso (Policies):**
    * Se corrigieron los `Gate::denies` que impedían a los agentes resolver sus propios tickets.

### 🐛 Deuda Técnica Conocida (Pospuesta)
* UI de Edición de Tickets y Respuestas (Botones presentes pero inactivos).
* Eliminación definitiva de tickets (Soft Deletes pendientes de UI).

### 🎯 Próximo Objetivo: Real-Time Communication
Inicio de la **Fase 2**: Implementación de **WebSockets** para transformar la experiencia de usuario de "Polling" a "Event-Driven".
* **Tecnologías:** Laravel Reverb (Backend) + Laravel Echo / Pusher-JS (Frontend).
* **Casos de Uso:**
    1.  Notificación instantánea de nuevo ticket a los agentes.
    2.  Actualización de respuestas en el chat sin recargar la página.

## 📡 Instalación de Laravel Reverb

Laravel Broadcasting es un sistema que nos permite integrar interfaces de tiempo real y en vivo en nuestra aplicación usando WebSockets. Esto nos permite crear un canal de eventos en el lado del servidor hacia el lado de JavaScript de nuestro cliente, permitiéndonos funcionalidades como notificaciones en tiempo real, aplicaciones de chats y dashboards dinámicos sin requerir refrescar la página.

### 1. Instalar Broadcasting
Ejecuta el siguiente comando:
```bash
sail artisan install:broadcasting
```
Esto preguntará automáticamente si deseas instalar **Laravel Reverb**, a lo cual debemos aceptar.

### 2. Actualizar archivo `.env`
```env
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=redis
```

### 3. Configurar Redis
```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

### 4. Prueba de funcionamiento con Tinker
Realizamos una prueba en Tinker para comprobar que la conexión a la caché/redis sea correcta:
```bash
sail artisan tinker
```
```php
Cache::put('key', 10);
// > true
Cache::get('key');
// > 10
```
Con esto, la configuración base está lista.

---

## 🗺️ Hoja de Ruta: Implementación de WebSockets (Real-Time Helpdesk)

### Fase 1: Fundamentos y el "Canal Público" 📢
*   **Tarea:** Crear el evento `TestEvent` y transmitirlo por un canal público.
*   **Descripción:** Aprenderás a usar la interfaz `ShouldBroadcast` y verás cómo un evento de PHP "viaja" hasta las herramientas de desarrollo del navegador sin restricciones.
*   **Criterio de Aceptación:** Ver el mensaje JSON del evento en la consola de Reverb y en el cliente de prueba (o consola del navegador) sin necesidad de login.

### Fase 2: Seguridad y Canales Privados 🔒
*   **Tarea:** Implementar un canal privado basado en el ID del usuario.
*   **Descripción:** Aprenderás a configurar `routes/channels.php`. Solo el usuario autenticado podrá escuchar sus propios mensajes. Es aquí donde aplicamos la lógica de "Este ticket es mío".
*   **Criterio de Aceptación:** El frontend intenta conectarse y Laravel devuelve un error 403 si el usuario no tiene permiso, y un 200 si es el dueño del canal.

### Fase 3: Notificación Global para Admins (New Ticket) 🎫
*   **Tarea:** Notificar en tiempo real a todos los administradores cuando entre un ticket `Open`.
*   **Descripción:** Pondrás en práctica los canales privados con roles. Solo los usuarios con `role: admin` deben recibir la señal para actualizar su contador de tickets pendientes.
*   **Criterio de Aceptación:** Crear un ticket desde una ventana de incógnito (como cliente) y ver cómo aparece la notificación instantánea en la sesión del Admin.

### Fase 4: Indicadores de Actividad (Typing...) ✍️
*   **Tarea:** Implementar "El agente está escribiendo una respuesta".
*   **Descripción:** Usarás Whisper (Client Events). Son eventos rápidos que no pasan por la base de datos, optimizando el rendimiento para interacciones fugaces.
*   **Criterio de Aceptación:** El cliente ve un texto dinámico que desaparece cuando el agente deja de escribir por más de 3 segundos.

### Fase 5: Hilo de Respuestas en Vivo y Presence Channels 👥
*   **Tarea:** Actualizar el chat del ticket automáticamente y mostrar quién está conectado.
*   **Descripción:** La tarea más compleja. Usarás Presence Channels para saber si el cliente y el agente están viendo el mismo ticket al mismo tiempo.
*   **Criterio de Aceptación:** Al enviar una respuesta, esta aparece en la pantalla de la otra persona sin recargar, y ambos ven un indicador de "En línea".

## 📅 [09-02-2026] - Fase 1: Fundamentos de WebSockets Completado

### 📡 Dominio de Laravel Reverb y Canales Públicos
- **Arquitectura Real-Time:** Logré conectar el flujo completo: Tinker -> Redis -> Queue Worker -> Reverb -> Navegador (Echo).
- **El "Queue Trap":** Entendí que con `ShouldBroadcast`, el evento no llega al socket si el worker no está procesando la cola. 
- **Configuración de Red en Docker:** Aprendí a mapear el puerto `8080` en el `docker-compose.yml` para permitir la entrada de conexiones WebSocket al contenedor de Sail.
- **Depuración:** Uso de `sail artisan reverb:start --debug` para visualizar el tráfico de mensajes entrantes y salientes.

### 💡 Concepto Clave:
El servidor de WebSockets es como una llamada telefónica permanente. Mientras que HTTP cuelga después de cada respuesta, el WS mantiene la línea abierta, permitiendo que el servidor "llame" al cliente cuando hay datos nuevos.

## 📅 [09-02-2026] - Fase 2: Canales Privados y Seguridad Completada

### 🔒 Autorización de WebSockets
- **Private Channels:** Implementé `PrivateChannel` para asegurar que los datos sensibles solo lleguen al destinatario correcto.
- **Broadcast Authorization:** Aprendí a usar `routes/channels.php` para definir reglas de acceso basadas en el objeto `$user` autenticado.
- **Integración Blade/JS:** Resolví la sincronización de IDs entre el backend y el frontend usando directivas de Blade para inyectar variables en JavaScript de forma segura.

## 📅 [10-02-2026] - Depuración de Autorización (403 Forbidden)

### 🔑 Autenticación en Broadast
- **Error 403:** Comprendí que este error ocurre cuando el servidor recibe la petición de suscripción pero la rechaza, ya sea por falta de Token o por lógica de canal fallida [cite: 09-02-2026].
- **CORS vs Auth:** Diferencié que el error de CORS bloquea la conexión antes de llegar a Laravel, mientras que el 403 es una respuesta directa de la lógica de Laravel [cite: 09-02-2026].
- **Headers en Echo:** Aprendí la importancia de enviar el `Authorization: Bearer` manualmente cuando el frontend y el backend corren en puertos distintos [cite: 04-02-2026].

## 📅 [10-02-2026] - Autenticación Stateless en WebSockets

### 🔐 Desafíos de Sanctum + Reverb
- **Guard Specification:** Aprendí que en canales privados, Laravel por defecto busca una sesión de cookie. Si uso Tokens, debo especificar el guard `sanctum` en `routes/channels.php` [cite: 09-02-2026].
- **Auth Flow:** Entendí que el proceso es: 1. El cliente envía el token al endpoint `auth`. 2. Laravel valida el token. 3. Si es válido, ejecuta la lógica del canal (el closure) [cite: 09-02-2026].
- **Debugging de Headers:** La pestaña 'Network' es mi mejor amiga para confirmar que el frontend realmente está enviando el token al servidor de Sockets [cite: 04-02-2026].

"Entendí que las Notificaciones son para humanos (alertas persistentes) y los Broadcast Events son para la sincronización de la interfaz (UI reactivity) [cite: 09-02-2026, 04-02-2026]. El backend 'avisa' y el frontend 'reacciona' [cite: 09-02-2026]."