# 📝 Changelog - PuntoSalud

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [2.5.10] - 2025-11-03

### 📊 Separación de Gestión Operativa de Caja y Reportes Históricos

**Agregado:**
- **Nueva vista de Reporte de Caja (reports/cash)**
  - Vista dedicada para reportes históricos con filtrado completo
  - Filtros de fecha, tipo de movimiento y categoría
  - Permite ver cajas de cualquier fecha pasada
  - Botón "Reimprimir" para cajas cerradas
  - Acceso desde menú Reportes (visible solo para admin/profesionales)

- **Nuevo método ReportController::cashReport()**
  - Lógica completa de reporte de caja con filtrado por fecha
  - Cálculo de saldo inicial desde día anterior
  - Filtros por tipo de movimiento y categoría de referencia
  - Resumen por tipo de movimiento agrupado
  - Estado de caja (abierta/cerrada/necesita apertura)

**Modificado:**
- **Vista Cash/Daily restringida a día actual**
  - Eliminados filtros de fecha y categoría
  - Eliminado botón "Ver Reportes"
  - Forzada fecha actual en controlador (no permite ver días anteriores)
  - Enfocada en operación diaria (botones de acción presentes)
  - Solo para recepcionistas en su turno de trabajo

- **Cards de resumen por tipo de movimiento simplificadas**
  - Eliminado cálculo "Neto" de las cards
  - Muestra solo ingresos O egresos según tengan valores
  - Condicional `@if($data['inflows'] > 0)` y `@if($data['outflows'] > 0)`
  - Montos destacados con `text-lg` y `font-semibold`
  - Mejor contraste con variantes dark mode
  - Aplica a ambas vistas: cash/daily y reports/cash

**Separación de responsabilidades:**
- **Vista Operativa (/cash/daily)**
  - Solo día actual, sin navegación histórica
  - Botones de acción: Ingreso Manual, Registrar Gasto, Retirar Dinero
  - Botón Cerrar Caja (cuando está abierta)
  - Enfocada en operación del día
  - Acceso: recepcionistas

- **Vista de Reportes (/reports/cash)**
  - Navegación libre por fechas
  - Filtros completos de tipo y categoría
  - Botón "Ver Reportes" (formato imprimible)
  - Botón "Reimprimir" para cajas cerradas
  - Enfocada en análisis histórico
  - Acceso: administradores y profesionales

**Técnico:**
- Nuevo método: `ReportController::cashReport()`
- Nueva ruta: `Route::get('/reports/cash', [ReportController::class, 'cashReport'])->name('reports.cash')`
- Modificado: `CashController::dailyCash()` - Fuerza `$selectedDate = now()`
- Lógica de filtrado y cálculo de balances compartida entre ambas vistas
- JavaScript de filtros solo en reports/cash

**Archivos Añadidos:**
- `resources/views/reports/cash.blade.php` - Nueva vista de reportes históricos

**Archivos Modificados:**
- `app/Http/Controllers/ReportController.php` - Método cashReport() agregado
- `app/Http/Controllers/CashController.php` - Fecha forzada a hoy
- `resources/views/cash/daily.blade.php` - Filtros removidos, cards simplificadas
- `routes/web.php` - Ruta reports/cash agregada

**Impacto:**
- ✅ Separación clara entre operación diaria y reportes históricos
- ✅ Recepcionistas enfocadas en día actual sin distracciones
- ✅ Administradores con acceso completo a historial
- ✅ Cards de resumen más limpias y fáciles de leer
- ✅ Menos información redundante (sin "Neto")
- ✅ Mejor experiencia visual con montos destacados
- ✅ Botón reimprimir accesible en reportes históricos

---

## [2.5.9] - 2025-11-02

### ⏱️ Sistema de EntreTurnos y Mejoras en Urgencias

**Agregado:**
- **Sistema completo de EntreTurnos**
  - Switch "EntreTurno" en modal de creación/edición de turnos
  - Campo `is_between_turn` (boolean) en tabla appointments
  - Opción de duración de 5 minutos para turnos rápidos
  - Destacado visual con emoji ⏱️ y colores naranjas
  - Modal de creación con borde y header naranja cuando es entreturno
  - Título dinámico: "Nuevo EntreTurno ⏱️" o "Editar EntreTurno ⏱️"

**Mejorado:**
- **Visualización de Urgencias**
  - Emoji 🚨 agregado a todas las urgencias
  - En reportes: urgencias muestran solo emoji (sin hora)
  - En dashboard y agenda: badge rojo con "🚨 URGENCIA"
  - Urgencias ordenadas primero en reporte daily-schedule

- **Visualización de EntreTurnos**
  - Dashboard: Badge naranja "⏱️ ENTRETURNO"
  - Agenda (tabla): Badge naranja + fila con borde/fondo naranja claro
  - Agenda (modal día): Div con borde naranja grueso + badge "⏱️ ENTRETURNO"
  - Reporte daily-schedule: Emoji ⏱️ + hora separada fuera del badge
  - NO se ordenan primero (a diferencia de urgencias)

**Interfaz:**
- **Modal de Nuevo Turno**
  - Switch toggle naranja junto al campo de horario
  - Indicador visual "⏱️ Sí" cuando está activado
  - Todo el modal cambia a tema naranja cuando es entreturno:
    - Borde grueso naranja (ring-4)
    - Header con fondo naranja claro
    - Emoji ⏱️ grande en lugar del icono de calendario
    - Subtítulo: "Programa un entreturno rápido"

- **Modal de Urgencia actualizado**
  - Eliminado selector de fecha (las urgencias son siempre para hoy)
  - Grid reorganizado de 3 a 2 columnas (Monto y Consultorio)
  - Fecha se establece automáticamente al día actual

**Técnico:**
- Migración: `2025_11_03_120000_add_is_between_turn_to_appointments_table.php`
- Campo agregado al fillable y casts del modelo Appointment
- Validación en AppointmentController (store y update): `'is_between_turn' => 'nullable|boolean'`
- Validación de duración actualizada: `in:5,10,15,20,30,40,45,60,90,120`
- JavaScript Alpine.js actualizado para manejar el campo booleano correctamente
- Eager loading optimizado en todos los controladores que retornan appointments

**Archivos Modificados:**
- `database/migrations/2025_11_03_120000_add_is_between_turn_to_appointments_table.php` - Nueva migración
- `app/Models/Appointment.php` - Fillable y casts actualizados
- `app/Http/Controllers/AppointmentController.php` - Validaciones y guardado
- `app/Http/Controllers/DashboardController.php` - Campo agregado a datos
- `app/Http/Controllers/ReportController.php` - Campo agregado al reporte
- `resources/views/appointments/modal.blade.php` - Switch y tema naranja
- `resources/views/appointments/modal-urgency.blade.php` - Fecha removida
- `resources/views/appointments/index.blade.php` - Badge y fondo naranja, JavaScript actualizado
- `resources/views/agenda/index.blade.php` - Badge naranja en modal de día
- `resources/views/dashboard/dashboard.blade.php` - Badge naranja y emoji urgencia
- `resources/views/reports/daily-schedule.blade.php` - Emoji ⏱️ + hora separada

**Diferencias visuales:**

**Urgencias (🚨 - ROJO):**
- Ordenadas primero en todos los listados
- En reportes: solo emoji, sin hora
- Badge rojo con borde rojo
- Fondo rojo claro en filas/cards

**EntreTurnos (⏱️ - NARANJA):**
- NO ordenados primero (mantienen orden cronológico)
- En reportes: emoji + hora separada
- Badge naranja con borde naranja
- Fondo naranja claro en filas/cards
- Modal con borde y header naranja

**Impacto:**
- ✅ Mayor flexibilidad para gestionar consultas rápidas entre turnos programados
- ✅ Identificación visual clara con emoji ⏱️ y colores naranjas
- ✅ Diferenciación clara entre Urgencias (rojas) y EntreTurnos (naranjas)
- ✅ Opción de 5 minutos para atenciones muy breves
- ✅ Mejor organización del flujo de trabajo diario
- ✅ Experiencia de usuario consistente en todas las vistas

---

### 🔄 Anulación de Pagos con Trazabilidad Completa

**Agregado:**
- **Función de anulación de pagos** (`annul()` en PaymentController)
  - Reemplaza el botón "Eliminar" por "Anular" en la vista de pagos
  - Crea un pago negativo (refund) como contraasiento contable
  - Registra automáticamente el movimiento de caja negativo
  - Libera los turnos asociados para que puedan ser cobrados nuevamente
  - Genera nuevo número de recibo para el refund
  - Marca el pago original con estado `'cancelled'`
  - Validaciones:
    - Verifica que la caja esté abierta
    - Detecta si el pago ya fue anulado anteriormente
    - Solo permite anular pagos en estado `'pending'`
    - No permite anular refunds (solo pagos originales)

- **Nuevo estado en ENUM `liquidation_status`**
  - Agregado valor `'cancelled'` al ENUM
  - Valores ahora: `'pending'`, `'liquidated'`, `'not_applicable'`, `'cancelled'`
  - Migración: `2025_11_02_050734_add_cancelled_to_liquidation_status_in_payments_table.php`

- **Ruta de anulación**
  - `POST /payments/{payment}/annul` - Route: `payments.annul`
  - Posicionada antes del resource para evitar conflictos

**Mejorado:**
- **Vista de pagos (payments/index.blade.php)**
  - Botón "Anular" en color naranja con icono de círculo tachado
  - Confirmación detallada con información de la acción
  - Muestra número de recibo de anulación tras éxito
  - Función JavaScript async/await para mejor UX
  - Solo se muestra en pagos `'pending'` que no sean refunds

- **Manejo robusto de estados de liquidación**
  - Operador null coalescing para estados no definidos
  - Caso especial para refunds: muestra "No aplica" (gris)
  - Pagos cancelados: muestra "Cancelado" (rojo)
  - Filtro actualizado con opción 'cancelled'

**Técnico:**
- Archivos modificados:
  - `app/Http/Controllers/PaymentController.php`: Método `annul()` con validaciones completas
  - `routes/web.php`: Ruta `payments.annul` antes del resource
  - `resources/views/payments/index.blade.php`: Botón + función JavaScript
  - `database/migrations/2025_11_02_050734_add_cancelled_to_liquidation_status_in_payments_table.php`: ENUM actualizado
  - `VERSION`: 2.5.9
  - `README.md`: Badge actualizado
  - `CHANGELOG.md`: Esta entrada

**Flujo de anulación:**
1. Pago original → `liquidation_status = 'cancelled'` + concepto `[ANULADO - Ref: xxx]`
2. Refund creado → `payment_type = 'refund'`, `liquidation_status = 'not_applicable'`
3. Movimiento de caja → Monto negativo registrado
4. Turnos → `final_amount = null`, listo para nuevo cobro

**Impacto:**
- ✅ Mantiene trazabilidad contable completa (no se eliminan registros)
- ✅ Integridad de caja garantizada con contraasientos
- ✅ Auditoría completa de anulaciones
- ✅ Turnos liberados para corrección de errores
- ✅ Mejor experiencia de usuario vs "eliminar"

---

## [2.5.8-4] - 2025-11-02

### 🔒 Validación de Caja Abierta y Optimización de Reportes

**Agregado:**
- **Validación de caja abierta antes de operaciones financieras**
  - Método `isCashOpenToday()` en modelo `CashMovement`
  - Validación en creación de ingresos manuales, gastos y retiros
  - Validación en registro de pagos desde múltiples puntos:
    - `PaymentController`: pagos de pacientes
    - `DashboardController`: pagos rápidos desde dashboard
    - `AppointmentController`: pagos de urgencias/walk-ins
  - Mensajes de error claros cuando la caja no está abierta

**Corregido:**
- **Mezcla de categorías en formularios de movimientos de caja**
  - Problema: Selector de gastos mostraba categorías de retiros mezcladas
  - Causa: Filtro por `affects_balance = -1` incluía gastos Y retiros
  - Solución: Filtrado específico por categoría en `movement_types`:
    - Gastos: `category = 'expense_detail'`
    - Retiros: `category = 'withdrawal_detail'`
    - Ingresos: `category = 'income_detail'`

**Mejorado:**
- **Optimización de reportes para impresión en una hoja A4**
  - `daily-schedule-print.blade.php`: Diseño ultra-compacto
    - Fuentes reducidas: 8-10px
    - Padding reducido: 2-4px
    - Márgenes optimizados para A4

  - `professional-liquidation.blade.php`:
    - Diseño compacto con fuentes legibles (12px)
    - Primera card en dos columnas horizontales
    - Título destacado (19px)
    - Desglose de métodos de pago (efectivo/transferencia) en resumen
    - Optimizado para caber en una hoja A4

**Técnico:**
- Archivos modificados:
  - `app/Models/CashMovement.php`: Método `isCashOpenToday()`
  - `app/Http/Controllers/CashController.php`: Validaciones + filtros de categoría
  - `app/Http/Controllers/PaymentController.php`: Validación de caja
  - `app/Http/Controllers/DashboardController.php`: Validación de caja
  - `app/Http/Controllers/AppointmentController.php`: Validación de caja
  - `resources/views/reports/daily-schedule-print.blade.php`: Estilos compactos
  - `resources/views/reports/daily-schedule.blade.php`: Ajustes de diseño
  - `resources/views/reports/professional-liquidation.blade.php`: Estilos print + desglose pagos

**Impacto:**
- ✅ Previene registros financieros cuando la caja está cerrada
- ✅ Mejora integridad de datos de caja
- ✅ Evita confusión entre categorías de movimientos
- ✅ Reportes profesionales listos para imprimir
- ✅ Mejor experiencia de usuario en gestión de caja

---

## [2.5.8] - 2025-10-29

### 🛡️ Fix: Manejo de Error de DNI Duplicado y Búsqueda Mejorada

**Corregido:**
- **Error no controlado al editar/crear paciente/profesional con DNI duplicado**
  - Problema: Error 500 o mensaje de validación críptico (`uvalidation.unique`)
  - Usuario veía mensaje técnico sin contexto
  - Experiencia de usuario negativa y confusa

- **Búsqueda de DNI inflexible**
  - Problema: Búsqueda solo funcionaba con formato exacto (con puntos)
  - Buscar "12345678" no encontraba "12.345.678"
  - Usuarios forzados a recordar formato exacto

**Soluciones implementadas:**

1. **Mensajes de validación personalizados**
   - Agregado mensaje para regla `unique`: "El DNI ingresado ya está registrado en el sistema."
   - Aplicado en `PatientController` y `ProfessionalController`
   - Mensaje claro y contextual para el usuario

2. **Manejo de excepciones de base de datos**
   - Captura `QueryException` para casos edge (race conditions)
   - Detecta código MySQL 1062 (duplicate entry)
   - Respuesta apropiada para AJAX y peticiones regulares

3. **Búsqueda normalizada de DNI**
   - Búsqueda funciona con o sin puntos
   - Query SQL: `REPLACE(dni, ".", "") LIKE ?`
   - Aplicado en índice de pacientes y profesionales

**Técnico:**
- Archivos modificados:
  - `app/Http/Controllers/PatientController.php`
  - `app/Http/Controllers/ProfessionalController.php`
- Agregados mensajes de validación: `'dni.unique' => '...'`
- Agregado catch para `QueryException` con verificación 1062
- Búsqueda mejorada: limpieza de búsqueda + `orWhereRaw()`

**Impacto:**
- ✅ Mensajes de error claros y útiles
- ✅ Búsqueda más flexible e intuitiva
- ✅ Encuentra DNI con o sin formato de puntos
- ✅ Datos del formulario preservados (withInput)
- ✅ Mejor experiencia de usuario general

### 🐛 Corrección Crítica de Cálculo de Balance en Caja

**Corregido:**
- **Bug crítico en cálculo de balance de caja**
  - Problema: Liquidaciones profesionales usaban `movement_date` con fecha medianoche (00:00:00)
  - Otros movimientos usaban `movement_date` con hora actual (`now()`)
  - El método `getCurrentBalanceWithLock()` ordenaba por `movement_date DESC`
  - Resultado: Balances posteriores ignoraban liquidaciones recientes
  - Caso real 27/10: Error de $549,625 en balance por liquidaciones no consideradas

- **Eliminación del campo `movement_date`**
  - Campo redundante que causaba inconsistencias
  - Ahora todos los movimientos usan únicamente `created_at`
  - Simplifica la lógica y previene futuros errores de sincronización
  - Laravel maneja correctamente zonas horarias con `created_at` + Carbon

**Técnico:**
- Migración: `drop_movement_date_from_cash_movements`
- Actualizados 7 archivos (controllers y models)
- Reemplazadas 40+ referencias de `movement_date` por `created_at`
- Ordenamiento y filtros ahora consistentes con `created_at`
- `whereDate()`, `orderBy()` y queries actualizadas

**Archivos Modificados:**
- `app/Models/CashMovement.php` - fillable, casts, scopes y métodos
- `app/Http/Controllers/CashController.php` - queries y ordenamientos
- `app/Http/Controllers/LiquidationController.php` - creación de movimientos
- `app/Http/Controllers/DashboardController.php` - queries
- `app/Http/Controllers/PaymentController.php` - queries
- `app/Http/Controllers/AppointmentController.php` - queries
- `app/Http/Controllers/ReportController.php` - queries
- `resources/views/cash/daily.blade.php` - modal de detalles (JavaScript)
- `resources/views/reports/professional-liquidation.blade.php` - tabla de reintegros
- `database/seeders/CashMovementSeeder.php` - generación de datos de prueba

**Impacto:**
- ✅ Cálculo de balance correcto en todas las operaciones
- ✅ Liquidaciones profesionales se consideran en el orden correcto
- ✅ Código más simple y mantenible
- ✅ Previene errores futuros de sincronización de fechas
- ✅ Compatible con todas las funcionalidades existentes

### 🔧 Fix: Validación de Cierre de Caja con Consultas $0

**Corregido:**
- **Bloqueo de cierre de caja por consultas sin cobro**
  - Problema: Profesionales con consultas atendidas pero con valor $0 (sin cobro) bloqueaban el cierre de caja
  - No se mostraba botón de liquidar porque `professional_amount = 0`
  - La validación detectaba turnos sin liquidar y impedía cerrar la caja
  - Caso real: Dos profesionales con consultas gratuitas bloquearon operación

- **Solución implementada**
  - Modificada validación de cierre de caja en `CashController::closeCash()`
  - Ahora calcula monto total de turnos atendidos por profesional
  - Excluye automáticamente profesionales con monto total = $0 de la validación
  - No requiere liquidación manual para consultas sin cobro

**Técnico:**
- Archivo modificado: `app/Http/Controllers/CashController.php`
- Agregado cálculo de `totalAmount` antes de verificar liquidación
- Condición: `if ($totalAmount == 0) return false;`
- Profesionales con consultas $0 quedan excluidos automáticamente

**Impacto:**
- ✅ Cierre de caja no bloqueado por consultas gratuitas o sin cobro
- ✅ Validación más inteligente y contextual
- ✅ No requiere intervención manual para casos especiales
- ✅ Mantiene validación estricta para consultas con cobro
- ✅ Solución transparente para el usuario

### 🎨 UX: Eliminar Mensaje Confuso en Reporte de Cierre

**Corregido:**
- **Mensaje "Caja sin cerrar" aparecía en reportes de cajas ya cerradas**
  - Problema: El reporte mostraba "Caja sin cerrar - Se requiere conteo..." incluso cuando la caja estaba cerrada
  - Causaba confusión al usuario al visualizar reportes históricos
  - El mensaje aparecía en situación incorrecta

- **Solución implementada**
  - Eliminado bloque `@else` que mostraba el mensaje confuso
  - Ahora solo muestra "Estado de Cierre" cuando existe movimiento de cierre
  - Si no hay cierre, simplemente no muestra esa sección (comportamiento correcto)

**Técnico:**
- Archivo modificado: `resources/views/cash/daily-report.blade.php`
- Eliminadas líneas 92-104 (bloque @else con alerta amber)
- Simplifica la lógica de presentación del reporte

**Impacto:**
- ✅ Elimina confusión al visualizar reportes
- ✅ Mensajes más claros y contextuales
- ✅ Mejor experiencia de usuario
- ✅ Interfaz más limpia

### 🎨 Diseño: Optimización del Layout del Reporte de Cierre

**Mejorado:**
- **Resumen financiero más compacto y legible**
  - Cards de resumen financiero ahora siempre en una sola línea (4 columnas)
  - Antes: 2 columnas en móvil, 4 en desktop (ocupaba más espacio vertical)
  - Ahora: 4 columnas siempre (reduce espacio en impresión)
  - Mejor aprovechamiento del espacio en la hoja impresa

- **Encabezado más compacto**
  - Reducido padding de `p-6` a `p-3` (pantalla)
  - Reducido padding de `print:p-2` a `print:p-1` (impresión)
  - Menos espacio vertical desperdiciado

- **Fecha en español**
  - Cambio de `format()` a `translatedFormat()`
  - Usa locale configurado (es_AR)
  - Muestra día y mes en español correctamente

**Técnico:**
- Archivo modificado: `resources/views/cash/daily-report.blade.php`
- Grid: `grid-cols-2 md:grid-cols-4` → `grid-cols-4`
- Padding encabezado: `p-6 print:p-2` → `p-3 print:p-1`
- Fecha: `$selectedDate->format()` → `$selectedDate->translatedFormat()`

**Impacto:**
- ✅ Reporte más compacto (cabe mejor en una hoja)
- ✅ Mejor legibilidad del resumen financiero
- ✅ Menos desperdicio de espacio vertical
- ✅ Localización correcta de fechas

### 📊 Mejoras en Detalle de Movimientos del Reporte Diario

**Añadido:**
- **Resumen de pagos a profesionales (💸)**
  - Nueva sección con tabla resumida de pagos del día
  - Muestra profesional, notas y monto pagado
  - Total de pagos a profesionales calculado automáticamente
  - Facilita verificación de liquidaciones pagadas

- **Desglose detallado de gastos (📤)**
  - Nueva sección con tabla de gastos del día
  - Muestra hora, descripción y monto de cada gasto
  - Total de gastos calculado automáticamente
  - Facilita auditoría de egresos

- **Desglose de otros egresos (📋)**
  - Nueva sección para movimientos no categorizados
  - Incluye tipo, hora, descripción y monto
  - Separa claramente de gastos y pagos profesionales
  - Total de otros egresos calculado

- **Visualización de observaciones del cierre**
  - Extracción automática de notas del cierre de caja
  - Formato destacado en sección de Estado de Cierre
  - Regex para extraer observaciones del campo description
  - Permite registrar y visualizar incidencias del día

**Técnico:**
- Archivo modificado: `resources/views/cash/daily-report.blade.php`
- Filtros agregados:
  - `$professionalPayments = $movements->filter(fn($m) => $m->movementType?->code === 'professional_payment')`
  - `$expenses = $movements->filter(fn($m) => $m->movementType?->code === 'expense')`
  - `$otherOutflows = $movements->filter(fn($m) => ... && $m->amount < 0)`
- Extracción de notas: `preg_match('/\$[0-9,]+\.?\d*\s*-\s*(.+)$/', $description, $notesMatch)`
- Tablas con formato consistente (profesional/hora, descripción/notas, monto)
- Totales en negrita con border-top-2

**Impacto:**
- ✅ Resumen claro de pagos a profesionales del día
- ✅ Mayor transparencia en movimientos de egresos
- ✅ Desglose claro de gastos del día
- ✅ Observaciones del cierre visibles en reporte
- ✅ Mejor trazabilidad de movimientos no estándar
- ✅ Facilita auditoría y control de caja

### 🎨 Categorías Dinámicas y Optimización Ultra-Compacta del Reporte

**Mejorado:**
- **Saldo Final que incluye liquidación de Dra. Zalazar**
  - Nuevo card "Saldo Final" en resumen financiero
  - Incluye automáticamente todos los ingresos de la propietaria (professional_id = 1)
  - Cálculo: Saldo Final Teórico + Total Ingresos Dra. Zalazar
  - Removido card "Saldo Inicial" para mejor visualización
  - Refleja el saldo real que queda en caja considerando que su liquidación no se retira

**Añadido:**
- **Desglose de Ingresos Dra. Natalia Zalazar**
  - Nueva sección después de "Liquidación por Profesional"
  - Muestra liquidación de pacientes (comisión por consultas del día)
  - Detalla cada pago de saldos con descripción completa
  - Total general de ingresos de la Dra. Zalazar
  - Facilita auditoría completa de sus ingresos diarios
  - Formato ultra-compacto consistente con resto del reporte

**Mejorado:**
- **Categorías de movimientos dinámicas desde base de datos**
  - Selectores de ingresos, gastos y retiros ahora cargan desde `movement_types` table
  - Antes: categorías hardcodeadas en arrays PHP
  - Ahora: carga dinámica con filtros por categoría y estado activo
  - Se excluyen tipos especiales (patient_payment, cash_opening, cash_closing)
  - Orden configurable desde base de datos

- **Desglose de Pagos Módulo Profesional**
  - Nueva sección en reporte de cierre con detalle de cada pago
  - Muestra nombre del profesional y monto pagado
  - Usa relación morphTo 'reference' para identificar profesional
  - Total calculado automáticamente
  - Facilita auditoría de pagos a profesionales

- **Optimización vertical extrema del reporte**
  - Fuentes reducidas: `text-[11px]` en pantalla, `print:text-[9px]` en impresión
  - Padding ultra-compacto: `py-[1px] px-1` en celdas
  - Headers con `py-[2px] px-1`
  - Márgenes mínimos entre secciones: `mb-2 print:mb-0.5`
  - Mejora significativa en cantidad de páginas impresas

- **Clases CSS reutilizables en layout de impresión**
  - `.report-section-title` - títulos de sección con responsive sizing (text-sm en print)
  - `.report-table` - tablas ultra-compactas (9px en print)
  - `.report-th` - headers de tabla (padding 2px 4px)
  - `.report-td` - celdas de tabla (padding 1px 4px)
  - Estilos consistentes en screen y print con `@apply`

- **Iconos y nombres dinámicos en tipos de movimiento**
  - Íconos cargados desde `movement_types.icon` en lugar de switch hardcoded
  - Nombres de tipos también dinámicos desde BD
  - Permite agregar nuevos tipos sin modificar código
  - Icon agregado al array `movementsByType` en controlador

**Técnico:**
- Eager loading de 'reference' morphTo relationship en CashController:532
- Filtrado con `whereNotIn` para excluir tipos especiales
- TailwindCSS arbitrary values para máximo control de spacing
- CSS @apply para reutilización de clases Tailwind
- Media queries @print optimizadas
- Categorías dinámicas en 3 métodos: ingresos (líneas 715-753), gastos (151-199), retiros (634-674)

**Archivos Modificados:**
- `app/Http/Controllers/CashController.php` - categorías dinámicas, eager loading, icon en array
- `resources/views/cash/daily-report.blade.php` - desglose profesionales, estilos ultra-compactos
- `resources/views/cash/daily.blade.php` - iconos y nombres dinámicos
- `resources/views/layouts/print.blade.php` - clases reutilizables para reportes

**Impacto:**
- ✅ Categorías configurables sin modificar código
- ✅ Detalle claro de pagos profesionales en cierre
- ✅ Reporte cabe en menos páginas (ahorro de papel y tinta)
- ✅ Estilos mantenibles y reutilizables
- ✅ Mayor flexibilidad del sistema
- ✅ Nuevos tipos de movimiento se integran automáticamente

### 🐛 Hotfix: Bug de Navegación de Meses en Agenda

**Corregido:**
- **Error crítico en navegación de calendario**
  - Problema: Carbon overflow cuando día actual es 31 y se navega a meses con 30 días
  - Síntoma: Septiembre mostraba como Octubre, Noviembre no aparecía
  - Ejemplo: Estar en Oct 31 y navegar a Sept causaba overflow a Oct 1
  - Bug afectaba navegación mensual en vista de Agenda

- **Solución implementada**
  - Forzar día 1 al crear fecha desde formato año-mes
  - Cambio: `Carbon::createFromFormat('Y-m', $month)` → `Carbon::createFromFormat('Y-m-d', $month . '-01')`
  - Comentario explicativo del bug agregado en código
  - Previene overflow automático de Carbon

**Técnico:**
- Archivo modificado: `app/Http/Controllers/AgendaController.php`
- Línea 21-23: Creación de fecha con día explícito en 1
- Comentario: "Bug: Si hoy es 31 y navegas a un mes con 30 días, Carbon hace overflow"
- Branch: hotfix-agenda-month-overflow (merged)

**Impacto:**
- ✅ Navegación de meses funciona correctamente siempre
- ✅ Previene confusión de usuarios
- ✅ Fix aplicable a cualquier día del mes
- ✅ Solución permanente sin efectos secundarios

---

## [2.5.7] - 2025-10-28

### 🗂️ Sistema de Tipos de Movimiento en Base de Datos

**Añadido:**
- **Tabla `movement_types` con estructura jerárquica**
  - Soporte para tipos principales y subcategorías (parent_type_id)
  - 11 tipos principales: apertura/cierre de caja, pagos, gastos, retiros, etc.
  - 17 subcategorías: detalles de gastos, ingresos y retiros
  - Campos: code, name, description, category, affects_balance, icon, color, is_active, order
  - Sistema de iconos emoji y colores para mejor UX

- **Modelo MovementType con funcionalidades completas**
  - Relaciones: parent, children, cashMovements
  - Scopes: mainTypes, subTypes, active, byCategory, byCode
  - Helper estático: `getIdByCode()` con caché en memoria
  - Método: `getAffectsBalanceText()` para etiquetas legibles

- **Interfaz de administración completa**
  - Vista index: listado de tipos principales y subcategorías
  - Vista create: formulario completo para nuevos tipos
  - Vista edit: formulario de edición con alertas si tiene movimientos
  - Toggle de estado activo/inactivo desde listado
  - Validación: no permite eliminar si tiene movimientos o subcategorías asociadas
  - Acceso restringido a administradores

- **Nueva entrada en menú de navegación**
  - "Tipos de Movimientos" en sección de Configuración
  - Visible solo para usuarios administradores
  - Breadcrumbs de navegación en todas las vistas

**Mejorado:**
- **Migración de datos existentes**
  - 78 registros de cash_movements migrados exitosamente
  - Campo `type` (string) → `movement_type_id` (FK)
  - Campo `reference_type` normalizado a nombres completos de clase
  - Eliminación de columna `type` obsoleta

- **Controladores actualizados para usar BD**
  - CashController: usa MovementType::getIdByCode() en lugar de strings
  - PaymentController: tipos desde BD
  - AppointmentController: tipos desde BD
  - DashboardController: tipos desde BD
  - LiquidationController: tipos desde BD
  - Uso de subcategorías específicas en lugar de tipos genéricos

- **Modelo CashMovement refactorizado**
  - Relación `movementType()` agregada
  - Scopes actualizados: byType, incomes, expenses, withdrawals
  - Campo `type` removido del fillable
  - Eager loading de movementType en consultas

- **Vista de Caja Diaria actualizada**
  - Muestra icono y nombre desde movementType
  - Colores dinámicos según movementType->color
  - JavaScript actualizado para usar movementType->code
  - Modal de detalles muestra información del tipo

**Técnico:**
- Migraciones con manejo seguro de datos existentes
- Seeder completo con todos los tipos del sistema
- Foreign key con restricción `onDelete('restrict')`
- Caché de códigos en MovementType para optimización
- Validaciones completas en MovementTypeController

**Archivos Añadidos:**
- `database/migrations/2025_10_26_071829_create_movement_types_table.php`
- `database/migrations/2025_10_26_072215_add_movement_type_id_to_cash_movements_table.php`
- `database/seeders/MovementTypeSeeder.php`
- `app/Models/MovementType.php`
- `app/Http/Controllers/MovementTypeController.php`
- `resources/views/settings/movement-types/index.blade.php`
- `resources/views/settings/movement-types/create.blade.php`
- `resources/views/settings/movement-types/edit.blade.php`

**Archivos Modificados:**
- `app/Models/CashMovement.php` - relación y scopes
- `app/Http/Controllers/CashController.php` - uso de MovementType
- `app/Http/Controllers/PaymentController.php` - uso de MovementType
- `app/Http/Controllers/AppointmentController.php` - uso de MovementType
- `app/Http/Controllers/DashboardController.php` - uso de MovementType
- `app/Http/Controllers/LiquidationController.php` - uso de MovementType
- `resources/views/cash/daily.blade.php` - muestra tipos desde BD
- `resources/views/layouts/app.blade.php` - menú admin
- `routes/web.php` - rutas de configuración

**Impacto:**
- ✅ Tipos de movimiento ahora configurables sin código
- ✅ Sistema más flexible y mantenible
- ✅ Mejor trazabilidad de categorías de movimientos
- ✅ Interfaz administrativa para gestión completa
- ✅ Migración exitosa sin pérdida de datos
- ✅ Base sólida para futuros reportes personalizados

---

## [2.5.6] - 2025-10-24

### 📅 Mejoras en Entreturnos y Gestión de Ingresos

**Añadido:**
- **Selector de fecha en Entreturnos/Urgencias**
  - Campo de fecha (sin hora) en modal de entreturno/urgencia
  - Fecha mínima: día actual en adelante
  - Fecha preseleccionada: día actual
  - Validación backend: `after_or_equal:today`
  - La hora se establece automáticamente al momento del registro

- **Nueva categoría de ingreso manual**
  - "Pago de Saldos Dra. Zalazar" agregada a ingresos manuales

**Mejorado:**
- **Selector de profesionales en ingresos manuales**
  - Ahora muestra TODOS los profesionales activos (sin restricción)
  - Antes: solo profesionales con turnos del día
  - Carga relación `specialty` para mejor visualización

- **Categorías de ingresos simplificadas**
  - Eliminado: "Venta de Producto"
  - Eliminado: "Cobro de Servicio Extra"
  - Categorías actuales: Pago Módulo Profesional, Pago de Saldos Dra. Zalazar, Corrección de Ingreso, Otros Ingresos

**Archivos Modificados:**
- `resources/views/appointments/modal-urgency.blade.php` - campo fecha agregado
- `resources/views/dashboard/dashboard.blade.php` - fecha inicializada en formulario
- `app/Http/Controllers/AppointmentController.php` - validación y procesamiento de fecha
- `app/Http/Controllers/CashController.php` - categorías actualizadas, profesionales sin restricción

**Impacto:**
- ✅ Mayor flexibilidad para programar entreturnos en fechas futuras
- ✅ Categorías de ingresos más específicas y relevantes al negocio
- ✅ Todos los profesionales disponibles en ingresos manuales
- ✅ UX mejorada con fecha preseleccionada

---

## [2.5.5] - 2025-10-23

### 💼 Mejoras en Gestión de Datos y Métodos de Pago

**Añadido:**
- **Campos adicionales en Pacientes**
  - `titular_obra_social`: Titular de la obra social
  - `plan_obra_social`: Plan específico de la obra social
  - Sección dedicada "Información de Obra Social" en formularios
  - Validaciones en backend (nullable, string, max:255)
  - Migración: `add_obra_social_fields_to_patients_table`

- **Campos adicionales en Profesionales**
  - `license_number`: Número de matrícula profesional
  - `notes`: Notas adicionales sobre el profesional (max:1000)
  - Sección "Notas Adicionales" en formularios con textarea
  - Validaciones en backend
  - Migración: `add_license_number_and_notes_to_professionals_table`

- **Método de pago: Tarjetas separadas**
  - Antes: cash, transfer, card (3 métodos)
  - Ahora: cash, transfer, debit_card, credit_card (4 métodos)
  - Iconos mantenidos: 💵 Efectivo, 🏦 Transferencia, 💳 Débito/Crédito
  - Validaciones actualizadas en todos los controladores

**Mejorado:**
- **Vista de Profesionales**
  - Tabla reorganizada: columnas DNI y Email eliminadas
  - Nueva columna: Matrícula (license_number)
  - Grid de formulario expandido de 3 a 4 columnas
  - Mejor visualización de información profesional

- **UX de Urgencias**
  - Icono 🚨 removido de etiquetas "URGENCIA/ENTRETURNO"
  - Solo texto "URGENCIA" o "ENTRETURNO" para evitar exaltación
  - Aplicado en: Dashboard, Appointments, Agenda

**Archivos Modificados:**
- `database/migrations/2025_10_23_113114_add_license_number_and_notes_to_professionals_table.php`
- `database/migrations/2025_10_23_113727_add_obra_social_fields_to_patients_table.php`
- `app/Models/Patient.php` - fillable actualizado
- `app/Models/Professional.php` - fillable actualizado
- `app/Http/Controllers/PatientController.php` - validaciones
- `app/Http/Controllers/ProfessionalController.php` - validaciones
- `app/Http/Controllers/PaymentController.php` - métodos de pago
- `app/Http/Controllers/DashboardController.php` - métodos de pago
- `app/Http/Controllers/AppointmentController.php` - métodos de pago
- `resources/views/patients/modal.blade.php` - nuevos campos
- `resources/views/patients/index.blade.php` - JavaScript actualizado
- `resources/views/professionals/modal.blade.php` - matrícula y notas
- `resources/views/professionals/index.blade.php` - tabla y formularios
- `resources/views/appointments/modal.blade.php` - métodos de pago con iconos
- `resources/views/appointments/index.blade.php` - sin emoji urgencia
- `resources/views/payments/create.blade.php` - métodos de pago
- `resources/views/payments/edit.blade.php` - métodos de pago
- `resources/views/payments/index.blade.php` - filtro métodos de pago
- `resources/views/components/payment-modal.blade.php` - métodos de pago
- `resources/views/cash/expense-form.blade.php` - métodos de pago
- `resources/views/dashboard/dashboard.blade.php` - sin emoji urgencia
- `resources/views/dashboard/dashboard-appointments.blade.php` - sin emoji urgencia
- `resources/views/agenda/index.blade.php` - sin emoji urgencia

**Impacto:**
- ✅ Mayor detalle en datos de pacientes (obras sociales)
- ✅ Mejor gestión de información profesional (matrículas y notas)
- ✅ Métodos de pago más específicos (4 opciones)
- ✅ UX más profesional y menos exaltada en urgencias
- ✅ Consistencia en iconos de métodos de pago en todo el sistema

---

## [2.5.4] - 2025-10-23

### 🎯 Mejoras en UX y Gestión de Horarios

**Añadido:**
- **Sistema de búsqueda en Profesionales**
  - Búsqueda en tiempo real con debounce de 500ms
  - Filtrado por nombre, DNI o email
  - Filtros combinados con especialidad y estado
  - Procesamiento en backend para mejor rendimiento
  - Watchers automáticos en Alpine.js

**Mejorado:**
- **Ampliación de horario de atención**: 8:00-18:00 → 8:00-21:00
  - Generación de slots disponibles hasta las 21:00
  - Validación de inputs actualizada
  - Mensajes informativos actualizados en modales

- **Duraciones de turnos más flexibles**
  - Agregada opción de 10 minutos
  - Agregada opción de 90 minutos (1h 30min)
  - Agregada opción de 120 minutos (2 horas)
  - Validaciones actualizadas en todos los métodos

**Optimizado:**
- **Vista de Agenda**
  - Calendario de 7 columnas → 5 columnas (solo días laborables)
  - Sábados y domingos ocultos del calendario
  - Mejor uso del espacio en pantalla
  - Navegación más limpia

- **Filtrado de usuarios inactivos**
  - Pacientes inactivos no aparecen en selectores de agenda
  - Pacientes inactivos no aparecen en selectores de turnos
  - Profesionales inactivos filtrados en todas las vistas
  - Mejora en la calidad de datos mostrados

**Archivos Modificados:**
- `app/Http/Controllers/ProfessionalController.php` - Respuesta AJAX optimizada
- `app/Http/Controllers/AgendaController.php` - Filtro de pacientes activos
- `app/Http/Controllers/AppointmentController.php` - Horarios, duraciones y filtros
- `resources/views/professionals/index.blade.php` - Sistema de búsqueda completo
- `resources/views/agenda/index.blade.php` - Calendario de 5 días
- `resources/views/appointments/modal.blade.php` - Horarios y duraciones actualizados

**Impacto:**
- ✅ Búsqueda más rápida y eficiente en profesionales
- ✅ Calendario enfocado en días laborables (Lun-Vie)
- ✅ Mayor flexibilidad en horarios (8:00-21:00)
- ✅ Más opciones de duración de turnos (10min a 2hs)
- ✅ Datos más limpios (solo usuarios activos)

---

## [2.5.4] - 2025-10-20

### 🚀 Optimización Masiva de Rendimiento del Dashboard

**Backend - Optimización de Queries:**
- **Unificación de Counts**: 5 queries SQL → 1 query con agregaciones
  - Reducción del 80% en queries para estadísticas de consultas
  - Uso de `SUM(CASE WHEN...)` para calcular todos los estados en una sola query

- **Cálculo de Ingresos Optimizado**: ~200 operaciones en memoria → 1 query SQL
  - Reducción del 95% en operaciones
  - Query SQL puro con JOINs y agregaciones por método de pago
  - Uso de `COALESCE` para manejar valores nulos

- **Profesionales Activos**: 10 queries → 1 query con subquery
  - Reducción del 90% en queries
  - Uso de `EXISTS` para detectar profesionales en consulta
  - Cálculo de disponibles en una sola operación

- **Eliminación de N+1**: Agregado eager loading de `paymentAppointments`
  - 100% de queries N+1 eliminadas
  - Uso de relaciones cargadas en lugar de queries adicionales

**Frontend - Eliminación Total de Parpadeos:**
- **Layout Principal**: CSS global `[x-cloak]` agregado
  - `x-cloak` en overlay mobile del sidebar
  - Estado inicial correcto del sidebar (collapsed en mobile)
  - Fuentes con `display=swap` para evitar FOIT

- **Navegación Principal**: Todos los textos protegidos contra flash
  - `x-cloak` en label "Menú"
  - `x-cloak` en todos los títulos de items del menú
  - `x-cloak` en tooltips del sidebar colapsado

- **Navegación de Usuario**: Componentes ocultos durante carga
  - `x-cloak` en información del usuario
  - `x-cloak` en chevron del dropdown
  - `x-cloak` en menús desplegables
  - `x-cloak` en tooltips de usuario

**Impacto Total:**
- ✅ Dashboard carga **60-70% más rápido**
- ✅ Queries reducidas de ~20 → ~5 (**-75%**)
- ✅ **Cero parpadeos visuales** en toda la interfaz
- ✅ Mejor experiencia en conexiones lentas
- ✅ Código más eficiente y escalable

**Archivos Modificados:**
- `app/Http/Controllers/DashboardController.php` - 4 optimizaciones de queries
- `resources/views/layouts/app.blade.php` - CSS global y estado inicial correcto
- `resources/views/layouts/nav-main.blade.php` - x-cloak en navegación
- `resources/views/layouts/nav-user.blade.php` - x-cloak en usuario

**Técnico:**
- Uso extensivo de SQL raw para agregaciones complejas
- Parámetros bindeados para seguridad en subqueries
- Alpine.js con `x-cloak` en todos los componentes dinámicos
- Estado inicial calculado en `x-data` para evitar flash

---

## [2.5.3] - 2025-10-20

### � Optimizaci�n de Rendimiento y Fix de Modales

**A�adido:**
- Atributo `defer` en todos los scripts de CDN (jQuery y Select2)
  - Dashboard, Appointments y Agenda optimizados
  - Mejora estimada del 20-30% en tiempo de carga inicial
  - Scripts se descargan en paralelo sin bloquear rendering

**Corregido:**
- Flash visual de modales al cargar p�ginas
  - Agregado `x-cloak` a modal de pacientes
  - Agregado `x-cloak` a modal principal y de especialidades de profesionales
  - Agregado CSS `[x-cloak] { display: none !important; }` en todas las vistas necesarias
  - Resuelve problema donde modales eran visibles por 1-30 segundos

**T�cnico:**
- Auditor�a completa de todas las vistas del proyecto
- Solo 3 vistas usan scripts CDN (todas optimizadas)
- Alpine.js ahora oculta correctamente los modales durante inicializaci�n

**Archivos Modificados:**
- `resources/views/patients/modal.blade.php`
- `resources/views/patients/index.blade.php`
- `resources/views/professionals/modal.blade.php`
- `resources/views/professionals/index.blade.php`
- `resources/views/appointments/index.blade.php`
- `resources/views/agenda/index.blade.php`
- `resources/views/dashboard/dashboard.blade.php`

---

### <� Mejoras en UX y Validaciones de Caja

**A�adido:**
- Auto-submit en selector de fecha (Cash/Daily)
  - Evento `@change="filterByDate()"` para recarga autom�tica
  - Elimina necesidad de hacer clic en bot�n "Filtrar"
  - Mejora significativa en UX y velocidad de navegaci�n

**Corregido:**
- Validaci�n de liquidaciones pendientes en cierre de caja
  - Cambio de l�gica: verifica existencia de liquidaciones, no payment_status
  - Detecta profesionales con turnos atendidos sin liquidaci�n creada
  - Query optimizado con filtros correctos

**A�adido:**
- Usuario Priscila agregado al UserSeeder
  - Email: gomezpri20@gmail.com
  - Rol: receptionist
  - Datos de producci�n para desarrollo

**Archivos Modificados:**
- `app/Http/Controllers/CashController.php`
- `resources/views/cash/daily.blade.php`
- `database/seeders/UserSeeder.php`

---

### = Validaci�n de Liquidaciones Pendientes

**A�adido:**
- Bloqueo de cierre de caja con liquidaciones profesionales pendientes
  - Validaci�n autom�tica al intentar cerrar caja
  - Verifica liquidaciones con `payment_status = 'pending'`
  - Mensaje descriptivo con nombres de profesionales pendientes

**Flujo de Validaci�n:**
1. Usuario intenta cerrar caja desde dashboard
2. Sistema verifica que no exista cierre previo
3. Sistema consulta liquidaciones pendientes de la fecha
4. Si hay pendientes: muestra error con lista de profesionales
5. Si no hay pendientes: permite continuar con el cierre

**Beneficios:**
- Previene cierre de caja con deudas profesionales pendientes
- Garantiza consistencia financiera del sistema
- Evita errores contables por liquidaciones olvidadas

**Archivos Modificados:**
- `app/Http/Controllers/CashController.php`

---

## [2.5.2] - 2025-10-17

### =� Sistema de Entreturnos/Urgencias

**A�adido:**
- Sistema completo de atenci�n de urgencias sin turno programado
  - Modal de registro desde dashboard con acceso r�pido
  - Valor por defecto $0 (modificable seg�n necesidad)
  - Registro con fecha y hora actual autom�tica
  - B�squeda avanzada de profesionales y pacientes con Select2

**Interfaz:**
- Destacado visual en ROJO en todos los listados
  - Identificador emoji =� + badge "URGENCIA"
  - Separaci�n visual clara del resto de turnos
  - Prioridad en ordenamiento de consultas

**Funcionalidad:**
- Integraci�n completa con sistema de pagos
- Incluido autom�ticamente en liquidaciones profesionales
- Compatible con todos los m�todos de pago
- Trazabilidad completa en movimientos de caja

**Validaciones:**
- Campos profesional, paciente y monto requeridos
- Consultorio opcional
- Notas/concepto para registrar detalles
- Flag `is_urgency` en modelo Appointment

**Archivos Modificados:**
- `app/Http/Controllers/AppointmentController.php`
- `resources/views/dashboard/dashboard.blade.php`
- `resources/views/appointments/modal-urgency.blade.php`
- `database/migrations/xxxx_add_is_urgency_to_appointments.php`

---

## [2.5.1] - 2025-10-14

### =� Sistema de Impresi�n Profesional de Recibos A5

**A�adido:**
- Vista de impresi�n optimizada para formato A5 (148 x 210 mm)
  - Dise�o profesional con logo y datos de la empresa
  - Informaci�n completa del pago y paciente
  - Desglose claro de m�todo de pago y concepto
  - C�digo QR con enlace al recibo (futuro uso)

**Caracter�sticas:**
- Auto-impresi�n con par�metro `?print=1` en URL
- Cierre autom�tico de ventana despu�s de imprimir
- Vista previa antes de imprimir (sin par�metro)
- Responsive para diferentes tama�os de papel

**Interfaz:**
- Bot�n "Imprimir Recibo" en vista de pago
- Modal de confirmaci�n despu�s de cobro
  - Opci�n: "S�, imprimir" o "No"
  - Abre en nueva pesta�a para no perder contexto

**T�cnico:**
- CSS optimizado para impresi�n
- M�rgenes y padding ajustados para A5
- Fuentes legibles y profesionales
- Compatible con todos los navegadores modernos

**Archivos A�adidos:**
- `resources/views/payments/receipt.blade.php`
- `app/Http/Controllers/PaymentController.php::printReceipt()`

**Archivos Modificados:**
- `resources/views/payments/show.blade.php`
- `resources/views/dashboard/dashboard.blade.php`
- `routes/web.php`

---

## [2.5.0] - 2025-10-14

### =" Sincronizaci�n y Mejora del Sistema de Recibos

**A�adido:**
- Sistema de numeraci�n autom�tica de recibos
  - Formato: YYYYMM####  (A�o + Mes + 4 d�gitos)
  - Ejemplo: 202510001, 202510002, etc.
  - Reinicio autom�tico cada mes
  - Generaci�n secuencial garantizada

**Corregido:**
- Sincronizaci�n de n�meros de recibo
  - Campo `receipt_number` agregado a migraciones existentes
  - Seeders actualizados para generar n�meros correctos
  - Migraci�n retroactiva para pagos existentes sin n�mero

**Mejora:**
- Generaci�n de recibos en DashboardController
  - M�todo `generateReceiptNumber()` privado
  - Query optimizado para obtener �ltimo n�mero del mes
  - Manejo de casos edge (primer pago del mes)
  - Padding con ceros a la izquierda

**Archivos Modificados:**
- `app/Http/Controllers/DashboardController.php`
- `database/seeders/PaymentSeeder.php`
- `database/migrations/xxxx_add_receipt_number_to_payments.php`

---

## [2.4.0] - 2025-10-13

### <� Sistema Integral de Gesti�n de Caja

**A�adido:**
- Sistema completo de apertura y cierre de caja
  - Validaciones autom�ticas por fecha
  - Bloqueo de operaciones si caja no est� abierta
  - Control de estado al login de recepcionistas

**Alertas Inteligentes:**
- Dashboard con alertas para recepcionistas
  - Caja sin cerrar de d�a anterior (alerta roja)
  - Caja del d�a sin abrir (alerta amarilla)
  - Botones de acci�n directa desde alertas

**Movimientos de Caja:**
- Tipos completos de movimiento
  - Apertura/Cierre de caja
  - Pagos de pacientes
  - Gastos varios
  - Entregas de turno
  - Recibos de turno
  - Retiros de efectivo

**Interfaz:**
- Vista Cash/Daily mejorada
  - Tabla con todos los movimientos del d�a
  - Filtros por fecha con bot�n "Hoy"
  - Indicadores visuales por tipo de movimiento
  - Traducci�n completa al espa�ol con iconos

**Balance:**
- C�lculo autom�tico en tiempo real
  - Balance te�rico vs. efectivo contado
  - Diferencias resaltadas en rojo
  - Trazabilidad por usuario

**Archivos A�adidos:**
- `app/Http/Controllers/CashController.php`
- `resources/views/cash/daily.blade.php`
- `app/Models/CashMovement.php`

**Archivos Modificados:**
- `resources/views/dashboard/dashboard.blade.php`
- `routes/web.php`

---

## [2.3.0] - 2025-10-12

### =� Reportes Profesionales

**A�adido:**
- Reporte de Pacientes a Atender
  - Listado diario para profesionales al llegar
  - Informaci�n de paciente, hora, monto y obra social
  - Vista previa web y versi�n impresi�n

- Reporte de Liquidaci�n Diaria
  - Comisiones calculadas por profesional
  - Diferenciaci�n de pagos anticipados vs. cobros del d�a
  - Subtotales y total general
  - Auto-cierre despu�s de imprimir

**Interfaz:**
- Accesos r�pidos desde Dashboard
- Selectores de fecha y profesional
- Dise�o optimizado para impresi�n A4

**Archivos A�adidos:**
- `app/Http/Controllers/ReportController.php`
- `resources/views/reports/daily-schedule.blade.php`
- `resources/views/reports/daily-schedule-print.blade.php`
- `resources/views/reports/professional-liquidation.blade.php`

---

## [2.2.0] - 2025-10-10

### =� Sistema Dual de Pagos

**A�adido:**
- Pagos individuales (single)
  - Un turno, un pago
  - Ingreso inmediato a caja
  - Asignaci�n autom�tica

- Paquetes de tratamiento (package)
  - M�ltiples sesiones, un pago grupal
  - Distribuci�n autom�tica entre turnos
  - Seguimiento de sesiones usadas

**Mejoras:**
- PaymentAllocationService
  - L�gica de asignaci�n centralizada
  - Manejo de prioridades (urgencias primero)
  - Validaciones de saldos

**Archivos A�adidos:**
- `app/Services/PaymentAllocationService.php`
- `app/Models/PaymentAppointment.php`

**Archivos Modificados:**
- `app/Models/Payment.php`
- `app/Http/Controllers/PaymentController.php`

---

## [2.1.0] - 2025-10-08

### =� Dashboard Moderno

**A�adido:**
- Vista en tiempo real del d�a actual
- M�tricas principales
  - Consultas del d�a (total, completadas, pendientes, ausentes)
  - Ingresos por m�todo de pago
  - Profesionales activos
- Listado de consultas con acciones r�pidas
  - Marcar como atendido
  - Finalizar y cobrar
  - Marcar ausente
- Resumen de caja por profesional

**Componentes:**
- Alpine.js para interactividad
- Modales de pago optimizados
- Sistema de notificaciones con SystemModal

**Archivos A�adidos:**
- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard/dashboard.blade.php`
- `resources/views/components/payment-modal.blade.php`
- `resources/views/components/system-modal.blade.php`

---

## [2.0.0] - 2025-10-05

### <� Versi�n Inicial Estable

**Core del Sistema:**
- Gesti�n completa de turnos m�dicos
- Administraci�n de profesionales y especialidades
- Registro de pacientes con historial
- Sistema de horarios y excepciones
- Liquidaciones profesionales b�sicas

**Tecnolog�as Base:**
- Laravel 12 con PHP 8.2
- MySQL para persistencia
- TailwindCSS 4.0 para UI
- Alpine.js para interactividad
- Vite para build moderno

**Arquitectura:**
- Modelos Eloquent con relaciones completas
- Migraciones versionadas
- Seeders para datos de prueba
- Scopes y accessors en modelos

---

## Tipos de Cambios

- **A�adido** - para nuevas funcionalidades
- **Cambiado** - para cambios en funcionalidad existente
- **Deprecado** - para funcionalidades que se eliminar�n
- **Eliminado** - para funcionalidades eliminadas
- **Corregido** - para correcci�n de bugs
- **Seguridad** - en caso de vulnerabilidades
