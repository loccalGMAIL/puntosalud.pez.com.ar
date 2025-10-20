# =Ý Changelog - PuntoSalud

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [2.5.3] - 2025-10-20

### ¡ Optimización de Rendimiento y Fix de Modales

**Añadido:**
- Atributo `defer` en todos los scripts de CDN (jQuery y Select2)
  - Dashboard, Appointments y Agenda optimizados
  - Mejora estimada del 20-30% en tiempo de carga inicial
  - Scripts se descargan en paralelo sin bloquear rendering

**Corregido:**
- Flash visual de modales al cargar páginas
  - Agregado `x-cloak` a modal de pacientes
  - Agregado `x-cloak` a modal principal y de especialidades de profesionales
  - Agregado CSS `[x-cloak] { display: none !important; }` en todas las vistas necesarias
  - Resuelve problema donde modales eran visibles por 1-30 segundos

**Técnico:**
- Auditoría completa de todas las vistas del proyecto
- Solo 3 vistas usan scripts CDN (todas optimizadas)
- Alpine.js ahora oculta correctamente los modales durante inicialización

**Archivos Modificados:**
- `resources/views/patients/modal.blade.php`
- `resources/views/patients/index.blade.php`
- `resources/views/professionals/modal.blade.php`
- `resources/views/professionals/index.blade.php`
- `resources/views/appointments/index.blade.php`
- `resources/views/agenda/index.blade.php`
- `resources/views/dashboard/dashboard.blade.php`

---

### <æ Mejoras en UX y Validaciones de Caja

**Añadido:**
- Auto-submit en selector de fecha (Cash/Daily)
  - Evento `@change="filterByDate()"` para recarga automática
  - Elimina necesidad de hacer clic en botón "Filtrar"
  - Mejora significativa en UX y velocidad de navegación

**Corregido:**
- Validación de liquidaciones pendientes en cierre de caja
  - Cambio de lógica: verifica existencia de liquidaciones, no payment_status
  - Detecta profesionales con turnos atendidos sin liquidación creada
  - Query optimizado con filtros correctos

**Añadido:**
- Usuario Priscila agregado al UserSeeder
  - Email: gomezpri20@gmail.com
  - Rol: receptionist
  - Datos de producción para desarrollo

**Archivos Modificados:**
- `app/Http/Controllers/CashController.php`
- `resources/views/cash/daily.blade.php`
- `database/seeders/UserSeeder.php`

---

### = Validación de Liquidaciones Pendientes

**Añadido:**
- Bloqueo de cierre de caja con liquidaciones profesionales pendientes
  - Validación automática al intentar cerrar caja
  - Verifica liquidaciones con `payment_status = 'pending'`
  - Mensaje descriptivo con nombres de profesionales pendientes

**Flujo de Validación:**
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

### =¨ Sistema de Entreturnos/Urgencias

**Añadido:**
- Sistema completo de atención de urgencias sin turno programado
  - Modal de registro desde dashboard con acceso rápido
  - Valor por defecto $0 (modificable según necesidad)
  - Registro con fecha y hora actual automática
  - Búsqueda avanzada de profesionales y pacientes con Select2

**Interfaz:**
- Destacado visual en ROJO en todos los listados
  - Identificador emoji =¨ + badge "URGENCIA"
  - Separación visual clara del resto de turnos
  - Prioridad en ordenamiento de consultas

**Funcionalidad:**
- Integración completa con sistema de pagos
- Incluido automáticamente en liquidaciones profesionales
- Compatible con todos los métodos de pago
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

### =¨ Sistema de Impresión Profesional de Recibos A5

**Añadido:**
- Vista de impresión optimizada para formato A5 (148 x 210 mm)
  - Diseño profesional con logo y datos de la empresa
  - Información completa del pago y paciente
  - Desglose claro de método de pago y concepto
  - Código QR con enlace al recibo (futuro uso)

**Características:**
- Auto-impresión con parámetro `?print=1` en URL
- Cierre automático de ventana después de imprimir
- Vista previa antes de imprimir (sin parámetro)
- Responsive para diferentes tamaños de papel

**Interfaz:**
- Botón "Imprimir Recibo" en vista de pago
- Modal de confirmación después de cobro
  - Opción: "Sí, imprimir" o "No"
  - Abre en nueva pestaña para no perder contexto

**Técnico:**
- CSS optimizado para impresión
- Márgenes y padding ajustados para A5
- Fuentes legibles y profesionales
- Compatible con todos los navegadores modernos

**Archivos Añadidos:**
- `resources/views/payments/receipt.blade.php`
- `app/Http/Controllers/PaymentController.php::printReceipt()`

**Archivos Modificados:**
- `resources/views/payments/show.blade.php`
- `resources/views/dashboard/dashboard.blade.php`
- `routes/web.php`

---

## [2.5.0] - 2025-10-14

### =" Sincronización y Mejora del Sistema de Recibos

**Añadido:**
- Sistema de numeración automática de recibos
  - Formato: YYYYMM####  (Año + Mes + 4 dígitos)
  - Ejemplo: 202510001, 202510002, etc.
  - Reinicio automático cada mes
  - Generación secuencial garantizada

**Corregido:**
- Sincronización de números de recibo
  - Campo `receipt_number` agregado a migraciones existentes
  - Seeders actualizados para generar números correctos
  - Migración retroactiva para pagos existentes sin número

**Mejora:**
- Generación de recibos en DashboardController
  - Método `generateReceiptNumber()` privado
  - Query optimizado para obtener último número del mes
  - Manejo de casos edge (primer pago del mes)
  - Padding con ceros a la izquierda

**Archivos Modificados:**
- `app/Http/Controllers/DashboardController.php`
- `database/seeders/PaymentSeeder.php`
- `database/migrations/xxxx_add_receipt_number_to_payments.php`

---

## [2.4.0] - 2025-10-13

### <æ Sistema Integral de Gestión de Caja

**Añadido:**
- Sistema completo de apertura y cierre de caja
  - Validaciones automáticas por fecha
  - Bloqueo de operaciones si caja no está abierta
  - Control de estado al login de recepcionistas

**Alertas Inteligentes:**
- Dashboard con alertas para recepcionistas
  - Caja sin cerrar de día anterior (alerta roja)
  - Caja del día sin abrir (alerta amarilla)
  - Botones de acción directa desde alertas

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
  - Tabla con todos los movimientos del día
  - Filtros por fecha con botón "Hoy"
  - Indicadores visuales por tipo de movimiento
  - Traducción completa al español con iconos

**Balance:**
- Cálculo automático en tiempo real
  - Balance teórico vs. efectivo contado
  - Diferencias resaltadas en rojo
  - Trazabilidad por usuario

**Archivos Añadidos:**
- `app/Http/Controllers/CashController.php`
- `resources/views/cash/daily.blade.php`
- `app/Models/CashMovement.php`

**Archivos Modificados:**
- `resources/views/dashboard/dashboard.blade.php`
- `routes/web.php`

---

## [2.3.0] - 2025-10-12

### =Ê Reportes Profesionales

**Añadido:**
- Reporte de Pacientes a Atender
  - Listado diario para profesionales al llegar
  - Información de paciente, hora, monto y obra social
  - Vista previa web y versión impresión

- Reporte de Liquidación Diaria
  - Comisiones calculadas por profesional
  - Diferenciación de pagos anticipados vs. cobros del día
  - Subtotales y total general
  - Auto-cierre después de imprimir

**Interfaz:**
- Accesos rápidos desde Dashboard
- Selectores de fecha y profesional
- Diseño optimizado para impresión A4

**Archivos Añadidos:**
- `app/Http/Controllers/ReportController.php`
- `resources/views/reports/daily-schedule.blade.php`
- `resources/views/reports/daily-schedule-print.blade.php`
- `resources/views/reports/professional-liquidation.blade.php`

---

## [2.2.0] - 2025-10-10

### =° Sistema Dual de Pagos

**Añadido:**
- Pagos individuales (single)
  - Un turno, un pago
  - Ingreso inmediato a caja
  - Asignación automática

- Paquetes de tratamiento (package)
  - Múltiples sesiones, un pago grupal
  - Distribución automática entre turnos
  - Seguimiento de sesiones usadas

**Mejoras:**
- PaymentAllocationService
  - Lógica de asignación centralizada
  - Manejo de prioridades (urgencias primero)
  - Validaciones de saldos

**Archivos Añadidos:**
- `app/Services/PaymentAllocationService.php`
- `app/Models/PaymentAppointment.php`

**Archivos Modificados:**
- `app/Models/Payment.php`
- `app/Http/Controllers/PaymentController.php`

---

## [2.1.0] - 2025-10-08

### =Ë Dashboard Moderno

**Añadido:**
- Vista en tiempo real del día actual
- Métricas principales
  - Consultas del día (total, completadas, pendientes, ausentes)
  - Ingresos por método de pago
  - Profesionales activos
- Listado de consultas con acciones rápidas
  - Marcar como atendido
  - Finalizar y cobrar
  - Marcar ausente
- Resumen de caja por profesional

**Componentes:**
- Alpine.js para interactividad
- Modales de pago optimizados
- Sistema de notificaciones con SystemModal

**Archivos Añadidos:**
- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard/dashboard.blade.php`
- `resources/views/components/payment-modal.blade.php`
- `resources/views/components/system-modal.blade.php`

---

## [2.0.0] - 2025-10-05

### <¯ Versión Inicial Estable

**Core del Sistema:**
- Gestión completa de turnos médicos
- Administración de profesionales y especialidades
- Registro de pacientes con historial
- Sistema de horarios y excepciones
- Liquidaciones profesionales básicas

**Tecnologías Base:**
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

- **Añadido** - para nuevas funcionalidades
- **Cambiado** - para cambios en funcionalidad existente
- **Deprecado** - para funcionalidades que se eliminarán
- **Eliminado** - para funcionalidades eliminadas
- **Corregido** - para corrección de bugs
- **Seguridad** - en caso de vulnerabilidades
