# AGENTS.md — PuntoSalud2

Guía de referencia para agentes de código que trabajan en este repositorio.

---

## Resumen del proyecto

Sistema de gestión médica en Laravel 12 (PHP 8.2) para manejo de turnos, profesionales, pacientes, pagos y liquidaciones. Frontend con TailwindCSS 4 + Vite. Vistas en Blade. Sin framework JS reactivo (sin Vue/React).

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
```

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
