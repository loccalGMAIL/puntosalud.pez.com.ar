# 📝 Changelog - PuntoSalud

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [2.9.0] - 2026-02-27

### ✨ Mejoras en Agenda y Dashboard

#### Agenda — Timeline del día
- **Refactoring de vista:** `agenda/index.blade.php` (1395 líneas) descompuesto en 5 partials organizados por responsabilidad (`cash-alerts`, `calendar`, `day-modal`, `styles`, `scripts`). El archivo principal queda en 82 líneas como orquestador limpio.
- **Fix doble barra de scroll:** modal convertido a `flex column`; solo el timeline scrollea, header/action bar/legend/footer siempre visibles.
- **Tipografía de bloques:** aumentada de 11px a 14px para mejor legibilidad.
- **Colores de bloques:** fondos claros (bg-100) con tipografía oscura (text-900) en lugar de bg-500/text-white.
- **Estado "Ausente":** cambiado de naranja a gris para diferenciarlo visualmente de urgencia (rojo).
- **Icono de nota:** cuando un turno tiene nota, aparece un icono de chat ámbar con el texto completo como tooltip.
- **Opción 25 minutos** agregada al selector de duración de turnos.

#### Dashboard
- **Icono de nota:** mismo icono ámbar con tooltip en las listas de turnos del dashboard principal y la vista de turnos del día.

---

## [2.8.1] - 2026-02-27

### 🔐 Sistema de Perfiles de Acceso Modular

Reemplaza el sistema de roles fijos (admin/receptionist) por perfiles configurables, donde cada perfil define qué módulos tiene habilitados. El acceso se controla 100% desde la base de datos sin tocar código.

**Cambios principales:**

- **BD:** tablas `profiles` y `profile_modules` (pivot); columna `profile_id` en `users`; eliminada columna `role`
- **Modelo `Profile`:** constante `MODULES` con los 9 módulos del sistema; método `allowsModule()`
- **Modelo `User`:** nuevo método `canAccessModule(string)`; `isAdmin()` como alias de `canAccessModule('configuration')`
- **Middleware `module`:** restringe rutas por módulo (`middleware('module:cash')`)
- **Perfiles base:** "Administrador" (9 módulos) y "Acceso General" (7, sin configuración ni sistema)
- **CRUD de perfiles** en `/profiles` con checkboxes por módulo (Alpine.js)
- **Navegación:** menús "Configuración" y "Sistema" separados e independientes
- **Fix:** bug en migración `restructure_payments_table` al correr `migrate:fresh`

---

## [2.8.0] - 2026-02-20

### 🔍 Sistema de Registro de Actividades de Usuarios

**Descripción:**
Sistema de auditoría completo que registra todas las operaciones CRUD realizadas sobre las entidades del sistema, así como los eventos de login y logout. Accesible únicamente por administradores.

**Cambios Implementados:**

1. **Modelo `ActivityLog` + Tabla `activity_logs`:**
   - Campos: `user_id`, `action`, `subject_type`, `subject_id`, `subject_description`, `ip_address`, `created_at`
   - Log inmutable (`UPDATED_AT = null`)
   - Método estático `record()` como helper central con captura silenciosa de errores
   - Scope `filter()` para filtros por fecha, usuario, acción y módulo
   - Índices en `(user_id, created_at)` y `(subject_type, subject_id)`

2. **Trait `LogsActivity`:**
   - Escucha eventos Eloquent `created`, `updated`, `deleted` mediante `bootLogsActivity()`
   - Método `activityDescription()` sobreescribible por cada modelo
   - Aplicado a 15 modelos: Patient, Professional, Appointment, Payment, CashMovement, User, ProfessionalLiquidation, Package, PatientPackage, ProfessionalSchedule, ScheduleException, AppointmentSetting, Office, Specialty, MovementType

3. **Registro de Login/Logout:**
   - `AuthController::login()` registra acción `login` tras autenticación exitosa
   - `AuthController::logout()` registra acción `logout` antes de cerrar sesión

4. **Vista de Historial (`/activity-log`):**
   - Acceso exclusivo para administradores (middleware `can:viewAny,User`)
   - 4 tarjetas estadísticas: acciones hoy / esta semana / este mes / usuarios activos hoy
   - Filtros: rango de fechas, usuario, acción y módulo
   - Tabla responsiva: mobile (cards con `md:hidden`) + desktop (tabla con `hidden md:block`)
   - Columnas: Fecha/Hora | Usuario | Acción (badge de color) | Módulo | Descripción | IP
   - Paginación de 50 registros
   - Badges de color por acción: creó (verde), modificó (azul), eliminó (rojo), inició sesión (violeta), cerró sesión (gris)
   - Nombres de módulos en español

5. **Navegación:**
   - Nuevo ítem "Actividad" en el submenú de Configuración (visible solo para admins)

**Archivos Creados:**
- `database/migrations/2026_02_20_000000_create_activity_logs_table.php`
- `app/Models/ActivityLog.php`
- `app/Traits/LogsActivity.php`
- `app/Http/Controllers/ActivityLogController.php`
- `resources/views/activity-log/index.blade.php`
- `resources/views/activity-log/_action-badge.blade.php`

**Archivos Modificados:**
- `app/Models/Patient.php`, `Professional.php`, `Appointment.php`, `Payment.php`, `CashMovement.php`, `User.php`, `ProfessionalLiquidation.php`, `Package.php`, `PatientPackage.php`, `ProfessionalSchedule.php`, `ScheduleException.php`, `AppointmentSetting.php`, `Office.php`, `Specialty.php`, `MovementType.php` (trait + activityDescription)
- `app/Http/Controllers/AuthController.php` (login/logout logging)
- `routes/web.php` (nueva ruta admin)
- `resources/views/layouts/app.blade.php` (ítem de navegación)
- `composer.json` (versión 2.8.0)

### 📅 Mejoras en Agenda y Timeline de Día

**Descripción:**
Rediseño visual y funcional del timeline del Day Modal y de las celdas del calendario mensual.

**Cambios Implementados:**

1. **Timeline con posicionamiento absoluto preciso (`pxPerMin: 3`):**
   - Todos los elementos (turnos, slots libres, líneas de hora) usan coordenadas de tiempo puras, sin cursor secuencial
   - Grilla horaria y bloques de turno perfectamente alineados
   - Líneas de media hora en guiones sutiles; hora en negrita a la izquierda

2. **Turnos pasados: solo lectura:**
   - Los turnos anteriores a la hora/fecha actual se muestran con opacidad reducida y sin acción de edición

3. **Slots libres con bloques parciales:**
   - Se mantiene la grilla de 30 minutos pero si un turno ocupa menos de 30 min, aparece un bloque libre con el tiempo restante del slot
   - Los slots no se superponen con turnos existentes

4. **Prevención de solapamiento de turnos:**
   - Al crear/editar un turno, las duraciones que superarían el siguiente turno del mismo profesional quedan deshabilitadas en el selector
   - La duración se ajusta automáticamente al cambiar la hora si excede el límite disponible

5. **Celdas del calendario mensuales clickeables:**
   - Click en cualquier parte de la celda abre el Day Modal (solo días del mes actual con horario, no feriados)
   - Se eliminó el botón "+" de las celdas; el "Nuevo Turno" está dentro del Day Modal

**Archivos Modificados:**
- `resources/views/agenda/index.blade.php` (timeline, celdas del calendario, lógica Alpine)
- `resources/views/appointments/modal.blade.php` (selector de duración dinámico con `durationOptions`)

---

## [2.7.1] - 2026-02-10

### 🎨 Mejoras UI/UX: Toast Notifications, Validación Inline, Tablas Responsivas y Sidebar Móvil

**Descripción:**
- Reemplazo completo de `alert()` del navegador por un sistema de toast notifications moderno
- Validación inline en formularios modales con mensajes de error por campo
- Tablas responsivas con vista de cards en dispositivos móviles
- Corrección del sidebar/menú en modo móvil

**Cambios Implementados:**

1. **Toast Notifications (reemplazo de todos los `alert()`):**
   - Nuevo componente global `toast-notifications.blade.php` con Alpine.js Store
   - 4 tipos de notificación: success (4s), error (6s), warning (5s), info (4s)
   - Auto-dismiss configurable + cierre manual con botón X
   - Stack de toasts en esquina inferior derecha con animación slide-in
   - Función global `window.showToast()` para contextos fuera de Alpine
   - Soporte completo de dark mode
   - Reemplazo de `showNotification()` en 10 vistas principales
   - Reemplazo de `alert()` directos en 14 archivos adicionales

2. **Validación Inline en Formularios:**
   - Patrón `formErrors` + métodos `hasError()`, `clearError()`, `setErrors()`, `clearAllErrors()`
   - Bordes rojos y mensajes de error debajo de cada campo con validación fallida
   - Los errores se limpian al corregir el campo (`@input` / `@change`)
   - Los errores se resetean al abrir/cerrar modales
   - Implementado en: Pacientes, Profesionales, Turnos, Agenda

3. **Tablas Responsivas (cards móviles):**
   - Patrón dual: `hidden md:block` (tabla desktop) + `md:hidden` (cards móvil)
   - Cards con datos clave, badges de estado y botones de acción touch-friendly
   - Implementado en: Pacientes, Profesionales, Turnos, Cobros, Caja del Día

4. **Fix Sidebar Móvil:**
   - Corrección de `max-md:-translate-x-full` que impedía abrir el sidebar en móvil
   - Unificación de breakpoints de `lg` (1024px) a `md` (768px) para consistencia
   - Sidebar usa `:style` inline para evitar conflictos de especificidad CSS
   - CSS `sidebar-init` / `content-init` para estado correcto pre-Alpine (sin flash)
   - Nuevo botón X para cerrar el sidebar en móvil
   - Transiciones suaves en sidebar y margen del contenido

**Archivos Creados:**
- `resources/views/components/toast-notifications.blade.php`

**Archivos Modificados:**
- `resources/views/layouts/app.blade.php` (toast, sidebar móvil)
- `resources/views/patients/index.blade.php` (toast, validación, cards)
- `resources/views/patients/modal.blade.php` (validación inline)
- `resources/views/professionals/index.blade.php` (toast, validación, cards)
- `resources/views/professionals/modal.blade.php` (validación inline)
- `resources/views/appointments/index.blade.php` (toast, validación, cards)
- `resources/views/appointments/modal.blade.php` (validación inline)
- `resources/views/agenda/index.blade.php` (toast, validación)
- `resources/views/payments/index.blade.php` (toast, cards)
- `resources/views/payments/show.blade.php` (toast)
- `resources/views/payments/create.blade.php` (toast)
- `resources/views/payments/edit.blade.php` (toast)
- `resources/views/cash/daily.blade.php` (toast, cards)
- `resources/views/cash/manual-income-form.blade.php` (toast)
- `resources/views/cash/expense-form.blade.php` (toast)
- `resources/views/cash/withdrawal-form.blade.php` (toast)
- `resources/views/components/cash-close-modal.blade.php` (toast)
- `resources/views/reports/cash.blade.php` (toast)
- `resources/views/users/index.blade.php` (toast)
- `resources/views/users/profile.blade.php` (toast)
- `resources/views/professionals/schedules/index.blade.php` (toast)
- `resources/views/recesos/index.blade.php` (toast)
- `resources/views/dashboard/dashboard.blade.php` (toast)
- `resources/views/dashboard/dashboard-appointments.blade.php` (toast)

**Impacto:**
- ✅ Eliminados TODOS los `alert()` del navegador - notificaciones modernas no intrusivas
- ✅ Errores de validación visibles por campo sin perder el contexto del formulario
- ✅ Todas las tablas principales son usables en dispositivos móviles
- ✅ Menú lateral funciona correctamente en móvil con animaciones suaves
- ✅ Dark mode completo en todos los componentes nuevos

---

## [2.7.0] - 2026-02-09

### 📅 Sábados en Agenda y Horarios de Profesionales

**Descripción:**
- Habilitación del día Sábado en la vista de Agenda para visualizar y gestionar turnos
- Nuevo botón de acción rápida "Semana Completa" en la configuración de horarios de profesionales

**Cambios Implementados:**

1. **Agenda - Calendario con Sábados:**
   - Grid del calendario ampliado de 5 a 6 columnas (Lun-Sáb)
   - Los Sábados ahora se muestran en el calendario con la misma funcionalidad que los días de semana
   - Si el profesional tiene horario configurado para Sábado, se pueden crear turnos normalmente
   - Si no tiene horario, el día aparece en gris ("Día sin atención")

2. **Horarios de Profesionales - Acción Rápida "Semana Completa":**
   - Nuevo botón que configura Lun-Vie 9:00-17:00 + Sáb 8:00-15:00
   - Horario de Sábado ajustado al horario del centro (8:00 a 15:00)
   - Los botones existentes ("Horario de Oficina" y "Solo Mañanas") se mantienen sin cambios

**Archivos Modificados:**
- `resources/views/agenda/index.blade.php` (grid 6 columnas, inclusión de Sábado)
- `resources/views/professionals/schedules/index.blade.php` (nuevo botón y función setFullWeekSchedule)

**Impacto:**
- ✅ Profesionales pueden atender los Sábados con gestión completa de turnos
- ✅ Configuración rápida de horarios incluyendo Sábado
- ✅ Sin impacto en profesionales que no atienden Sábados (día se muestra gris)

---

## [2.6.3] - 2026-01-30

### 🗂️ Reorganización del Menú de Caja

**Descripción:**
- Mejora en la navegación del sistema de caja para acceso más intuitivo
- Acceso directo al Análisis de Caja desde el menú lateral

**Cambios en el Menú Lateral:**

| Ubicación | Anterior | Nuevo |
|-----------|----------|-------|
| Menú principal | Caja | **Caja del Día** |
| Submenú Reportes | Reporte de Caja | **Movimientos de Caja** |
| Submenú Reportes | (no existía) | **Análisis de Caja** (nuevo) |

**Estructura Final del Menú:**
```
├── Caja del Día → /cash/daily (operativa diaria)
└── Reportes
    ├── Movimientos de Caja → /reports/cash (movimientos de un día)
    └── Análisis de Caja → /cash/report (análisis por período con exportación)
```

**Archivos Modificados:**
- `resources/views/layouts/app.blade.php` (menú lateral)
- `resources/views/cash/daily.blade.php` (breadcrumb y título)
- `resources/views/cash/report.blade.php` (breadcrumb y título)
- `resources/views/reports/cash.blade.php` (breadcrumb, título y botón eliminado)

**Impacto:**
- ✅ Navegación más clara y directa
- ✅ Acceso rápido al Análisis de Caja sin pasar por otra vista
- ✅ Nombres más descriptivos para cada funcionalidad

---

### 📊 Exportación de Reportes de Caja (Excel y PDF)

**Descripción:**
- Nueva funcionalidad para exportar el reporte de caja en formatos Excel (CSV) y PDF
- Descarga directa sin pasos intermedios

**Características Implementadas:**

1. **Exportación Excel (CSV):**
   - Archivo CSV compatible con Excel (separador `;` y BOM UTF-8)
   - Incluye resumen, detalle por período y análisis por tipo de movimiento
   - Nombre de archivo descriptivo: `reporte-caja-FECHA-a-FECHA.csv`

2. **Exportación PDF:**
   - Generación directa de PDF usando `barryvdh/laravel-dompdf`
   - Diseño profesional con tablas y colores
   - Incluye encabezado, resumen, análisis por tipo y detalle por período
   - Nombre de archivo descriptivo: `reporte-caja-FECHA-a-FECHA.pdf`

**Archivos Creados:**
- `resources/views/cash/report-pdf.blade.php` (vista optimizada para PDF)

**Archivos Modificados:**
- `app/Http/Controllers/CashController.php` (métodos `exportCashReportCsv` y `downloadCashReportPdf`)
- `routes/web.php` (rutas `cash.report.export` y `cash.report.pdf`)
- `resources/views/cash/report.blade.php` (botones Excel y PDF funcionales)
- `composer.json` (nuevo paquete `barryvdh/laravel-dompdf`)

**Impacto:**
- ✅ Exportación rápida a Excel para análisis en hojas de cálculo
- ✅ Generación de PDF profesional para archivo o impresión
- ✅ Ambos formatos respetan los filtros seleccionados (fechas y agrupación)

---

### 🖨️ Impresión de Movimientos de Caja

**Descripción:**
- Nueva funcionalidad para imprimir la tabla de movimientos de caja del día
- Botón "Imprimir Movimientos" disponible en la vista de reportes de caja

### 🐛 Corrección Reporte de Caja por Rango de Fechas

**Problema Corregido:**
- El reporte por rango (`/cash/report`) incluía incorrectamente los movimientos de apertura y cierre de caja en los totales
- Esto causaba inconsistencias: la suma de reportes diarios no coincidía con el reporte por rango

**Solución Implementada:**
- Filtrado de movimientos `cash_opening` y `cash_closing` en el método `cashReport()`, consistente con `dailyCash()` y `dailyReport()`

### ✨ Mejora en Cards de Análisis por Tipo de Movimiento

**Descripción:**
- Las cards de "Análisis por Tipo de Movimiento" ahora obtienen nombres e iconos desde la base de datos
- Eliminado switch hardcodeado de ~40 líneas por código dinámico
- Cada card muestra solo Ingresos o Egresos según corresponda (sin mostrar ambos ni Neto)

**Archivos Modificados:**
- `app/Http/Controllers/CashController.php` (método `cashReport()`)
- `resources/views/cash/report.blade.php`

**Características Implementadas:**

1. **Nueva Vista de Impresión:**
   - Vista dedicada `reports/cash-movements-print.blade.php`
   - Formato limpio y optimizado para impresión
   - Incluye resumen rápido (saldo inicial, ingresos, egresos, saldo final)
   - Tabla completa de movimientos con todos los datos
   - Totales al pie de la tabla

2. **Botón de Impresión:**
   - Botón "Imprimir Movimientos" siempre visible en `/reports/cash`
   - Color verde (emerald) para diferenciarlo del botón de cierre
   - Abre vista de impresión en nueva pestaña

3. **Cierre Automático:**
   - La pestaña de impresión se cierra automáticamente después de imprimir
   - Usa evento `afterprint` con fallback de 3 segundos

**Archivos Creados:**
- `resources/views/reports/cash-movements-print.blade.php`

**Archivos Modificados:**
- `app/Http/Controllers/ReportController.php` (nuevo método `cashMovementsPrint`)
- `routes/web.php` (nueva ruta `reports.cash.print`)
- `resources/views/reports/cash.blade.php` (botón agregado)

**Impacto:**
- ✅ Impresión rápida de movimientos del día
- ✅ Disponible sin necesidad de cerrar la caja
- ✅ Experiencia de usuario mejorada con cierre automático

---

## [2.6.2-hotfix-4] - 2026-01-21

### 🖨️ Impresión Individual de Liquidaciones Parciales

**Descripción:**
- Nueva funcionalidad para imprimir cada liquidación parcial por separado
- Resuelve confusión cuando hay múltiples liquidaciones en el día

**Características Implementadas:**

1. **Icono de Impresión en Vista de Detalle:**
   - Agregado icono de impresora en cada card de "Liquidación #1", "#2", etc.
   - Ubicado junto al título sin romper el diseño
   - Abre nueva pestaña con impresión de solo esa liquidación

2. **Icono de Impresión en Vista de Selección:**
   - Agregado icono de impresora en la lista de liquidaciones realizadas
   - Permite imprimir directamente desde el panel general sin entrar al detalle

3. **Vista de Impresión Adaptada:**
   - Título específico: "LIQUIDACIÓN #X DEL PROFESIONAL"
   - Resumen simplificado mostrando solo el monto de esa liquidación
   - Muestra únicamente los turnos correspondientes a esa liquidación
   - Oculta secciones no relevantes (turnos previos, pendientes, sin pagar)
   - Footer actualizado con número de liquidación

4. **Corrección de Totales con Pagos Múltiples:**
   - Los totales de Efectivo/Digital ahora consideran correctamente pagos mixtos
   - Antes: pagos múltiples se sumaban todo en "Digital"
   - Ahora: separa correctamente efectivo de métodos digitales usando `payment_methods_array`

**Archivos Modificados:**
- `resources/views/reports/professional-liquidation.blade.php` (líneas 178-196)
- `resources/views/reports/professional-liquidation-select.blade.php` (líneas 187-210)
- `resources/views/reports/professional-liquidation-print.blade.php` (múltiples secciones)
- `app/Http/Controllers/ReportController.php` (líneas 604-618)

**Impacto:**
- ✅ Entrega de liquidaciones parciales sin confusión
- ✅ Documento limpio con solo la información de esa liquidación
- ✅ Totales precisos en pagos mixtos (efectivo + digital)
- ✅ Acceso rápido desde vista de selección y detalle

---

## [2.6.2-hotfix-3] - 2026-01-21

### 🔄 Liquidaciones Parciales Durante el Día

**Descripción:**
- Permite liquidar profesionales aunque tengan turnos pendientes (scheduled)
- Habilita múltiples liquidaciones durante el día de trabajo

**Problema Anterior:**
- No se podía liquidar si el profesional tenía turnos programados sin atender
- Obligaba a esperar al final del día para liquidar
- Poco flexible para profesionales que querían cobrar parcialmente

**Solución Implementada:**
- Removida validación que bloqueaba liquidación con turnos `scheduled`
- Mantenida validación crítica: no liquidar con turnos `attended` sin cobrar
- Mantenida validación de cierre: caja no cierra con `payment_details` sin liquidar

```php
// REMOVIDO - Ya no bloquea liquidaciones parciales:
// if ($pendingAppointments > 0) { throw new \Exception(...) }

// MANTENIDO - Sigue validando turnos atendidos sin cobrar:
if ($unpaidAppointments > 0) { throw new \Exception(...) }
```

**Archivos Modificados:**
- `app/Http/Controllers/LiquidationController.php` (líneas 42-51 removidas, comentario agregado)

**Impacto:**
- ✅ Mayor flexibilidad operativa
- ✅ Liquidar varias veces al día según necesidad
- ✅ Profesionales cobran más rápido
- ✅ Control contable intacto (cierre sigue validando)

---

## [2.6.2-hotfix] - 2026-01-09

### 🐛 Correcciones Críticas de Producción

#### Fix 1: Error en Cierre de Caja - Relación paymentAppointment

**Descripción del Problema:**
- Al cerrar la caja se producía error: "Call to undefined method App\Models\PaymentDetail::paymentAppointment()"
- El sistema impedía completar el cierre de caja
- Error introducido en commit 5fb3d23 durante implementación de liquidaciones múltiples

**Causa Raíz:**
- En `CashController.php` línea 477 se usaba `paymentAppointment.appointment` (singular)
- El modelo `PaymentDetail` no tiene relación `paymentAppointment()`
- La relación correcta es `payment.paymentAppointments.appointment` (plural, a través de Payment)

**Solución Implementada:**
```php
// Antes (incorrecto):
$hasPendingPayments = PaymentDetail::whereHas('paymentAppointment.appointment', ...)

// Después (correcto):
$hasPendingPayments = PaymentDetail::whereHas('payment.paymentAppointments.appointment', ...)
```

**Archivos Modificados:**
- `app/Http/Controllers/CashController.php` (línea 477)

**Impacto:**
- ✅ Cierre de caja funciona correctamente
- ✅ Validación de liquidaciones pendientes operativa
- ✅ Sistema permite flujo completo de cierre de día

---

#### Fix 2: Componente Reutilizable de Modal de Cierre de Caja

**Descripción del Problema:**
- Al cerrar caja de días anteriores desde Dashboard, se mostraba modal básico
- Modal del Dashboard solo pedía monto y notas (sin información de contexto)
- Modal de Cash/Daily era superior: mostraba resumen, alertas de diferencia, pre-llenaba datos
- Inconsistencia UX entre ambas vistas

**Solución Implementada:**

1. **Nuevo Componente Blade Reutilizable:**
   - Creado `resources/views/components/cash-close-modal.blade.php`
   - Acepta props: `theoreticalBalance`, `incomeTotal`, `expenseTotal`, `closeDate`, `isUnclosedDate`
   - Incluye toda la lógica Alpine.js y estilos
   - Modal completo con:
     - Resumen del día (saldo teórico, ingresos, egresos)
     - Pre-llenado de monto con saldo teórico
     - Alertas en tiempo real de diferencias (sobrante/faltante)
     - Título dinámico según sea día actual o sin cerrar
     - Validación y confirmación con diálogos informativos

2. **DashboardController Mejorado:**
   - Calcula resumen completo para días sin cerrar (`unclosed_summary`)
   - Obtiene movimientos del día, balance teórico, ingresos/egresos
   - Pasa datos estructurados a la vista

3. **Vistas Actualizadas:**
   - `dashboard.blade.php`: Reemplazado modal básico por componente
   - `cash/daily.blade.php`: Reemplazado modal por componente
   - JavaScript simplificado: solo dispara evento `close-cash-modal`

**Archivos Modificados:**
- `resources/views/components/cash-close-modal.blade.php` (nuevo)
- `app/Http/Controllers/DashboardController.php` (líneas 37-74)
- `resources/views/dashboard/dashboard.blade.php` (líneas 115-124)
- `resources/views/cash/daily.blade.php` (líneas 344-351)

**Impacto:**
- ✅ Consistencia UI/UX entre Dashboard y Cash Daily
- ✅ Mejor experiencia: información completa en ambas vistas
- ✅ Código DRY: un solo componente para ambos casos
- ✅ Mantenimiento simplificado

---

#### Fix 3: Profesionales con Liquidación $0 y Gastos en Lista de Pagos

**Problema 1: Profesionales No Aparecían en Liquidaciones**
- Profesionales con comisión 0% no aparecían en lista de liquidaciones pendientes
- Profesionales con reintegros que igualaban comisión tampoco aparecían
- Sistema no permitía cerrar caja pero no mostraba quién faltaba liquidar
- **Caso específico**: Dra. Zalazar con tratamiento especial de comisión 0%

**Causa Raíz:**
- Filtro en `ReportController.php` línea 343 excluía profesionales con `professional_amount = $0`
- Comentario incorrecto: "Si el monto es $0, significa que ya fue liquidado completamente"
- En realidad, monto $0 puede deberse a:
  - Comisión 0%
  - Pagos directos que igualan comisión
  - Reintegros que reducen monto neto a $0

**Solución:**
```php
// Agregado campo has_pending_payments
'has_pending_payments' => $centroPaymentDetails->count() > 0 || $professionalPaymentDetails->count() > 0

// Filtro corregido
return $professional['attended_count'] > 0 && $professional['has_pending_payments'];
```

**Problema 2: Gastos Aparecían en Lista de Pagos**
- En sección de Payments (`/payments`) se mostraban movimientos tipo `expense` (gastos)
- Los gastos no generan número de recibo
- No deberían aparecer en lista de ingresos

**Solución:**
```php
// Filtrar consulta principal
$query = Payment::with([...])
    ->where('payment_type', '!=', 'expense');

// Actualizar estadísticas
$stats = [
    'total' => Payment::where('payment_type', '!=', 'expense')->count(),
    // ... resto de stats
];
```

**Archivos Modificados:**
- `app/Http/Controllers/ReportController.php` (líneas 327-346)
- `app/Http/Controllers/PaymentController.php` (líneas 29-30, 71-89)

**Impacto:**
- ✅ Profesionales con liquidación $0 aparecen correctamente
- ✅ Sistema permite completar todas las liquidaciones antes de cerrar caja
- ✅ Coherencia entre validación de cierre y lista de pendientes
- ✅ Lista de pagos limpia, solo muestra ingresos válidos
- ✅ Estadísticas precisas sin incluir gastos

---

## [2.6.1] - 2026-01-05

### 🎂 Nuevo - Sistema de Cumpleaños de Profesionales

**Descripción:**
- Sistema completo de registro y visualización de cumpleaños de profesionales
- Visualización automática en el calendario de agenda
- Cálculo automático de edad en formularios y agenda

**Características Implementadas:**

1. **Campo de Fecha de Nacimiento en Profesionales:**
   - Nuevo campo `birthday` en tabla `professionals`
   - Input type="date" con validación (debe ser anterior a hoy)
   - Límite automático de fecha máxima (hoy)
   - Cálculo automático de edad al seleccionar fecha
   - Muestra edad en tiempo real debajo del campo (ej: "45 años")

2. **Visualización en Agenda:**
   - Icono 🎂 en días donde algún profesional cumple años
   - Visible en todo el calendario, independiente del profesional seleccionado
   - Tooltip informativo al pasar el mouse
   - Muestra nombre completo y edad que cumple (ej: "🎉 Cumpleaños: Dr. Juan Pérez (45 años)")
   - Soporte para múltiples cumpleaños en el mismo día

3. **Cálculo de Edad:**
   - En formulario: Actualización automática al seleccionar/cambiar fecha
   - En agenda: Calcula edad que cumple considerando el año del calendario
   - Considera correctamente mes y día para cálculo preciso

**Archivos Modificados:**
- `app/Models/Professional.php` - Agregado campo `birthday` con cast `date:Y-m-d`
- `app/Http/Controllers/ProfessionalController.php` - Validación del campo birthday
- `app/Http/Controllers/AgendaController.php` - Lógica de cálculo de cumpleaños
- `resources/views/professionals/modal.blade.php` - Campo de fecha con cálculo de edad
- `resources/views/professionals/index.blade.php` - Funciones calculateAge() y getMaxDate()
- `resources/views/agenda/index.blade.php` - Visualización de cumpleaños con icono

**Validaciones:**
- Campo `birthday`: `nullable|date|before:today`
- Mensaje de error: "La fecha de nacimiento debe ser anterior a hoy"

**Impacto:**
- ✅ Registro completo de datos de profesionales
- ✅ Recordatorio visual de cumpleaños en agenda
- ✅ Mejora la gestión de recursos humanos
- ✅ UX mejorada con cálculo automático de edad
- ✅ Tooltip informativo sin saturar la interfaz

### 🔄 Mejora - Orden de Visualización de Nombres de Pacientes

**Descripción:**
- Cambio en el orden de visualización de nombres de pacientes en todo el sistema
- Ahora se muestra: **Apellido, Nombre** (formato estándar)

**Implementación:**
- Modificado el orden de concatenación en vistas y listados
- Formato anterior: "Juan Pérez"
- Formato nuevo: "Pérez, Juan"

**Archivos Modificados:**
- `resources/views/patients/index.blade.php` - Vista principal de listado de pacientes

**Impacto:**
- ✅ Mejor organización alfabética por apellido
- ✅ Formato estándar profesional para listados médicos
- ✅ Facilita búsqueda y lectura de registros
- ✅ Consistencia con prácticas de gestión clínica

### 🔧 Mejora - Cierre Automático de Caja Fuera de Horario

**Descripción del Problema:**
- Cuando se cierra la caja después de las 23:59 del día de apertura (ej: fines de semana, feriados)
- El movimiento de cierre se registraba con la fecha/hora actual del servidor (día siguiente)
- Generaba saldos negativos en la caja anterior y estado incorrecto
- **Solución manual anterior:** Modificar manualmente la fecha en BD a las 23:59 del día de apertura

**Causa Raíz:**
- El campo `created_at` se generaba automáticamente con la hora actual del servidor
- Las búsquedas con `whereDate('created_at')` no encontraban apertura y cierre juntos
- El sistema consideraba que eran días diferentes

**Solución Implementada:**

1. **Búsqueda Inteligente de Apertura:**
   - Busca la última apertura sin cierre correspondiente (independiente de la fecha)
   - No depende de `close_date` del frontend
   - Query optimizado con `whereNotExists` para verificar ausencia de cierre

2. **Ajuste Automático de Fecha:**
   - Fuerza `created_at` a las 23:59:59 del día de apertura
   - Mantiene `updated_at` con la hora real del cierre (auditoría)
   - Deshabilita timestamps temporalmente para control preciso

3. **Descripción Mejorada con Auditoría:**
   - Nuevo método `buildClosingDescription()`
   - Incluye nota automática cuando se cierra en día diferente
   - Formato: "Cierre de caja del día 10/01/2026 (cerrado el 13/01/2026 00:15)"

**Archivos Modificados:**
- `app/Http/Controllers/CashController.php` (método `closeCash()`, líneas 432-523)
  - Búsqueda de apertura sin cierre (líneas 432-455)
  - Ajuste de fecha a 23:59:59 (líneas 505-523)
  - Método helper `buildClosingDescription()` (líneas 1376-1392)

**Ejemplo de Funcionamiento:**
```
Apertura: Viernes 10/01/2026 08:00
Cierre real: Lunes 13/01/2026 00:15

Registro en BD:
- created_at: 2026-01-10 23:59:59
- updated_at: 2026-01-13 00:15:30
- description: "Cierre de caja del día 10/01/2026 - Efectivo contado: $5,000.00
               - Saldo retirado: $5,000.00 (cerrado el 13/01/2026 00:15)"
```

**Impacto:**
- ✅ No más correcciones manuales en base de datos
- ✅ Balance correcto en reportes diarios
- ✅ Estado preciso de caja (abierta/cerrada)
- ✅ Auditoría completa con hora real de cierre
- ✅ Transparencia con nota de cuándo se cerró realmente
- ✅ Previene negativos en caja anterior

---

## [2.6.0-fix] - 2024-12-15

### 🐛 Corregido - Categorización de Pagos Múltiples en Liquidaciones

**Descripción del Problema:**
- Los pagos múltiples aparecían completamente en la columna "Digital" de la liquidación impresa
- Incluso cuando TODOS los payment_details eran efectivo, el monto completo se mostraba en "Digital"
- **Caso reportado**: Recibo 2025120193 (12/12) - pago múltiple con 2 payment_details en efectivo

**Causa Raíz:**
- En `professional-liquidation-print.blade.php`, cuando un pago tenía múltiples payment_details:
  - Se asignaba `payment_method = 'multiple'` como marcador
  - La condición `$isCash = ($appointment['payment_method'] === 'cash')` evaluaba a `false`
  - Todo el monto se asignaba a `$otherAmount` (columna "Digital")
- El código no evaluaba el método de cada payment_detail individual

**Solución Implementada:**
- Modificar la lógica para evaluar cada `payment_detail` de forma individual en pagos múltiples
- Sumar montos con `method='cash'` → columna **Efectivo**
- Sumar montos con otros métodos (transfer, debit_card, credit_card) → columna **Digital**
- Aplicado en ambas secciones:
  - Turnos Pagados Previamente (`prepaid_appointments`)
  - Turnos Cobrados Hoy (`today_paid_appointments`)

**Archivos Modificados:**
- `resources/views/reports/professional-liquidation-print.blade.php` (líneas 341-357, 453-469)

**Impacto:**
- ✅ Pagos múltiples ahora se categorizan correctamente en columnas Efectivo/Digital
- ✅ El recibo 2025120193 ahora muestra los montos en la columna correcta
- ✅ Liquidaciones reflejan correctamente el flujo de efectivo vs. pagos digitales
- ✅ Cálculos de totales por método de pago son precisos

---

## [2.6.0-hotfix] - 2025-11-27

### 🕐 Corregido - Problema Crítico de Zona Horaria en Vista Agenda

**Descripción del Problema:**
- El modal de turnos diarios mostraba fecha incorrecta (día anterior)
- Botón "Nuevo Turno" deshabilitado incorrectamente para días actuales
- Causado por conversión automática a UTC en funciones JavaScript de fecha
- **Impacto**: Los usuarios NO podían crear turnos desde la vista Agenda

**Causa Raíz:**
- Uso de `new Date().toISOString().split('T')[0]` que convierte a UTC
- Argentina (UTC-3): Antes de las 3 AM, la fecha resultante era del día anterior
- Funciones `formatDateSpanish()` e `isDayInPast()` también afectadas

**Solución Implementada:**

1. **Nueva función helper `getTodayDate()`** (líneas 583-589):
   ```javascript
   getTodayDate() {
       const now = new Date();
       const year = now.getFullYear();
       const month = String(now.getMonth() + 1).padStart(2, '0');
       const day = String(now.getDate()).padStart(2, '0');
       return `${year}-${month}-${day}`;
   }
   ```

2. **Función `resetForm()` corregida** (línea 563):
   - Antes: `appointment_date: new Date().toISOString().split('T')[0]`
   - Ahora: `appointment_date: this.getTodayDate()`

3. **Función `isDayInPast()` simplificada** (líneas 759-764):
   - Comparación directa de strings de fecha para evitar timezone
   - Usa `getTodayDate()` para obtener fecha actual correcta

4. **Función `formatDateSpanish()` corregida** (líneas 721-730):
   - Parse como fecha local: `new Date(year, month-1, day)`
   - Evita interpretación UTC de strings de fecha

**Archivos Modificados:**
- `resources/views/agenda/index.blade.php` (líneas 563, 583-589, 721-730, 759-764)

**Impacto:**
- ✅ Modal de agenda muestra fecha correcta en el título
- ✅ Botón "Nuevo Turno" se habilita/deshabilita correctamente
- ✅ Usuarios pueden crear turnos sin confusión de fechas
- ✅ Fix crítico que desbloqueó operación normal del sistema

---

## [2.6.0-fix] - 2025-11-19

### 🐛 Correcciones y Mejoras Post-Lanzamiento v2.6.0

**Liquidaciones Negativas:**
- **Agregado**: Soporte para liquidar profesionales con saldo negativo (profesional debe al centro)
  - Profesionales que reciben pagos directos pueden tener liquidaciones negativas
  - Botón "Liquidar" ahora visible independientemente del signo del monto
  - NO se crea movimiento de caja cuando el monto es negativo
  - Los payment_details SÍ se marcan como liquidados en todos los casos
  - Permite cerrar caja sin bloqueos por liquidaciones pendientes
- **Modificado**: `LiquidationController.php`
  - Validación: quitar `min:0` para permitir montos negativos
  - Condición: NO crear CashMovement si `net_professional_amount < 0`
- **Modificado**: `professional-liquidation.blade.php` y `professional-liquidation-select.blade.php`
  - Mostrar botón "Liquidar" cuando hay turnos atendidos (antes solo si monto > 0)

**Movimientos de Caja - Corrección Crítica:**
- **Corregido**: DashboardController y AppointmentController registraban en caja pagos que no ingresaban físicamente
  - **Problema**: Pagos directos a profesionales (`received_by='profesional'`) se contaban en caja del centro
  - **Resultado**: Caja del sistema tenía más dinero del real, no coincidía con arqueo físico
- **Modificado**: `DashboardController.createCashMovement()` (líneas 477-523)
  - Filtra `payment_details` por `received_by='centro'` antes de crear movimientos
  - Crea UN movimiento por cada payment_detail (no uno solo por el total)
  - Solo registra dinero que realmente ingresa al centro
- **Modificado**: `AppointmentController.createCashMovement()` (líneas 688-734)
  - Misma lógica que DashboardController
  - Filtra por `received_by='centro'`
- **Modificado**: `AppointmentController.determineReceivedBy()` (líneas 844-870)
  - Hecho explícito que QR siempre va al centro
  - Documentación mejorada de la lógica de routing

**Recibos con Pagos Mixtos:**
- **Corregido**: `receipts/print.blade.php` no soportaba múltiples payment_details
  - **Problema**: Intentaba acceder a `$payment->payment_method` (campo legacy que no existe en v2.6.0)
  - **Error**: No mostraba método de pago en recibos
- **Modificado**: Vista de recibo ahora lee de `paymentDetails` (líneas 341-381)
  - Si hay UN método: muestra el método directamente
  - Si hay MÚLTIPLES métodos: muestra "Mixto" + desglose con monto de cada uno
  - Ejemplo: `💵 Efectivo $15.000 | 💳 Débito $10.000`

**Otras Correcciones:**
- **Corregido**: Error "Attempt to read property 'full_name' on null" en payments/index
  - Vista intentaba acceder a `$payment->patient` sin verificar si existe
  - Agregada validación `@if($payment->patient)` antes de acceder a propiedades
- **Agregado**: Botón "Reimprimir Recibo" en payments/show
  - Permite reimprimir recibos desde el detalle de cualquier pago
  - Se abre en nueva ventana para facilitar impresión

**Archivos Modificados:**
- `app/Http/Controllers/LiquidationController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/AppointmentController.php`
- `resources/views/reports/professional-liquidation.blade.php`
- `resources/views/reports/professional-liquidation-select.blade.php`
- `resources/views/receipts/print.blade.php`
- `resources/views/payments/index.blade.php`
- `resources/views/payments/show.blade.php`

**Impacto:**
- ✅ Caja del sistema ahora coincide con arqueo físico
- ✅ Liquidaciones negativas se procesan correctamente
- ✅ Recibos muestran correctamente pagos mixtos
- ✅ No más errores por pacientes null
- ✅ Facilita reimpresión de recibos

---

## [2.6.0] - 2025-11-18

### 🚀 Reestructuración Mayor del Sistema de Pagos

**⚠️ BREAKING CHANGE**: Esta versión introduce cambios estructurales importantes en la base de datos que requieren migración de datos.

**Nuevo Sistema de Payment Details:**
- **Nueva tabla `payment_details`** para soportar pagos con múltiples métodos
- **Nueva tabla `patient_packages`** para gestión de paquetes de sesiones
- **Nueva tabla `packages`** para definición de paquetes pre-configurados
- **Reestructuración de tabla `payments`** para soporte de pagos mixtos e ingresos manuales

**Comando de Migración Automático:**
```bash
php artisan migrate:v2.6.0
```
- Migra automáticamente todos los datos de payments a la nueva estructura
- Crea payment_details desde payment_method legacy
- Migra packages a patient_packages
- Valida integridad de datos post-migración
- Soporte para rollback con `--rollback`
- Modo forzado con `--force` para scripts automatizados

**Agregado:**
- **PaymentDetail Model & Migration**
  - `payment_id`: FK a payments
  - `payment_method`: cash, transfer, debit_card, credit_card, qr, other
  - `amount`: monto de este método específico
  - `received_by`: 'centro' o 'profesional' (tracking de quién recibe el dinero)
  - `reference`: referencia opcional (número de transferencia, comprobante, etc.)
  - Soporte para pagos mixtos (ej: $5000 efectivo + $3000 transferencia)

- **PatientPackage Model & Migration**
  - Separación de paquetes de pacientes de la tabla payments
  - `patient_id`, `package_id`, `payment_id`
  - `sessions_included`, `sessions_used`, `sessions_remaining` (computed)
  - `purchase_date`, `expires_at`
  - `status`: active, expired, completed
  - Tracking completo de uso de sesiones

- **Package Model & Migration**
  - Plantillas de paquetes pre-configurados
  - `name`, `description`, `sessions`, `price`
  - `validity_days`: duración del paquete
  - Permite crear paquetes estándar (ej: "Paquete 10 sesiones")

- **Professional: Campo `receives_transfers_directly`**
  - Nuevo campo boolean en professionals table
  - Indica si el profesional cobra transferencias directamente
  - Afecta cálculo de liquidaciones en reportes de caja
  - UI: Checkbox en formulario de edición de profesionales

- **Payment Model: Accessors de compatibilidad**
  - `entry_type`: 'payment' o 'income' (determina si es pago de paciente o ingreso manual)
  - `payment_method`: obtiene método desde payment_details (compatibilidad con vistas legacy)
  - `amount`: alias para total_amount

**Modificado:**
- **Payments Table Structure**
  - `patient_id` ahora nullable (para ingresos manuales)
  - `payment_type`: single, package_purchase, refund, manual_income
  - `total_amount` reemplaza a `amount` (es la suma de payment_details)
  - `is_advance_payment`: boolean para pagos anticipados
  - `status`: pending, confirmed, cancelled
  - `liquidation_status`: pending, liquidated, cancelled, not_applicable
  - `income_category`: código de MovementType para ingresos manuales

- **CashController: Cálculo de comisión Dra. Zalazar**
  - Aplicación correcta de `commission_percentage` en reportes de caja
  - Diferenciación entre total facturado vs comisión del profesional
  - Líneas 684, 924: `$amount * ($commission_percentage / 100)`

- **Daily Report View: Mejora en presentación Dra. Zalazar**
  - Cambio de "Liquidación" a "Facturación de Pacientes" (más claro)
  - Muestra cantidad de consultas junto al desglose de métodos
  - Validación mejorada de existencia de datos antes de renderizar

**Migración de Datos (migrate:v2.6.0):**
1. Renombra `payments` a `payments_old`
2. Crea nueva tabla `payments` con estructura v2.6.0
3. Migra registros de payments_old → payments
4. Crea `payment_details` para cada pago (basado en payment_method legacy)
5. Determina `received_by`: 'profesional' si es transferencia con patient_id, sino 'centro'
6. Crea `patient_packages` para pagos tipo 'package'
7. Actualiza foreign keys en payment_appointments y liquidation_details
8. Valida integridad: conteos, montos, referencias

**Validaciones Post-Migración:**
- ✅ Mismo número de pagos en old vs new
- ✅ Todos los pagos tienen payment_details
- ✅ Montos de payments coinciden con suma de payment_details
- ✅ Paquetes migrados correctamente
- ✅ No existen payment_appointments o liquidation_details huérfanos

**Archivos Modificados:**
- `app/Models/Payment.php` - Nuevos accessors y relaciones
- `app/Models/PaymentDetail.php` - Nuevo modelo
- `app/Models/PatientPackage.php` - Nuevo modelo
- `app/Models/Package.php` - Nuevo modelo
- `app/Http/Controllers/CashController.php` - Cálculo de comisión
- `app/Console/Commands/MigrateToV260.php` - Comando de migración
- `database/migrations/2025_11_07_100000_restructure_payments_table.php`
- `database/migrations/2025_11_07_100001_create_payment_details_table.php`
- `database/migrations/2025_11_07_100002_create_packages_table.php`
- `database/migrations/2025_11_07_100003_create_patient_packages_table.php`
- `database/migrations/2025_11_07_100004_migrate_existing_payment_data.php`
- `database/migrations/2025_11_12_100000_add_payment_preferences_to_professionals_table.php`
- `resources/views/cash/daily-report.blade.php` - Mejoras UI
- `resources/views/professionals/index.blade.php` - Campo receives_transfers_directly

**Impacto:**
- ✅ Sistema preparado para pagos mixtos (múltiples métodos en un solo pago)
- ✅ Tracking preciso de quién recibe cada pago (centro vs profesional)
- ✅ Liquidaciones más precisas según configuración de cada profesional
- ✅ Base sólida para futuras funcionalidades (pagos parciales, adelantos, etc.)
- ✅ Migración automática preserva todos los datos históricos
- ⚠️ Requiere backup antes de migrar (recomendado)
- ⚠️ Migración puede tardar varios minutos en bases de datos grandes

**Instrucciones de Actualización:**
```bash
# 1. Hacer backup de la base de datos
mysqldump -u usuario -p database > backup_pre_v2.6.0.sql

# 2. Actualizar código
git pull origin v2.6.0

# 3. Ejecutar migración (con confirmación)
php artisan migrate:v2.6.0

# 4. Verificar logs
tail -f storage/logs/laravel.log

# 5. En caso de problemas, rollback
php artisan migrate:v2.6.0 --rollback
```

---

## [2.5.11] - 2025-11-04

### 🐛 Fixes

**Corregido:**
- **Método de pago QR agregado al sistema**
  - Agregado 'qr' al enum de payment_method en todas las tablas relevantes
  - Actualizadas validaciones en controladores (CashController, DashboardController, PaymentController)
  - Agregados match statements para mostrar 'QR' en reportes de liquidación
  - Agregada opción QR en todos los formularios de pago (📱 QR)
  - Actualizado recibo de pago (receipts/print.blade.php) para mostrar método QR
  - Actualizado recibo de ingreso (receipts/income-print.blade.php) para mostrar método QR
  - Ahora el método de pago QR aparece correctamente en impresiones de recibos

- **Error 422 al crear entreturno en Agenda**
  - Inicializado campo `is_between_turn` en `resetForm()` para evitar undefined
  - Inicializados todos los campos de pago (pay_now, payment_type, etc.) para consistencia
  - Conversión automática de booleanos a enteros (0/1) en FormData antes de enviar
  - Agregado `is_between_turn` en `openEditModal()` al cargar turno existente
  - Ahora funciona correctamente crear entreturno con checkbox activo

- **Búsqueda con acentos en Select2 (pacientes)**
  - Implementada función `normalizeText()` usando `normalize('NFD')` para quitar acentos
  - Aplicada normalización a término de búsqueda y todos los campos (text, dni, firstName, lastName)
  - Ahora buscar "Jose" encuentra "José", "Gomez" encuentra "Gómez", "Maria" encuentra "María", etc.
  - Búsqueda case-insensitive y accent-insensitive

- **Creación rápida de pacientes desde modal de turnos**
  - Agregado botón "+" estético (42x42px, emerald-600) al lado del select de pacientes
  - Modal de nuevo paciente se abre sin salir del flujo de creación de turno
  - Componente Alpine.js `patientModal()` para manejar creación desde agenda
  - PatientController devuelve paciente creado en respuesta JSON
  - Uso de sessionStorage para seleccionar automáticamente paciente después de recargar
  - Recarga automática de página con nuevo paciente preseleccionado

### 📋 Arqueo de Caja - Reporte Informativo sin Cierre

**Agregado:**
- **Funcionalidad de Arqueo de Caja**
  - Nuevo botón "Arqueo de Caja" en vista Cash/Daily
  - Genera reporte informativo sin cerrar la caja
  - Permite verificar efectivo antes de retirarse sin afectar operaciones
  - Muestra el estado actual de caja en tiempo real
  - Solo disponible cuando la caja está abierta

- **Nuevo método CashController::cashCount()**
  - Genera reporte de arqueo usando fecha actual
  - Calcula todos los totales financieros (ingresos, egresos, balance)
  - Muestra movimientos agrupados por tipo
  - Incluye liquidaciones profesionales y pagos de Dra. Zalazar
  - No registra cierre de caja (`is_closed = false`)
  - No requiere conteo manual de efectivo

- **Nueva vista count-report.blade.php**
  - Basada en estructura de daily-report pero sin cierre
  - Box informativo azul explicando que es un arqueo
  - Texto: "Este es un arqueo de caja - La caja permanece abierta"
  - Mantiene todas las secciones financieras del reporte de cierre
  - Optimizada para impresión A4
  - Auto-print con parámetro `?print=true`

- **Nueva ruta /cash/count**
  - GET route: `Route::get('/cash/count', [CashController::class, 'cashCount'])->name('cash.count')`
  - Abre en nueva ventana para no perder contexto
  - Compatible con impresión directa

**Interfaz:**
- **Botón en Cash/Daily**
  - Color azul distintivo (bg-blue-600 hover:bg-blue-700)
  - Icono de clipboard/documento
  - Posicionado antes del botón "Cerrar Caja"
  - Solo visible cuando caja está abierta y no cerrada
  - Abre reporte en nueva ventana con auto-print

**Diferencias vs. Cierre de Caja:**
- NO cierra la caja (operaciones continúan normales)
- NO requiere conteo de efectivo
- NO registra movimiento de cierre
- SÍ muestra todos los totales y movimientos
- SÍ permite impresión para verificación
- SÍ incluye todas las liquidaciones del día

**Técnico:**
- Archivos agregados:
  - `resources/views/cash/count-report.blade.php` - Vista de arqueo

- Archivos modificados:
  - `app/Http/Controllers/CashController.php` - Método cashCount() (líneas 510-625)
  - `routes/web.php` - Ruta cash.count (línea 85)
  - `resources/views/cash/daily.blade.php` - Botón de arqueo (líneas 48-56)
  - `VERSION` - Actualizado a 2.5.11

**Flujo de uso:**
1. Usuario en turno necesita verificar efectivo
2. Click en "Arqueo de Caja" desde vista diaria
3. Se abre nueva ventana con reporte completo
4. Reporte se imprime automáticamente
5. Usuario verifica efectivo con reporte impreso
6. Caja permanece abierta para operaciones

**Impacto:**
- ✅ Permite verificación de efectivo sin cerrar operaciones
- ✅ Ideal para cambios de turno o verificaciones intermedias
- ✅ No interfiere con flujo normal de trabajo
- ✅ Mantiene trazabilidad sin registros innecesarios
- ✅ Reporte impreso para auditoría informal
- ✅ Mejora control interno de caja

### 🧾 Recibos para Ingresos Manuales - Numeración Unificada

**Problema resuelto:**
Los ingresos manuales no generaban recibos numerados, causando:
- Inconsistencia en la numeración de comprobantes
- Imposibilidad de imprimir recibos para ingresos manuales
- Dificultad para rastrear todos los ingresos en un solo lugar

**Solución implementada:**
Sistema unificado donde TODOS los recibos (pagos de pacientes + ingresos manuales) se almacenan en la tabla `payments` con numeración secuencial compartida.

**Agregado:**
- **Migración de Base de Datos**
  - Campo `patient_id` ahora nullable en tabla `payments`
  - Nuevo campo `income_category` para almacenar tipo de ingreso manual
  - Soporte para registros sin paciente asociado

- **Registro de Ingresos Manuales**
  - Ingresos manuales ahora crean registro en tabla `payments` automáticamente
  - Generación automática de `receipt_number` secuencial
  - `payment_type` = 'manual_income' para identificar ingresos manuales
  - `liquidation_status` = 'not_applicable' (no se liquidan)
  - Registro paralelo en `cash_movements` vinculado mediante `reference_type/reference_id`

- **Impresión de Recibos de Ingresos**
  - Nueva vista `receipts/income-print.blade.php` con diseño verde distintivo
  - Muestra: número de recibo, fecha, categoría, concepto, monto
  - Formato A5 (12cm x 18cm) optimizado para impresoras térmicas
  - Auto-impresión con parámetro `?print=1`
  - Modal de confirmación con `SystemModal.confirm()` después del registro

- **Vista Unificada de Ingresos (payments/index)**
  - Ahora muestra pagos de pacientes E ingresos manuales en una sola tabla
  - Filas de ingresos manuales con fondo verde claro distintivo
  - Columna "Paciente / De" adaptada para ambos tipos
  - Botón "Imprimir Recibo" para ingresos manuales
  - Búsqueda funciona en ambos tipos (por recibo, paciente o concepto)
  - Título actualizado: "Gestión de Ingresos"

**Modificado:**
- **CashController::manualIncomeForm()**
  - Ahora crea Payment + CashMovement (antes solo CashMovement)
  - Retorna `payment_id` para impresión de recibo
  - Payment vinculado a CashMovement mediante reference

- **CashController::printIncomeReceipt()**
  - Recibe `$paymentId` en lugar de `$cashMovementId`
  - Busca en tabla `payments` en lugar de `cash_movements`
  - Validación: `payment_type === 'manual_income'`

- **PaymentController::index()**
  - SIMPLIFICADO: ya no combina dos tablas
  - Query simple sobre tabla `payments` únicamente
  - Paginación nativa de Laravel (antes manual)
  - Estadísticas incluyen todos los registros automáticamente

- **Modelo Payment**
  - Agregado `income_category` a `$fillable`
  - Soporte completo para registros sin paciente

**Rutas:**
- Actualizada: `GET /cash/income-receipt/{payment}` (antes `{cashMovement}`)

**Numeración Unificada:**
```
REC-00001 - Pago de paciente (Juan Pérez)
REC-00002 - Ingreso manual (Módulo Dr. García)
REC-00003 - Pago de paciente (María López)
REC-00004 - Ingreso manual (Corrección de caja)
REC-00005 - Pago de paciente (Carlos Díaz)
```

**Archivos modificados:**
- `database/migrations/2025_11_07_052638_make_patient_id_nullable_in_payments_table.php` - Nueva migración
- `app/Models/Payment.php` - Agregado income_category
- `app/Http/Controllers/CashController.php` - manualIncomeForm() crea Payment
- `app/Http/Controllers/PaymentController.php` - index() simplificado
- `resources/views/receipts/income-print.blade.php` - Usa objeto Payment
- `resources/views/payments/index.blade.php` - Detecta manual_income
- `resources/views/cash/manual-income-form.blade.php` - Usa payment_id
- `routes/web.php` - Ruta actualizada

**Flujo completo:**
1. Usuario registra ingreso manual desde Cash/Daily
2. Sistema crea Payment (con receipt_number) + CashMovement
3. Modal pregunta: "¿Desea imprimir el recibo ahora?"
4. Si acepta: abre recibo en nueva ventana con auto-print
5. Recibo muestra número secuencial único compartido con pagos
6. Todos los recibos visibles en payments/index con numeración ordenada

**Impacto:**
- ✅ Numeración secuencial consistente para TODOS los recibos
- ✅ Trazabilidad completa de ingresos en un solo lugar
- ✅ Recibos imprimibles para cualquier tipo de ingreso
- ✅ Simplificación del código (menos queries, menos lógica de combinación)
- ✅ Búsqueda unificada de todos los ingresos
- ✅ Cumplimiento de normativa fiscal (todos los ingresos con comprobante)
- ✅ Ordenamiento cronológico correcto por número de recibo

---

## [2.5.10] - 2025-11-03

### 📊 Separación de Gestión Operativa de Caja y Reportes Históricos

**Agregado:**
- **Módulo de Recesos y Feriados**
  - Nueva gestión completa de feriados desde Configuración
  - CRUD de feriados con activar/desactivar y eliminar
  - Filtro por año para búsqueda de feriados
  - Migración extendiendo tabla schedule_exceptions con tipo, estado y rango de fechas
  - RecessController con validaciones y operaciones AJAX

- **Integración de Feriados en Agenda**
  - Visualización de días feriados con fondo rojo distintivo
  - Bloqueo automático de creación de turnos en feriados
  - Etiqueta con descripción del feriado en calendario
  - Leyenda actualizada con indicador visual de feriados
  - Validación backend en creación y edición de turnos

- **Cards de Profesionales Favoritos en Agenda**
  - Top 6 profesionales más frecuentes mostrados al iniciar
  - Acceso directo a agenda del profesional desde cards
  - Diseño con avatar, especialidad y cantidad de turnos
  - Grid responsivo con efectos hover y gradientes

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
- **Vista de Pagos - Estadísticas mejoradas**
  - Reemplazada card "Monto Total" por dos cards específicas
  - Nueva card "💵 Efectivo" mostrando cantidad de pagos en efectivo
  - Nueva card "🏦 Transferencias" mostrando cantidad de pagos por transferencia
  - Grid actualizado a 5 columnas para mejor distribución
  - Mejor visibilidad de métodos de pago para control de caja

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
