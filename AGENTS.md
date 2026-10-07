# AGENTS.md — PuntoSalud2

Guía de referencia para agentes de código que trabajan en este repositorio.

---

## Resumen del proyecto

Sistema de gestión médica en Laravel 13 (PHP 8.3) para manejo de turnos, profesionales, pacientes, pagos y liquidaciones. Frontend con TailwindCSS 4 + Vite. Vistas en Blade. Sin framework JS reactivo (sin Vue/React).

---

## Comandos esenciales

### Desarrollo
```bash
composer dev          # Levanta servidor, queue worker y Vite en paralelo (recomendado)
php artisan serve     # Solo servidor Laravel
npm run dev           # Solo Vite (dev)
npm run build         # Build de assets para producción
```

### Tests
```bash
composer test                                          # Limpia config + corre toda la suite
php artisan test                                       # Toda la suite directamente
php artisan test tests/Unit/ExampleTest.php            # Un archivo específico
php artisan test --filter NombreDelTest                # Un test por nombre
php artisan test --testsuite Unit                      # Solo suite Unit
php artisan test --testsuite Feature                   # Solo suite Feature
php artisan test --filter NombreDelTest --stop-on-failure  # Detener al primer fallo
```

Los tests usan SQLite en memoria (`:memory:`). No requieren base de datos real.

### Calidad de código
```bash
./vendor/bin/pint              # Formatea todo el proyecto (Laravel Pint)
./vendor/bin/pint --test       # Verifica sin modificar (modo dry-run)
./vendor/bin/pint app/         # Solo el directorio app/
composer analyse               # Análisis estático (Larastan/PHPStan nivel 5 sobre app/)
```

Los errores históricos están en `phpstan-baseline.neon`. Los errores nuevos se corrigen, no se agregan a la baseline. Las relaciones Eloquent llevan tipo de retorno y `@return BelongsTo<Modelo, $this>` (o el tipo que corresponda).

### Base de datos
```bash
php artisan migrate                     # Ejecutar migraciones pendientes
php artisan migrate:fresh --seed        # Reset completo + seeders
php artisan db:seed                     # Solo seeders
php artisan config:clear                # Limpiar caché de configuración
```

---

## Arquitectura de directorios

```
app/
├── Http/
│   ├── Controllers/     # Un controller por entidad del dominio
│   └── Requests/        # Form requests de validación (si los hay)
├── Models/              # Eloquent models
├── Services/            # Lógica de negocio compleja (ej. PaymentAllocationService)
└── Traits/              # Traits reutilizables (ej. LogsActivity)
database/
├── factories/
├── migrations/
└── seeders/
resources/
├── css/app.css          # Entry point TailwindCSS 4
├── js/app.js
└── views/               # Blade templates organizados por módulo
routes/
├── web.php
└── console.php
tests/
├── Unit/
└── Feature/
```

---

## Convenciones de código PHP / Laravel

### Nomenclatura
- **Clases/Modelos**: `PascalCase` — `ProfessionalLiquidation`, `CashMovement`
- **Métodos y variables**: `camelCase` — `markAsAttended()`, `$finalAmount`
- **Columnas de BD / atributos fillable**: `snake_case` — `appointment_date`, `final_amount`
- **Scopes Eloquent**: prefijo `scope` + `PascalCase` — `scopeForProfessional()`, `scopeCompleted()`
- **Accessors**: `get{Attribute}Attribute` — `getIsPaidAttribute()`, `getEndTimeAttribute()`
- **Rutas**: `kebab-case` — `professional-schedules`, `activity-log`
- **Vistas Blade**: `snake_case` o `kebab-case` dentro de carpeta por módulo

### Imports / use statements
- Un `use` por línea, ordenados alfabéticamente dentro de cada grupo.
- Orden estándar PSR: clases del framework → modelos propios → facades → otros.
- No usar imports con alias salvo colisión real de nombres.

```php
use App\Models\Appointment;
use App\Models\Professional;
use App\Services\PaymentAllocationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
```

### Modelos Eloquent
- Definir siempre `$fillable` explícito (no usar `$guarded = []`).
- Usar `$casts` para tipos: `decimal:2` para dinero, `datetime` para fechas, `boolean` para flags.
- Agrupar con comentarios en español: `/** Relaciones */`, `/** Scopes */`, `/** Accessors */`, `/** Helpers */`.
- Los métodos helper de negocio van al final del modelo.

```php
protected $casts = [
    'appointment_date' => 'datetime',
    'estimated_amount' => 'decimal:2',
    'is_active'        => 'boolean',
];
```

### Controllers
- Inyectar dependencias de servicios en el constructor.
- Usar `$request->filled('campo')` para verificar parámetros opcionales del filtro.
- Retornar `view()` con array compacto de variables.
- Transacciones DB con `DB::transaction()` o `DB::beginTransaction()` / `rollBack()` en operaciones financieras.
- Comentarios en español para bloques de lógica.

### Servicios
- Clases en `app/Services/` para lógica de negocio que no pertenece a un solo modelo.
- Inyectarlos vía constructor en controllers.

### Manejo de errores
- Usar `try/catch` con `DB::rollBack()` en operaciones críticas de pagos/liquidaciones.
- Retornar `redirect()->back()->withErrors()` o `redirect()->back()->with('error', ...)` en fallos.
- Mensajes de error en español.
- No usar `abort()` dentro de lógica de negocio; reservarlo para guards de autorización.

### Migraciones
- Nombre de archivo: `YYYY_MM_DD_HHMMSS_descripcion_snake_case.php`
- Siempre definir método `down()` que revierta la migración.
- Para cambios estructurales, preferir nueva migración antes que modificar una existente.

---

## Frontend / Blade / CSS

### TailwindCSS 4
- Configuración vía `@theme` y `@variant` en `resources/css/app.css`.
- Dark mode deshabilitado intencionalmente — el sistema siempre usa modo claro.
- No agregar `dark:` utilities a menos que se habilite explícitamente.

### Blade
- Componentes reutilizables en `resources/views/components/`.
- Layouts en `resources/views/layouts/`.
- Vistas organizadas por módulo: `views/appointments/`, `views/patients/`, etc.
- Usar `@csrf` en todos los formularios POST/PUT/DELETE.
- Usar `route()` helper para URLs, nunca hardcodear paths.

---

## Dominio y lógica de negocio

### Estados de turno (Appointment.status)
`scheduled` → `attended` | `absent` | `cancelled`

### Valores monetarios
- Almacenados como `DECIMAL(10,2)` en BD.
- Cast a `decimal:2` en modelos.
- Nunca usar `float` para cálculos de dinero; usar sumas con Eloquent `sum()`.

### Idioma
- Código (clases, métodos, variables): **inglés**.
- Comentarios, mensajes de UI, mensajes de error: **español**.
- Textos en vistas Blade: **español**.

### Actividad / Auditoría
- Los modelos auditables usan el trait `LogsActivity`.
- Implementar `activityDescription(): string` en cada modelo que lo use.

---

## Entorno de tests

- `APP_ENV=testing`, SQLite en memoria.
- Tests en `tests/Unit/` para lógica pura de modelos/servicios.
- Tests en `tests/Feature/` para flujos HTTP completos.
- Usar `RefreshDatabase` trait en tests que necesiten BD.
- Ejecutar `php artisan config:clear` si los tests fallan por caché de configuración.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.3. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
