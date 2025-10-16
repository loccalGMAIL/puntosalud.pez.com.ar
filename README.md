# 🏥 PuntoSalud - Sistema de Gestión Médica

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat&logo=php)](https://php.net)
[![Version](https://img.shields.io/badge/Version-2.5.1-green?style=flat)](#changelog)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=flat)](#license)

Sistema integral de gestión médica para clínicas y consultorios, desarrollado con Laravel 12 y tecnologías modernas.

## 📋 Tabla de Contenidos

- [Características](#características)
- [Tecnologías](#tecnologías)
- [Comandos de Desarrollo](#comandos-de-desarrollo)
- [Arquitectura del Sistema](#arquitectura-del-sistema)
- [Changelog](#changelog)
- [Contribución](#contribución)

## ✨ Características

### 🎯 **Gestión de Turnos**
- Programación de citas médicas con validación de disponibilidad
- **Sistema dual de pagos anticipados** (individual y paquetes)
- Control de estados: programado → atendido → cobrado
- Asignación automática de pagos a turnos

### 💰 **Módulo de Pagos Avanzado** *(v2.1.0)*
- **Pagos individuales**: Un turno, un pago, ingreso inmediato
- **Paquetes de tratamiento**: Múltiples sesiones, un pago grupal  
- Métodos de pago: efectivo, transferencia, tarjeta
- Generación automática de números de recibo
- Trazabilidad completa de transacciones

### 🏦 **Gestión de Caja Integral** *(v2.4.0)*
- **Sistema completo de apertura/cierre de caja** con validaciones automáticas
- **Alertas inteligentes** para recepcionistas: caja sin cerrar, apertura pendiente
- **Trazabilidad completa** de todos los movimientos financieros por usuario
- **Control de estados**: verificación automática al login de recepcionistas
- **Traducción completa** de tipos de movimiento al español con iconos
- **Balance en tiempo real** con diferencias entre efectivo contado vs teórico
- Tipos de movimiento: apertura, cierre, pagos, gastos, entrega/recibo de turno
- Reportes diarios y por períodos personalizables

### 👨‍⚕️ **Administración de Profesionales**
- Gestión de especialidades médicas
- Configuración de comisiones por profesional
- Horarios de trabajo y excepciones
- Sistema de liquidaciones automático

### 👥 **Gestión de Pacientes**
- Registro completo de información personal y médica
- Historial de citas y tratamientos
- Seguimiento de pagos y saldos

### 📊 **Dashboard Optimizado** *(v2.0.0)*
- Vista en tiempo real del día actual
- Controles dinámicos de estado de turnos
- Resumen de ingresos por profesional y método de pago
- Interfaz responsiva con componentes reutilizables

### 📋 **Sistema de Reportes** *(v2.2.0)*
- **Listado de Pacientes a Atender**: Reporte diario para profesionales al llegar
- **Liquidación Diaria de Profesionales**: Reporte de cierre con comisiones calculadas
- Diferenciación de pagos anticipados vs. cobros del día
- Vista previa web y versión optimizada para impresión
- Auto-cierre de ventanas de impresión

## 🛠 Tecnologías

### Backend
- **Laravel 12** - Framework PHP
- **PHP 8.2** - Lenguaje de programación
- **MySQL** - Base de datos
- **Eloquent ORM** - Manejo de datos

### Frontend
- **Vite** - Build tool moderno
- **TailwindCSS 4.0** - Framework de CSS
- **Alpine.js** - Framework JavaScript reactivo
- **Blade** - Motor de plantillas

## ⚡ Comandos de Desarrollo

```bash
# Desarrollo completo (servidor + queue + vite)
composer dev

# Solo servidor Laravel
php artisan serve

# Solo desarrollo frontend
npm run dev

# Construir para producción
npm run build

# Ejecutar tests
composer test
php artisan test

# Formatear código
./vendor/bin/pint

# Limpiar caché
php artisan config:clear
```

## 🏗 Arquitectura del Sistema

### Modelos Principales
- **Appointment**: Gestión de citas médicas
- **Payment**: Sistema de pagos individual y paquetes
- **Professional**: Información y configuración de médicos
- **Patient**: Datos de pacientes
- **CashMovement**: Trazabilidad completa de caja

### Servicios
- **PaymentAllocationService**: Asignación automática de pagos a turnos
- Lógica de negocio centralizada para pagos y liquidaciones

### Base de Datos
- Migraciones con versionado temporal
- Relaciones Eloquent optimizadas
- Índices para consultas eficientes

## 📝 Changelog

### v2.5.1 (2025-10-14) - Sistema de Impresión de Recibos A5

**🖨️ Nueva Funcionalidad de Recibos Impresos:**
- **Vista de Impresión A5**: Plantilla completa para imprimir recibos en formato A5 (148mm x 210mm)
  - Diseño profesional con header de clínica y número de recibo destacado
  - Información completa del paciente: nombre, DNI, obra social
  - Detalles del pago: monto, tipo (individual/paquete/reintegro), método (efectivo/transferencia/tarjeta)
  - Para paquetes: muestra sesiones usadas y restantes
  - Lista de profesionales asociados al pago con sus especialidades
  - Sección de concepto con descripción del pago
  - Espacio para firma y aclaración
  - Footer con fecha/hora de impresión

**📄 CSS Optimizado para Impresión:**
- **@media print**: Configuración específica para impresión A5
  - Tamaño de página: `@page { size: A5; margin: 0; }`
  - Ajuste automático de colores: `-webkit-print-color-adjust: exact`
  - Oculta botón de impresión al imprimir
  - Padding y márgenes optimizados: 15mm de padding interno

**⚡ Auto-impresión con JavaScript:**
- **Parámetro `?print=1`**: Dispara impresión automáticamente al cargar
  - Retraso de 500ms para asegurar carga completa de estilos
  - Función `window.print()` para abrir diálogo de impresión del navegador

**🔗 Integración con Sistema de Cobros:**
- **Dashboard Controller Actualizado**: Retorna `payment_id` en respuesta de cobros
  - Método `markCompletedAndPaid()` ahora incluye `payment_id` en JSON
  - Permite construir URL del recibo: `/payments/{id}/print-receipt`

**💬 Modal de Confirmación Post-Cobro:**
- **Pregunta Automática**: Al cobrar desde dashboard, pregunta "¿Desea imprimir el recibo?"
  - Usa `confirm()` nativo del navegador
  - Si acepta: abre recibo en nueva ventana con `?print=1`
  - Si rechaza: continúa con recarga de página
  - No bloquea el flujo normal de cobro

**🎨 Diseño del Recibo:**
- **Header Destacado**: Nombre de clínica, subtítulo y número de recibo
  - Recibo number en azul con fuente grande y bold
  - Border inferior para separación visual

**📋 Secciones del Recibo:**
1. **Información del Recibo**: Fecha, paciente, DNI, obra social
2. **Monto Total**: Destacado en caja azul con fuente grande
3. **Detalles del Pago**: Tipo y método con badges de colores
4. **Sesiones de Paquete** (si aplica): Usadas y restantes
5. **Concepto**: Descripción del pago
6. **Profesionales**: Lista de médicos asociados
7. **Firma**: Línea para firma y aclaración
8. **Footer**: Validez y timestamp de impresión

**🎯 Flujo de Usuario:**
1. Recepcionista cobra consulta desde dashboard
2. Sistema muestra notificación de éxito con número de recibo
3. Aparece modal: "¿Desea imprimir el recibo?"
4. Si confirma → abre ventana nueva con recibo y diálogo de impresión
5. Usuario imprime o guarda como PDF
6. Dashboard se recarga automáticamente

**📁 Archivos Creados:**
- `resources/views/receipts/print.blade.php` - Vista de impresión A5

**📁 Archivos Modificados:**
- `routes/web.php` - Ruta `/payments/{payment}/print-receipt` agregada
- `app/Http/Controllers/PaymentController.php` - Método `printReceipt()` agregado
- `app/Http/Controllers/DashboardController.php` - Retorna `payment_id` en cobros
- `resources/views/dashboard/dashboard.blade.php` - Modal de confirmación post-cobro
- `README.md` - Badge de versión y changelog actualizado
- `VERSION` - Actualizado a 2.5.1

**✅ Beneficios:**
- ✅ Recibos profesionales para entrega a pacientes
- ✅ Formato A5 estándar para archivado
- ✅ Impresión rápida con un solo clic
- ✅ Información completa y trazable
- ✅ Compatible con impresoras térmicas y láser
- ✅ Opción de guardar como PDF desde diálogo de impresión
- ✅ No interrumpe flujo de trabajo (ventana separada)
- ✅ Auto-impresión opcional sin pasos adicionales

**🎨 Colores y Badges:**
- **Verde**: Pago Individual
- **Azul**: Paquete de Tratamiento
- **Amarillo**: Reintegro
- **Métodos**: 💵 Efectivo | 🏦 Transferencia | 💳 Tarjeta

**📝 Nota Técnica:**
- Los recibos se generan desde registros de `Payment` existentes
- Ingresos manuales (que no crean `Payment`) no tienen recibo imprimible
- El sistema usa relaciones Eloquent para cargar paciente y profesionales
- El número de recibo ya existente en el sistema se muestra en formato legible

**🔧 Mejoras Adicionales v2.5.1:**

**📐 Optimización del Formato de Recibo:**
- **Formato Final**: Cambiado de A5 a formato personalizado 12cm × 18cm
  - Sistema viewport-based con flexbox para posicionamiento en lado derecho
  - Margen derecho de 1cm para mejor alineación en impresoras
  - Body con `display: flex; justify-content: flex-end` para posicionamiento automático
- **Tipografía Mejorada**: Aumentos significativos en tamaños de fuente
  - Body: 11px → 13px para mejor legibilidad
  - Labels: 12px → 14px con font-weight 600
  - Valores: 12px → 14px con font-weight 400
  - Títulos: 14px → 16px con font-weight 700
  - Monto total: 24px con font-weight 700 en color azul
- **CSS Classes Agregadas**: Estructura de estilos más robusta
  - `.info-row`: Flexbox con justify-between para alineación
  - `.amount-row`: Layout específico para sección de monto total
  - `.divider`: Separadores visuales de 2px con color negro
  - Mejor jerarquía visual y espaciado consistente

**🖨️ Funcionalidad de Reimpresión:**
- **Botón en Vista de Caja Diaria** (`cash/daily.blade.php`):
  - Icono de impresora morado en movimientos de tipo `patient_payment`
  - Condicional: solo visible cuando `reference_id` existe
  - Target `_blank` con parámetro `?print=1` para auto-impresión
- **Botón en Dashboard de Turnos** (`dashboard-appointments.blade.php`):
  - Nuevo botón "Imprimir recibo" junto a "Ver detalle" en turnos pagados
  - Color morado distintivo (`bg-purple-600`) para identificación
  - Condicional `@if($consulta['isPaid'])` para mostrar solo en pagos completados

**🪟 Auto-cierre de Ventana:**
- **JavaScript Mejorado**: Sistema automático de cierre post-impresión
  - Detecta parámetro `?print=1` en URL
  - Delay de 500ms para garantizar carga completa de estilos
  - Llama `window.print()` automáticamente
  - Cierra ventana emergente 100ms después de mostrar diálogo
  - Mejora UX sin intervención manual del usuario

**🎨 Sistema de Modales Unificado:**
- **Reemplazo de Alerts Nativos**: Migración completa a `SystemModal`
  - `dashboard.blade.php`: Todos los `confirm()` reemplazados por `SystemModal.confirm()`
  - `dashboard-appointments.blade.php`: Idem con confirmaciones de pago e impresión
  - Tipos implementados: `success`, `error`, `warning`, `confirm`
  - Promises para manejo asíncrono: `await SystemModal.confirm()`
- **Flujo de Pago Mejorado**:
  - Cierra modal de pago ANTES de mostrar confirmación de impresión
  - Eliminado mensaje intermedio de "turno finalizado y cobrado"
  - Flujo directo: pago → cierra modal → pregunta imprimir → recarga
- **Opacidad Ajustada**: Background modal de sistema sincronizado con payment-modal
  - Antes: `bg-opacity-20` (demasiado claro)
  - Ahora: `rgba(0, 0, 0, 0.5)` inline style (consistente con otros modales)

**💰 Correcciones en Reportes de Caja:**
- **Exclusión de Movimientos Administrativos**:
  - `cash/daily.blade.php`: Filtro `whereNotIn('type', ['cash_opening', 'cash_closing'])`
  - Ingresos y egresos calculados sin incluir apertura/cierre
  - Totales reflejan movimientos operativos reales del día
  - Grouping por tipo también excluye apertura/cierre
- **Fix Cálculo de Saldo Teórico** (`CashController.php`):
  - **Problema crítico resuelto**: Balance en cierre de caja mostraba saldo incorrecto
  - **Causa**: Ordenamiento por `movement_date DESC` ignoraba liquidaciones retroactivas
  - **Solución**: Cambio a ordenamiento por `id DESC` en método `closeCash()`
  - Ahora considera movimientos en orden cronológico de creación, no de fecha asignada
  - Liquidaciones profesionales con `movement_date` anterior se contabilizan correctamente

**🔍 Mejoras de Interfaz:**
- **Vista de Detalle de Pago** (`payments/show.blade.php`):
  - Anulado enlace "Ver perfil del paciente" (funcionalidad removida)
  - Simplificación de navegación en vista de detalles

**📁 Archivos Modificados Adicionales:**
- `resources/views/receipts/print.blade.php` - Formato, tipografía y CSS optimizado
- `resources/views/cash/daily.blade.php` - Botón reimpresión y exclusión de totales
- `resources/views/dashboard/dashboard-appointments.blade.php` - Botón reimpresión y SystemModal
- `resources/views/dashboard/dashboard.blade.php` - SystemModal y flujo de pago
- `resources/views/components/system-modal.blade.php` - Ajuste de opacidad
- `app/Http/Controllers/CashController.php` - Ordenamiento por ID y exclusión de totales
- `app/Models/CashMovement.php` - Método `getCurrentBalanceWithLock()` con orden por ID
- `resources/views/payments/show.blade.php` - Perfil de paciente anulado

**🎯 Impacto de las Mejoras:**
- ✅ Recibos con formato profesional optimizado para impresión
- ✅ Reimpresión rápida desde múltiples puntos del sistema
- ✅ Modales consistentes en toda la aplicación sin alerts nativos
- ✅ Cálculos de caja precisos sin movimientos administrativos
- ✅ Balance teórico correcto considerando liquidaciones retroactivas
- ✅ Mejor experiencia de usuario con auto-impresión y cierre automático

**🔧 Correcciones Adicionales:**
- **Reporte de Cierre de Caja Optimizado**:
  - Datos de liquidación obtenidos desde tabla `professional_liquidations` (no calculados desde pagos)
  - Logo de clínica agregado en encabezado del reporte (tamaño 192px pantalla / 144px impresión)
  - Optimización de espacios para caber en una hoja: padding reducido, tipografía más pequeña
  - Auto-cierre de ventana después de imprimir con JavaScript
  - Estado de cierre compactado con formato inline
  - Cards del resumen financiero con tipografía reducida (text-xs para labels, text-base para valores)
  - Tablas optimizadas con padding `py-0.5` y fuente `text-xs` en impresión
  - Agregado icono para tipo de movimiento "otros" (📝 Otros Ingresos)
  - Abreviaciones en headers de tablas: "Consultas" → "Cons.", "Cantidad" → "Cant."
- **Beneficios**: Mejor legibilidad, formato profesional, impresión en una sola página

**📁 Archivos Modificados (correcciones):**
- `app/Http/Controllers/CashController.php` - Liquidaciones desde DB
- `resources/views/cash/daily-report.blade.php` - Logo, optimización de espacios y tipografía

### v2.5.0 (2025-10-14) - Sincronización y Mejora del Sistema de Recibos

**🔄 Sincronización del Sistema de Números de Recibo:**
- **Unificación de Implementaciones**: El modelo `Payment` ahora genera números de recibo con el mismo formato que los controladores
  - Formato estándar: `YYYYMMNNNN` (10 dígitos numéricos)
  - Ejemplo: `2025100149` = Año 2025, Mes 10 (Octubre), Recibo #149 del mes
  - La secuencia se reinicia cada mes (no cada año)
  - Capacidad: hasta 9,999 recibos por mes

**📋 Detalles del Formato:**
- **YYYY** (4 dígitos): Año completo
- **MM** (2 dígitos): Mes (01-12)
- **NNNN** (4 dígitos): Número secuencial del mes con padding de ceros

**🔧 Cambios Técnicos:**
- **Payment Model Actualizado**: Método `generateReceiptNumber()` sincronizado
  - Cambio de reinicio anual a reinicio mensual
  - Query actualizada: usa `payment_date` en lugar de `created_at`
  - Filtrado por año Y mes (whereYear + whereMonth)
  - Ordenamiento por `receipt_number` descendente
  - Extrae últimos 4 dígitos para calcular siguiente número
- **Documentación Completa**: Agregados comentarios PHPDoc explicativos
  - Descripción del formato con ejemplos
  - Explicación de la lógica de reinicio mensual

**🎯 Estado del Sistema:**
- ✅ Modelo sincronizado con controladores (antes desincronizado)
- ✅ Formato consistente en toda la aplicación
- ✅ Sin cambios en base de datos (campo `receipt_number` VARCHAR(50) sin modificar)
- ✅ Compatible con datos existentes
- ⚠️ Código duplicado en 3 controladores (pendiente de refactorización en v2.6.0)

**🔮 Próximos Pasos (v2.6.0):**
- Deprecar métodos duplicados en PaymentController, DashboardController y AppointmentController
- Centralizar toda la lógica en el modelo Payment
- Implementar lock pesimista (`lockForUpdate()`) para prevenir condiciones de carrera
- Agregar tests unitarios para generación de recibos
- Considerar índice compuesto: `(payment_date, receipt_number)`

**📁 Archivos Modificados:**
- `app/Models/Payment.php` (líneas 197-215) - Método generateReceiptNumber() sincronizado y documentado
- `README.md` - Actualizado badge de versión y changelog
- `VERSION` - Actualizado a 2.5.0

**🔍 Contexto Histórico:**
- Versiones anteriores tenían implementación duplicada en 3 controladores
- El modelo Payment tenía formato diferente (`REC-2025-000001`) que nunca se usó en producción
- Base de datos siempre usó formato `YYYYMMNNNN` desde el inicio
- Esta versión elimina la inconsistencia entre modelo y controladores

### v2.4.18 (2025-10-14) - Optimización de Reportes y Búsqueda de Pacientes

**📋 Optimización de Impresión de Reportes:**
- **Eliminación de Pestañas al Imprimir**: Implementado sistema de impresión directa
  - Botones cambiados de `<a target="_blank">` a `<button onclick="window.print()">`
  - Patrón sessionStorage para auto-impresión desde vistas selectoras
  - Función `navigateAndPrint()` que marca flag y navega a detalle
  - Detección automática de flag para disparar `window.print()` al cargar
- **CSS @media print Mejorado**: Oculta sidebar y navegación al imprimir
  - Selectores específicos: `[x-data]:first-of-type > div:first-child`, `.fixed.left-0.top-0`
  - Reset de margin-left: `[class*="lg:ml-"]` para contenido principal
  - Preservación de colores: `-webkit-print-color-adjust: exact` para badges
- **Vistas Actualizadas**:
  - `daily-schedule.blade.php` y `daily-schedule-select.blade.php`
  - `professional-liquidation.blade.php` y `professional-liquidation-select.blade.php`
  - Experiencia fluida sin ventanas emergentes innecesarias

**🔍 Búsqueda de Pacientes en Tiempo Real:**
- **Búsqueda Backend Implementada**: La búsqueda ahora consulta toda la base de datos
  - Antes: Filtrado solo en página actual (15-20 registros)
  - Ahora: Búsqueda en todos los pacientes mediante AJAX
  - Debounce de 500ms para optimizar peticiones al servidor
- **Filtros Expandidos en Backend**:
  - Filtro de estado (activo/inactivo) agregado al controlador
  - Filtro de obra social mejorado: incluye opción "sin obra social"
  - Búsqueda multi-campo: nombre, apellido, DNI, email, teléfono
- **Paginación Aumentada**: De 15 a 50 resultados por página
  - Mejor rendimiento con menos peticiones
  - Más resultados visibles simultáneamente
- **Watchers de Alpine.js**: Detección automática de cambios en filtros
  - `$watch` para búsqueda (con debounce)
  - `$watch` para filtros de obra social y estado (inmediato)
  - Actualización automática de tabla sin recargar página
- **Recarga Después de Crear**: `window.location.reload()` después de crear/editar paciente
  - Garantiza que el nuevo paciente aparezca en resultados inmediatamente
  - Soluciona problema de caché de datos iniciales

**🎯 Beneficios:**
- ✅ Impresión más rápida y profesional sin ventanas extra
- ✅ Búsqueda eficiente en bases de datos grandes
- ✅ Mejor experiencia de usuario con búsqueda en tiempo real
- ✅ Resultados precisos sin importar el tamaño de la BD
- ✅ Filtros más potentes y flexibles

**📁 Archivos Modificados:**
- `app/Http/Controllers/PatientController.php` - Filtros backend y respuesta AJAX mejorada
- `resources/views/patients/index.blade.php` - Búsqueda en tiempo real con watchers
- `resources/views/reports/daily-schedule.blade.php` - Botón window.print() + CSS
- `resources/views/reports/daily-schedule-select.blade.php` - Función navigateAndPrint()
- `resources/views/reports/daily-schedule-print.blade.php` - Normalización con vista web
- `resources/views/reports/professional-liquidation.blade.php` - Botón window.print() + CSS
- `resources/views/reports/professional-liquidation-select.blade.php` - Función navigateAndPrint()

### v2.4.17 (2025-10-13) - Mejoras de UI/UX y Selectores Avanzados
**🎨 Mejoras en Modal de Profesionales:**
- **Tamaño y Diseño Actualizado**: Modal de profesionales ahora coincide con el de pacientes
  - Ancho ampliado de `max-w-md` a `max-w-4xl` para mejor visualización
  - Padding reducido: `py-4` → `py-3`, `space-y-4` → `space-y-3` para más compacto
  - Iconos agregados en encabezado: ➕ para crear, ✏️ para editar
  - Grid system de 12 columnas para distribución optimizada de campos

- **Botón de Especialidad Reposicionado**: Botón "+" para nueva especialidad ya no se superpone
  - Layout `flex gap-2` en lugar de posicionamiento absoluto
  - Botón al lado del select, no encima

- **Validaciones de Datos Mejoradas**: Frontend y backend sincronizados
  - Nombres y apellidos: solo letras y espacios (incluye caracteres españoles)
  - DNI: solo números y puntos
  - Validación Alpine.js en tiempo real: `@input="form.first_name = form.first_name.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"`
  - Mensajes de error en español personalizados

**📊 Optimización de Tabla de Profesionales:**
- **Diseño Compacto**: Reducción de elementos para evitar scroll horizontal
  - Padding: `px-6 py-4` → `px-2 py-2`
  - Fuentes: `text-sm` → `text-xs`
  - Iconos: `w-4 h-4` → `w-3.5 h-3.5`
  - Botones: `p-2` → `p-1`
  - Headers abreviados: "Teléfono" → "Tel.", "Comisión" → "Com.", "Acciones" → "Acc."

- **Reorganización de Columnas**: Todo en una línea sin elementos multi-fila
  - Columnas finales: Profesional, DNI, Especialidad, Email, Tel., Com., Estado, Acc.
  - Email con truncado: `max-w-[150px] truncate` para nombres largos
  - Badges compactos con `rounded` en lugar de `rounded-full`

**📋 Simplificación de Reportes Diarios:**
- **Vista daily-schedule Rediseñada**: Similar al diseño de professional-liquidation
  - Container reducido a `max-w-4xl` para mejor enfoque
  - Card de información del profesional con grid 3 columnas
  - Eliminados 4-card statistics section redundante
  - Botones reordenados: "Volver" (gris) primero, "Imprimir" (azul) segundo

- **Contenido Simplificado**: Foco en información operativa
  - **Datos Removidos**: Aranceles, total cobrado, datos de contacto (teléfono/email)
  - **Columnas Finales**: Hora, Paciente (con DNI y obra social), Estado, Observaciones
  - Estadísticas en header: total de turnos y programados (en lugar de pagados)

- **Vista daily-schedule-select Actualizada**: Consistencia con liquidación
  - Descripción mejorada: "Ver listado de pacientes programados por profesional y fecha"
  - Date selector centrado con `max-w-sm`
  - Título: "Acceso Rápido" → "Profesionales con Pacientes Programados"
  - Cards reorganizadas con formato `flex justify-between`
  - Ambos botones en azul para consistencia visual

**🔍 Selectores con Búsqueda Avanzada (Select2):**
- **Implementación de Select2 4.1.0**: Búsqueda inteligente en profesionales y pacientes
  - CDN: CSS y JS de Select2 integrados
  - jQuery 3.6.0 agregado como dependencia
  - Estilos personalizados para dark mode

- **Búsqueda Multi-campo en Pacientes**:
  - Busca por: nombre, apellido y **DNI simultáneamente**
  - Custom matcher usando `attr('data-dni')` en lugar de `.data()` para Alpine.js
  - Placeholder: "Buscar paciente por nombre o DNI..."
  - Autofocus en campo de búsqueda al abrir dropdown

- **Búsqueda en Profesionales**:
  - Busca por: nombre, apellido y especialidad
  - Data attributes: `data-specialty` para filtrado ampliado
  - Placeholder: "Buscar profesional..."

- **Integración con Modales**:
  - `dropdownParent` configurado para renderizar dentro del modal
  - Previene cierre de modal al abrir dropdown (Alpine.js `@click.away`)
  - Evento `select2:open` para autofocus en search field
  - Sincronización bidireccional con Alpine.js usando eventos custom

- **Vistas Actualizadas**:
  - **appointments/index.blade.php**: Selects de profesional y paciente en modal
  - **agenda/index.blade.php**:
    - Select de header con ancho fijo de 500px para mejor legibilidad
    - Select2 en modal de turnos (profesional y paciente)
    - Auto-submit de formulario al cambiar profesional
    - Config: `width: '500px'` y `dropdownAutoWidth: true`

**🐛 Correcciones Técnicas:**
- **Fix Modal Closing**: Reemplazado MutationObserver por setInterval (300ms)
  - MutationObserver detectaba cambios DOM de Select2 y cerraba modal
  - setInterval verifica estado de modal sin interferir con Select2

- **Fix Patient Search Filtering**: Cambio de `.data()` a `.attr()`
  - jQuery `.data()` no accede a atributos generados dinámicamente por Alpine.js
  - Solución: `$option.attr('data-dni')` accede correctamente

- **Fix Agenda Professional Select Width**: De `min-w-[400px]` a `width: 500px`
  - Inline style en elemento + config Select2 para aplicar correctamente
  - `flex-1` en form container para mejor adaptación

**🎯 Beneficios:**
- ✅ Interfaz más consistente entre módulos de profesionales y pacientes
- ✅ Búsqueda rápida y eficiente en selectores con grandes volúmenes de datos
- ✅ Reportes más claros enfocados en información operativa
- ✅ Reducción significativa de scroll horizontal en tablas
- ✅ Mejor experiencia de usuario con búsqueda multi-campo
- ✅ DNI search permite localizar pacientes rápidamente por documento

**📁 Archivos Modificados:**
- `resources/views/professionals/modal.blade.php` - Diseño, validaciones y grid
- `app/Http/Controllers/ProfessionalController.php` - Validaciones backend
- `resources/views/professionals/index.blade.php` - Tabla compacta
- `resources/views/reports/daily-schedule.blade.php` - Simplificación y limpieza
- `resources/views/reports/daily-schedule-select.blade.php` - Rediseño
- `resources/views/appointments/modal.blade.php` - IDs y data attributes para Select2
- `resources/views/appointments/index.blade.php` - Select2 CSS/JS e inicialización
- `resources/views/agenda/index.blade.php` - Select2 en header y modal con ancho ampliado
- `README.md` - Actualizado badge y changelog

### v2.4.16 (2025-10-09) - Mejoras de Visualización y UX
**📊 Sistema de Paginación:**
- **Paginación en Pacientes**: Tabla de pacientes ahora muestra 15 registros por página
  - Implementado `paginate(15)->withQueryString()` en PatientController
  - Links de paginación con Tailwind CSS
  - Mantiene filtros activos entre páginas
  - Mejor rendimiento con grandes volúmenes de datos

- **Paginación en Profesionales**: Tabla de profesionales con paginación idéntica
  - 15 registros por página para óptima visualización
  - Conserva búsquedas y filtros al cambiar de página
  - Interfaz limpia y navegable

**📧 Mejora en Reporte Diario:**
- **Email en lugar de Consultorio**: Reporte de pacientes a atender ahora muestra:
  - Línea 1: Teléfono del paciente
  - Línea 2: **Email del paciente** (antes mostraba consultorio)
  - Agregado campo `patient_email` en ReportController
  - Información más útil para contacto con pacientes

**🎯 Beneficios:**
- ✅ Mejor performance en tablas con muchos registros
- ✅ Navegación más rápida y fluida
- ✅ Información de contacto más completa en reportes
- ✅ Interfaz más profesional y escalable

**📁 Archivos Modificados:**
- `app/Http/Controllers/PatientController.php` - Paginación implementada
- `app/Http/Controllers/ProfessionalController.php` - Paginación implementada
- `resources/views/patients/index.blade.php` - Links de paginación y ajuste Alpine.js
- `resources/views/professionals/index.blade.php` - Links de paginación y ajuste Alpine.js
- `app/Http/Controllers/ReportController.php` - Campo patient_email agregado
- `resources/views/reports/daily-schedule.blade.php` - Muestra email en lugar de consultorio
- `VERSION` - Actualizado a 2.4.16

### v2.4.15 (2025-10-09) - Correcciones Críticas de Integridad Contable
**🔒 Prevención de Apertura sin Cierre Previo:**
- **Validación de Cierre Obligatorio**: No se permite abrir caja del día actual si hay días anteriores sin cerrar
  - Validación en `CashController.openCash()` antes de crear apertura
  - Mensaje específico indicando fecha exacta sin cerrar: "Primero debe cerrar la caja del día {fecha}"
  - Respuesta JSON incluye campo `unclosed_date` para referencia
  - Previene inconsistencias contables por apertura prematura

**🔧 Flujo de Validación:**
1. Primera validación: Verifica días sin cerrar usando `hasUnclosedCash()`
2. Segunda validación: Verifica que no exista apertura del día actual
3. Solo si ambas pasan, se permite crear la apertura

**🐛 Corrección de Referencias Polimórficas:**
- **Fix Error SQL**: Column not found 'professional_id' en reportes de liquidación
  - Problema: Queries usaban campo `professional_id` eliminado en v2.4.13
  - Solución: Actualizado a usar `reference_type` + `reference_id`
- **Controladores Corregidos**:
  - `ReportController.professionalLiquidation()`: 2 consultas actualizadas
  - `LiquidationController.processLiquidation()`: 1 consulta actualizada
  - Todas usan ahora: `where('reference_type', 'App\Models\Professional')`

**🎯 Impacto:**
- ✅ Elimina posibilidad de apertura sin cierre previo (bug reportado en movimientos 200-201)
- ✅ Garantiza integridad contable con secuencia obligatoria apertura → cierre
- ✅ Reportes de liquidación funcionan correctamente con referencias polimórficas
- ✅ Consultas de reintegros corregidas en todos los controladores

**📁 Archivos Modificados:**
- `app/Http/Controllers/CashController.php` - Validación de cierre previo en apertura
- `app/Http/Controllers/ReportController.php` - Referencias polimórficas
- `app/Http/Controllers/LiquidationController.php` - Referencias polimórficas
- `VERSION` - Actualizado a 2.4.15

### v2.4.14 (2025-10-07) - Botón de Reimpresión y Protección de Cajas Históricas
**🖨️ Nueva Funcionalidad de Reimpresión:**
- **Botón "Reimprimir"**: Nuevo botón en vista de Caja del Día cuando la caja está cerrada
  - Se muestra junto al botón "Ver Reporte" en el header
  - Color distintivo morado para diferenciarlo de otras acciones
  - Ícono de impresora para identificación visual clara

**⚡ Funcionalidad de Autoimpresión:**
- **Impresión Automática**: Al hacer clic en "Reimprimir" se abre el reporte en nueva ventana y automáticamente:
  - Abre el diálogo de impresión del navegador
  - Cierra la ventana emergente después de imprimir (si aplica)
  - Aprovecha parámetro `print=true` ya existente en el sistema

**🔒 Protección de Cajas Históricas:**
- **Botones Condicionales**: Los botones de acción solo se muestran en la caja del día actual
  - "Ingreso Manual" - Oculto en días anteriores
  - "Registrar Gasto" - Oculto en días anteriores
  - "Retirar Dinero" - Oculto en días anteriores
- **Prevención de Modificaciones Accidentales**: Evita que se registren movimientos en cajas cerradas de días pasados
- **Vista de Solo Lectura**: Cajas históricas son consultivas, sin opciones de modificación

**🎨 Mejoras de UX:**
- **Dos Opciones Claras**:
  - "Ver Reporte" (verde con ícono de ojo): Para visualizar en pantalla
  - "Reimprimir" (morado con ícono de impresora): Para imprimir directamente
- **Target _blank**: Abre en nueva pestaña/ventana sin perder el contexto actual
- **Interfaz Contextual**: Los botones disponibles dependen del día visualizado

**📁 Archivos Modificados:**
- `resources/views/cash/daily.blade.php` - Botón de reimpresión y protección de días anteriores
- `VERSION` - Actualizado a 2.4.14

**🎯 Beneficios:**
- ✅ Acceso rápido a reimprimir reportes de cierre sin pasos adicionales
- ✅ Mejor experiencia para usuarios que necesitan impresiones físicas
- ✅ Separación clara entre visualización e impresión
- ✅ Protección de integridad contable de cajas cerradas
- ✅ Prevención de errores al registrar movimientos en fechas incorrectas
- ✅ Aprovecha infraestructura existente (no requiere cambios en backend)

### v2.4.13 (2025-10-07) - Refactorización de Sistema de Referencias en CashMovement
**🔧 Optimización de Arquitectura:**
- **Eliminación de Campo Redundante**: Removido campo `professional_id` de tabla `cash_movements`
  - Campo era redundante con sistema de referencias polimórficas existente
  - Generaba complejidad innecesaria en estructura de datos

**✨ Implementación de Referencias Polimórficas:**
- **Sistema Unificado**: Uso exclusivo de `reference_type` y `reference_id` para todas las referencias
  - Reintegros a pacientes: `reference_type = 'App\Models\Professional'`
  - Pagos módulo profesional: `reference_type = 'App\Models\Professional'`
  - Liquidaciones profesionales: `reference_type = 'App\Models\Professional'`
  - Otros casos: mantienen sus reference_type específicos

**🔄 Cambios Implementados:**
- **CashController**:
  - `addExpense()`: Reintegros usan reference polimórfica en lugar de professional_id
  - `manualIncomeForm()`: Pagos módulo usan reference polimórfica
  - `getCashMovementDetails()`: Carga polimórfica unificada de profesional
  - `dailyCash()`: Eliminada carga eager de relación `professional`
- **Modelo CashMovement**:
  - Eliminado `professional_id` de array `$fillable`
  - Eliminada relación `professional()` (ya no necesaria)
  - Sistema `morphTo()` maneja todas las referencias
- **Vista daily.blade.php**:
  - Detección de reintegros actualizada: `reference_type === 'App\Models\Professional'`

**🎯 Beneficios:**
- ✅ Modelo de datos más limpio y consistente
- ✅ Eliminación de redundancia en estructura de base de datos
- ✅ Aprovechamiento completo del sistema polimórfico de Laravel
- ✅ Menor complejidad en queries y relaciones
- ✅ Mayor flexibilidad para referencias futuras

**📁 Archivos Modificados:**
- `app/Http/Controllers/CashController.php` - Referencias polimórficas implementadas
- `app/Models/CashMovement.php` - Campo y relación eliminados
- `resources/views/cash/daily.blade.php` - Detección actualizada
- `VERSION` - Actualizado a 2.4.13

**⚠️ Nota Técnica:**
Esta es una refactorización de fondo que no afecta funcionalidad. La migración física de la base de datos se realizará en ambiente de producción de forma separada.

### v2.4.12 (2025-10-05) - Mejoras en Agenda y Reporte de Caja
**📅 Visualización de Turnos Pasados:**
- **Modal de Días Pasados Habilitado**: Botón "+X más" ahora funciona en días pasados
  - Permite visualizar todos los turnos de días anteriores
  - Modal muestra aviso visual: "Día pasado - Solo visualización"
  - Botón "Nuevo Turno" oculto automáticamente en días pasados
- **Edición Inteligente de Turnos**: Sistema de permisos granular
  - Turnos con estado "atendido" no son editables (botón deshabilitado visualmente)
  - Turnos en fechas/horarios pasados no son editables
  - Indicadores visuales claros (opacidad 50%, cursor not-allowed)
  - Tooltips informativos: "Turno atendido - No editable"
- **Funciones JavaScript Nuevas**:
  - `isDayInPast()`: Valida si el día seleccionado es anterior a hoy
  - `isAppointmentInPast()`: Valida si la fecha/hora del turno ya pasó

**💰 Reporte de Cierre de Caja Optimizado:**
- **Desglose de Movimientos Limpio**: Apertura y cierre excluidos de tablas
  - Tabla "Desglose por Tipo de Movimiento" ya no muestra apertura/cierre de caja
  - Enfoque en movimientos operativos: pagos, gastos, retiros, reintegros
  - Totales calculados sin incluir montos de apertura/cierre
- **Cálculos Precisos**: Ingresos y egresos reflejan operaciones reales
  - `$movementsForTotals` filtra automáticamente tipos administrativos
  - Resumen financiero más representativo de la actividad del día

**⏱️ Opciones de Duración Ampliadas:**
- **Nuevas Duraciones de Turnos**: Agregadas 3 opciones al selector
  - 10 minutos (para consultas express/controles rápidos)
  - 90 minutos / 1 hora 30 minutos (terapias/procedimientos medianos)
  - 120 minutos / 2 horas (cirugías menores/procedimientos extensos)
- **Total Opciones Disponibles**: 10, 15, 20, 30, 40, 45, 60, 90, 120 minutos

**📁 Archivos Modificados:**
- `resources/views/agenda/index.blade.php` - Modal de días pasados y validaciones
- `app/Http/Controllers/CashController.php` - Filtros en método `dailyReport()`
- `resources/views/appointments/modal.blade.php` - Opciones de duración ampliadas
- `VERSION` - Actualizado a 2.4.12

**🎯 Beneficios:**
- Mayor transparencia histórica de turnos sin riesgo de modificaciones accidentales
- Reportes de caja más claros enfocados en movimientos operativos
- Flexibilidad horaria para diferentes tipos de consultas/procedimientos

### v2.4.11 (2025-10-02) - Sistema de Devoluciones/Reintegros de Profesionales
**💸 Nueva Funcionalidad de Devoluciones:**
- **Devolución a Pacientes por Profesionales**: Sistema para registrar reintegros que realiza el profesional al paciente
  - Nueva categoría "Reintegro/Devolución a Paciente" en gastos (eliminada de ingresos manuales)
  - Selector de profesional responsable (obligatorio para esta categoría)
  - El monto se registra como gasto y se asocia al profesional para futuras liquidaciones
  - Campo `professional_id` en tabla `cash_movements` con foreign key a `professionals`

**🔄 Cambios de Categorización:**
- **Movido de Ingresos a Gastos**: "Reintegro/Devolución" ya no aparece en ingresos manuales
  - Antes: Categoría disponible en `/cash/manual-income`
  - Ahora: Categoría "Reintegro/Devolución a Paciente" en `/cash/expense-form`
- **Selector Condicional**: Campo de profesional solo aparece cuando se selecciona categoría de devolución
  - Implementado con Alpine.js (x-show condicional)
  - Validación requerida solo para esta categoría específica
  - Mensaje informativo: "Este monto será descontado de la liquidación del profesional"

**🗄️ Cambios de Base de Datos:**
- **Migración**: `2025_10_02_104646_add_professional_id_to_cash_movements_table.php`
  - Campo `professional_id` UNSIGNED BIGINT nullable
  - Foreign key a tabla `professionals` con `onDelete('set null')`
  - Índice en `professional_id` para optimización de consultas
- **Modelo CashMovement**: Agregada relación `professional()` y campo en `$fillable`

**📋 Flujo de Uso:**
1. Recepcionista registra devolución desde "Registrar Gasto"
2. Selecciona categoría "Reintegro/Devolución a Paciente"
3. Aparece combo de profesionales (ordenados alfabéticamente por apellido)
4. Selecciona profesional responsable del reintegro
5. Completa monto, descripción y opcionalmente comprobante
6. El sistema registra el movimiento de caja asociado al profesional
7. Este monto podrá ser descontado en futuras liquidaciones del profesional

**🎯 Beneficios:**
- Trazabilidad completa de devoluciones por profesional
- Control de gastos post-cobro que afectan la liquidación
- Registro contable correcto (gasto, no ingreso)
- Base para futuro sistema de descuentos automáticos en liquidaciones

**📁 Archivos Modificados:**
- `database/migrations/2025_10_02_104646_add_professional_id_to_cash_movements_table.php` - Nueva migración
- `app/Models/CashMovement.php` - Relación professional() y fillable actualizado
- `app/Http/Controllers/CashController.php` - Categoría movida, validación y guardado de professional_id
- `resources/views/cash/expense-form.blade.php` - Selector condicional de profesional con Alpine.js

### v2.4.10 (2025-10-02) - Corrección Crítica del Sistema de Liquidaciones
**🔧 Refactorización Completa de Liquidaciones:**
- **PROBLEMA CRÍTICO SOLUCIONADO**: Sistema de liquidaciones no usaba las tablas diseñadas
  - Antes: Solo creaba `CashMovement` sin trazabilidad
  - Ahora: Usa correctamente `professional_liquidations` y `liquidation_details`

**🛡️ Prevención de Duplicados:**
- **Validación por fecha**: No permite liquidar dos veces el mismo profesional en la misma fecha
  - Verifica existencia de liquidación previa antes de procesar
  - Mensaje de error con ID de liquidación existente
- **Validación por pago**: No permite liquidar pagos ya liquidados
  - Verifica `liquidation_status` de cada pago asociado
  - Bloquea si detecta pagos previamente liquidados

**📊 Sistema de Trazabilidad Completo:**
- **`professional_liquidations`**: Registro resumen de la liquidación
  - Total de turnos (programados, atendidos, ausentes)
  - Monto total cobrado
  - Comisión del profesional calculada
  - Monto de la clínica
  - Estado de pago y método
  - Fecha y usuario que procesó
- **`liquidation_details`**: Detalle individual por turno
  - Link a appointment, payment y payment_appointment
  - Monto individual y comisión calculada
  - Concepto descriptivo (hora + paciente)
  - Permite auditoría completa de qué se liquidó

**✅ Actualización Automática de Estados:**
- **Campo `liquidation_status` en payments**: Ahora se actualiza correctamente
  - Marca como 'liquidated' todos los pagos incluidos en la liquidación
  - Permite filtrar pagos pendientes vs liquidados
  - Scopes `pendingLiquidation()` y `liquidated()` ahora funcionales

**🔗 Referencias Correctas:**
- **CashMovement mejorado**:
  - Antes: `reference_type` = Professional (genérico)
  - Ahora: `reference_type` = ProfessionalLiquidation (específico)
  - Permite navegar desde el movimiento de caja a la liquidación completa

**📈 Validaciones de Negocio:**
- Verifica que caja esté abierta
- Verifica que no haya turnos pendientes sin atender
- Verifica que no haya turnos atendidos sin cobrar
- Verifica saldo suficiente en caja
- **NUEVO**: Valida que monto ingresado coincida con comisión calculada

**📁 Archivos Modificados:**
- `app/Http/Controllers/LiquidationController.php` - Refactorización completa del método `processLiquidation()`
  - Usa `ProfessionalLiquidation` y `LiquidationDetail`
  - Actualiza `liquidation_status` en payments
  - Validaciones anti-duplicado robustas
  - Respuesta JSON enriquecida con más información

**🎯 Impacto:**
- ✅ Elimina duplicados de liquidaciones (problema reportado con movimientos #121, #122, #123)
- ✅ Permite auditoría completa de qué se liquidó y cuándo
- ✅ Previene errores de cálculo con validación automática
- ✅ Marcado correcto de pagos liquidados en la vista de payments
- ✅ Trazabilidad completa desde cualquier punto (turno → pago → liquidación → caja)

### v2.4.9 (2025-10-02) - Mejoras en Dashboard y Gestión de Consultas
**📊 Dashboard Optimizado:**
- **Vista de Consultas Filtrada**: Dashboard principal ahora oculta consultas completadas (atendidas + pagadas) y ausentes
  - Enfoque en consultas que requieren acción
  - Vista más limpia y orientada a tareas pendientes
- **Nueva Vista "Todas las Consultas"**: Vista completa con todas las consultas del día
  - Accesible desde botón "Ver todas →" en el dashboard
  - Muestra todas las consultas sin filtros (programadas, atendidas, pagadas, ausentes, canceladas)
  - Breadcrumb navegacional: Dashboard > Todas las Consultas
  - Botón "Volver al Dashboard"
- **Detalle de Pagos Integrado**: En consultas pagadas, nuevo botón de ojo (👁️) para ver detalle
  - Reutiliza vista existente de payments.show
  - Color verde esmeralda para asociación visual con pagos
  - Solo visible en turnos con pago registrado

**📈 Card de Métricas Mejorada:**
- **Contador de Ausentes**: Agregado indicador de consultas ausentes en card "Consultas del Día"
  - Layout en grid 2x2 para mejor distribución
  - Iconografía consistente: ✓ Completadas (verde) | ⏰ Pendientes (amarillo) | ✕ Ausentes (rojo)

**🔗 Navegación Mejorada:**
- **Botón "Ver Detalle de Caja"**: Ahora redirige a vista de Caja del Día (`/cash/daily`)
  - Acceso directo desde resumen de caja en dashboard

**📁 Archivos Nuevos:**
- `resources/views/dashboard-appointments.blade.php` - Vista completa de consultas
- Ruta: `GET /dashboard/appointments` → `dashboard.appointments`

**📁 Archivos Modificados:**
- `app/Http/Controllers/DashboardController.php` - Método `appointments()` y contador de ausentes
- `resources/views/dashboard.blade.php` - Filtros y mejoras visuales
- `routes/web.php` - Nueva ruta para vista de consultas

### v2.4.8 (2025-10-02) - Optimización de Integridad Contable y UX
**🔒 Integridad Contable Reforzada:**
- **Pagos No Editables**: Deshabilitada edición de pagos registrados para mantener trazabilidad
  - Métodos `edit()` y `update()` de PaymentController bloqueados con error 403
  - Botones de edición removidos de vistas de pagos
  - Correcciones se realizan mediante ingresos/gastos manuales
- **Sistema de Ingresos Manuales**: Nueva funcionalidad completa para ajustes de caja
  - 6 categorías: Pago Módulo Profesional, Corrección, Venta Producto, Servicio Extra, Reintegro, Otros
  - Adjuntar comprobantes (JPG, PNG, PDF hasta 2MB)
  - Lock pesimista para prevenir condiciones de carrera
  - Descripción automática con categoría incluida
- **PaymentController Actualizado**: Código obsoleto corregido
  - `createCashMovement()` ahora usa esquema correcto con `balance_after`
  - Uso de `getCurrentBalanceWithLock()` para transacciones seguras

**🎨 Mejoras de Interfaz:**
- **Tabla de Turnos Optimizada**: Reducción del 30% en ancho
  - Padding reducido: `px-6 py-4` → `px-3 py-2`
  - Fuentes más compactas: `text-sm` → `text-xs`
  - Headers abreviados: "Duración" → "Dur.", "Consultorio" → "Consult."
  - Botones de acción más pequeños con iconos reducidos
- **Formato de Montos Mejorado**: Decimales siempre visibles
  - Formato argentino: `$1.234,56` (con 2 decimales fijos)
  - Aplicado en tabla de appointments
- **Formularios de Caja Simplificados**:
  - Removida vista previa de expense-form
  - Formularios más directos y limpios
  - Adjuntar comprobantes en ambos formularios (gastos e ingresos)

**🎯 Categorías Actualizadas:**
- "Pago Bloque Profesional" renombrado a "Pago Módulo Profesional"

**📁 Archivos Modificados:**
- `app/Http/Controllers/PaymentController.php` - Pagos no editables
- `app/Http/Controllers/CashController.php` - Ingreso manual con comprobantes
- `app/Models/CashMovement.php` - Método `getCurrentBalanceWithLock()`
- `resources/views/payments/*.blade.php` - Botones de edición removidos
- `resources/views/cash/manual-income-form.blade.php` - Nueva funcionalidad
- `resources/views/cash/expense-form.blade.php` - Vista previa removida
- `resources/views/appointments/index.blade.php` - Tabla optimizada
- `routes/web.php` - Rutas de ingreso manual

### v2.4.7 (2025-10-02) - Corrección de Condiciones de Carrera en Sistema de Caja
**🔒 Sistema de Locks Pesimistas:**
- **Prevención de Condiciones de Carrera**: Implementado `lockForUpdate()` en todos los cálculos de balance de caja
  - Nuevo método `getCurrentBalanceWithLock()` en modelo `CashMovement`
  - Lock pesimista garantiza consistencia en transacciones concurrentes
  - Evita descalces de balance cuando múltiples operaciones ocurren simultáneamente
- **Controladores Actualizados**: Aplicado en todos los puntos críticos
  - `CashController`: Gastos, retiros, apertura y cierre de caja
  - `AppointmentController`: Creación de pagos anticipados
  - `DashboardController`: Cobros desde dashboard
  - `LiquidationController`: Liquidaciones profesionales

**🎨 Mejoras de Interfaz:**
- **Colores Consistentes**: Etiquetas de "Apertura de Caja" y "Cierre de Caja" ahora usan el mismo color naranja
  - Mejora visual en lista de "Movimientos del Día"
  - Mejor identificación de operaciones administrativas de caja

**🎯 Impacto:**
- Elimina errores de balance por operaciones simultáneas
- Mayor confiabilidad en sistema de caja con múltiples usuarios
- Consistencia contable garantizada a nivel de base de datos

### v2.4.6 (2025-10-01) - Mejoras de Validación y Configuración
**🔧 Sistema de Turnos Optimizado:**
- **Creación Flexible**: Turnos pueden crearse sin caja abierta cuando no hay pago inmediato
  - Validación de caja solo al cobrar instantáneamente
  - Mayor flexibilidad operativa para programación de agenda
  - Mensajes de error específicos para cada contexto
- **Cobro Seguro**: Validación de caja obligatoria al cobrar desde dashboard
  - Previene cobros cuando caja está cerrada o no abierta
  - Protege integridad contable del sistema
  - Mensajes informativos para recepcionistas

**⚙️ Configuraciones Actualizadas:**
- **Consultorios Expandidos**: Actualizado seeder de 5 a 10 consultorios
  - Numeración secuencial: Consultorio 1 al 10
  - Todos activos por defecto para uso inmediato
- **Duraciones Refinadas**: Opciones de tiempo más granulares para turnos
  - Nuevas opciones: 15, 20, 30, 40, 45, 60 minutos
  - Eliminadas duraciones extensas (90, 120 min) por optimización
  - Validación backend actualizada para valores exactos

**🎯 Beneficios:**
- Operación más fluida sin restricciones innecesarias de caja
- Mayor seguridad en procesos de cobro
- Configuración más práctica para clínicas reales
- Mejor experiencia de usuario para recepcionistas

### v2.4.5 (2025-09-29) - Correcciones del Sistema de Caja y Reportes
**🔧 Sistema de Cierre de Caja Mejorado:**
- **Retiro Completo de Saldo**: Al cerrar caja, todo el saldo se retira automáticamente
  - El balance queda en 0 después del cierre, evitando arrastrar errores al día siguiente
  - Descripción mejorada muestra tanto el efectivo contado como el saldo retirado
  - Cálculo correcto de diferencias entre efectivo contado vs saldo real

**📊 Mejoras en Reportes de Caja:**
- **Ordenamiento Inverso**: Tabla de "Datos Tabulares del Período" ahora muestra fechas más recientes primero
  - Aplica para agrupación por día, semana y mes
  - Mejor experiencia de usuario al ver primero los datos más actuales
- **Análisis Filtrado**: Removidos movimientos administrativos del análisis por tipo
  - Excluidos: apertura y cierre de caja del análisis por tipo de movimiento
  - Enfoque en movimientos operativos relevantes (pagos, gastos, retiros)
- **Traducción Mejorada**: Tipo "cash_withdrawal" ahora aparece como "💸 Retiro de Efectivo"
  - Consistencia en idioma español con iconos apropiados

**🎯 Beneficios:**
- Mayor precisión contable con saldos que no se arrastran entre días
- Reportes más claros enfocados en operaciones de negocio
- Mejor experiencia de usuario con datos ordenados cronológicamente

### v2.4.4 (2025-09-26) - Modal de Detalles de Movimientos de Caja
**🔍 Sistema de Detalles Avanzado:**
- **Modal Interactivo**: Nuevo modal de detalles al hacer clic en el botón 👁️ de cualquier movimiento
  - Información básica: ID, fecha, tipo, usuario, monto y saldo resultante
  - Descripción completa del movimiento
  - Datos contextuales específicos según el tipo de movimiento
- **Detalles de Pagos de Pacientes**: Sección azul especializada que muestra:
  - Número de recibo y método de pago (efectivo/transferencia/tarjeta)
  - **Nombre completo del paciente** con accessor automático
  - Lista de profesionales relacionados con el pago
  - Tipo de pago (individual/paquete de tratamiento)
  - Notas adicionales del pago
- **Detalles de Liquidaciones Profesionales**: Sección verde especializada que muestra:
  - **Nombre completo del profesional** liquidado
  - **Especialidad médica** con relación cargada automáticamente
  - Información de contacto y porcentaje de comisión

**🔧 Mejoras Técnicas del Backend:**
- **Endpoint Mejorado**: `getCashMovementDetails()` con carga inteligente de relaciones
  - Carga automática de datos del pago y paciente para `patient_payment`
  - Carga automática de datos del profesional y especialidad para `professional_payment`
  - Manejo seguro de errores para evitar crashes por datos inconsistentes
- **Accessor Full Name**: Agregado `'full_name'` al array `$appends` del modelo `Patient`
  - Combina automáticamente `first_name + ' ' + last_name`
  - Disponible en todas las respuestas JSON del paciente
- **Compatibilidad Reference Types**: Soporte para múltiples formatos de `reference_type`
  - Compatible con `'payment'` y `'App\\Models\\Payment'`
  - Compatible con `'professional'` y `'App\\Models\\Professional'`

**🎨 Experiencia de Usuario:**
- **Modal Responsive**: Diseño adaptativo con animaciones suaves
  - Cierre con Escape, click fuera o botón X
  - Loading states durante la carga de datos
  - Manejo de errores con mensajes informativos
- **Información Contextual Rica**: Cada tipo de movimiento muestra datos relevantes
  - Pagos: Quién pagó, a qué doctores, cómo pagó
  - Liquidaciones: Qué profesional, de qué especialidad
  - Otros: Información básica estándar
- **Navegación Mejorada**: Fácil acceso desde la tabla de movimientos diarios
  - Botón visual intuitivo en cada fila
  - Información detallada sin salir de la vista principal

### v2.4.3 (2025-09-26) - Optimizaciones del Sistema de Caja
**💰 Mejoras en Gestión de Movimientos de Caja:**
- **Ordenamiento Optimizado**: Los movimientos diarios ahora se ordenan por `created_at` únicamente
  - Eliminado ordenamiento redundante por `movement_date`
  - Mejor rendimiento en consultas y visualización más intuitiva
  - Los movimientos aparecen en el orden real de registro
- **Columna ID Agregada**: Nueva columna con ID único de movimiento para trazabilidad
  - Formato `#123` con fuente monoespaciada para mejor legibilidad
  - Facilita debugging y seguimiento de operaciones específicas
- **Visualización de Profesionales**: En pagos profesionales se muestra el nombre del médico
  - Reemplaza descripción genérica con "Dr. [Nombre] [Apellido]"
  - Descripción original como subtexto para mantener contexto

**🔧 Correcciones y Mejoras Técnicas:**
- **Fix Apertura de Caja**: Corregido cálculo incorrecto de `balance_after`
  - La apertura ahora suma al saldo anterior en lugar de reemplazarlo
  - Monto de apertura ahora es opcional (puede ser 0)
  - Elimina descoordinación en balances diarios
- **Enum Actualizado**: Agregado `cash_withdrawal` a tipos de movimiento permitidos
  - Soluciona error de truncado en retiros de caja
  - Base de datos y migración actualizadas
- **UI Simplificada**: Removidas cards de apertura/cierre del resumen por tipo
  - Resumen enfocado en movimientos operativos relevantes
  - Interfaz más limpia sin información redundante

### v2.4.2 (2025-09-23) - Sistema de Liquidaciones y Mejoras de UX
**🏦 Sistema de Liquidación de Profesionales:**
- **Botón de Liquidación**: Nuevo botón "Liquidar" en reportes de liquidación profesional
  - Disponible en vista de selección (`/reports/professional-liquidation`) y detalle
  - Solo visible cuando hay monto a liquidar (> 0)
  - Color distintivo naranja para diferenciarlo de otras acciones
- **Validaciones Avanzadas**: Control completo antes de liquidar
  - Verifica que no haya turnos sin atender del profesional
  - Verifica que no haya turnos atendidos sin cobrar
  - Valida saldo suficiente en caja y que esté abierta
  - Mensajes específicos indicando qué falta por completar
- **Movimientos de Caja**: Registro automático de pagos a profesionales
  - Tipo `professional_payment` con monto negativo
  - Referencia al profesional y usuario que procesa
  - Actualización automática del balance de caja

**🎨 Sistema de Modales Reutilizable:**
- **Componente Global**: Modal unificado para todo el sistema (`<x-system-modal>`)
  - Tipos: success, error, warning, confirm
  - Iconos y colores temáticos por tipo
  - Soporte para HTML en mensajes
- **JavaScript Global**: `SystemModal.confirm()` y `SystemModal.show()`
  - Reemplaza alerts nativos por interfaz profesional
  - Animaciones suaves y responsive design
  - Cierre con Escape y click fuera
- **UX Mejorada**: Confirmaciones elegantes para operaciones críticas
  - Liquidaciones con modal de confirmación
  - Mensajes de éxito y error consistentes
  - Mejor feedback visual para el usuario

**🔧 Correcciones y Mejoras Técnicas:**
- **Fix Detección de Caja Cerrada**: Corregido problema en `hasUnclosedCash()`
  - Cambio de `whereColumn()` a `whereRaw('DATE()')` para comparar fechas
  - Resuelve falsos positivos de "caja sin cerrar"
- **Gestión de Versiones**: Sistema de versionado mejorado
  - Versión leída desde archivo `version` en raíz del proyecto
  - Independiente del `.env` para mejor control en git
- **Comando Artisan**: Nuevo comando `php artisan cache:limpiar`
  - Limpia y regenera todas las cachés del sistema
  - Combina `optimize:clear`, `config:cache`, `route:cache`, `view:cache`

**🧭 Mejoras de Navegación:**
- **Breadcrumbs**: Agregados en vistas de reportes
  - `/reports/daily-schedule`: Dashboard > Reportes > Pacientes a Atender
  - `/reports/professional-liquidation`: Dashboard > Reportes > Liquidación Profesionales
- **Botones de Retorno**: Botón "Volver al Dashboard" en headers de reportes
  - Mejora la navegación con múltiples opciones de retorno
  - Diseño consistente con el sistema

**🎯 Limpieza de Código:**
- **Dashboard Simplificado**: Removidos botones de liquidación del dashboard principal
  - Interfaz más limpia y enfocada
  - Liquidaciones centralizadas en vistas específicas
- **Estados de Caja**: Eliminados mensajes permanentes de "caja abierta"
  - Solo muestra alertas cuando hay problemas que requieren acción
  - Interfaz menos intrusiva para operación normal

### v2.4.1 (2025-09-17) - Mejoras Avanzadas del Sistema de Caja
**🔧 Nuevas Funcionalidades de Caja:**
- **Visualización de Usuarios**: Los movimientos de caja ahora muestran el usuario responsable
  - Avatar circular con iniciales y nombre completo del usuario
  - Trazabilidad completa de quién genera cada ingreso/egreso
- **Reporte de Cierre Diario**: Nuevo reporte imprimible con resumen completo
  - Layout específico para impresión sin elementos de navegación
  - Desglose por tipo de movimiento y actividad por usuario
  - Estado de cierre con diferencias calculadas automáticamente
- **Retiro de Dinero**: Nueva funcionalidad para registrar salidas de efectivo
  - Formulario específico con tipos de retiro (depósito bancario, gastos, etc.)
  - Validación de saldo disponible antes de permitir retiros
  - Integración completa con el sistema de movimientos

**🛡️ Validaciones de Integridad:**
- **Control de Turnos vs Caja**: Los turnos requieren caja abierta para pagos inmediatos
  - Validación en backend: no permite turnos de hoy si caja cerrada
  - Validación de pagos: bloquea pagos inmediatos si caja no está operativa
  - Alertas visuales en agenda mostrando estado de caja en tiempo real
- **Consistencia Contable**: Previene inconsistencias entre turnos futuros y pagos presentes
  - Mensajes específicos según el contexto (turno hoy vs pago inmediato)
  - Opción de crear turno sin pago si la caja está cerrada

**🎨 Mejoras de Interfaz:**
- **Botón Condicional**: "Cerrar Caja" / "Reporte de Cierre" según estado
- **Modal de Cierre**: Interfaz intuitiva con resumen del día y detección de diferencias
- **Alertas Contextuales**: Estados de caja visibles en tiempo real (abierta/cerrada/sin abrir)
- **Flujo Automático**: Después del cierre redirige automáticamente al reporte generado

### v2.4.0 (2025-09-16) - Sistema Completo de Apertura/Cierre de Caja
**💰 Sistema de Gestión de Caja:**
- **Apertura/Cierre Automático**: Sistema completo de control de caja diario
  - Validación automática al login para recepcionistas
  - Alertas inteligentes: caja sin cerrar de día anterior, apertura pendiente
  - Registro del monto inicial y efectivo contado con diferencias
- **Trazabilidad por Usuario**: Seguimiento completo de quién abre/cierra la caja
  - Timestamps precisos y registro del usuario responsable
  - Control de estados: abierta, cerrada, necesita apertura
- **Modelos y Validaciones**: Lógica de negocio robusta
  - Nuevos scopes en CashMovement: `openingMovements()`, `closingMovements()`, `forDate()`
  - Métodos estáticos: `getCashStatusForDate()`, `hasUnclosedCash()`
  - Validaciones para prevenir múltiples aperturas/cierres del mismo día

**🎨 Interfaz de Usuario:**
- **Alertas Contextuales**: Banners informativos según estado de caja
  - 🔴 Rojo: Caja sin cerrar de día anterior (acción requerida)
  - 🟡 Amarillo: Necesita apertura del día actual
  - 🟢 Verde: Caja abierta correctamente con información del responsable
- **Modales Funcionales**: Formularios intuitivos para apertura/cierre
  - Validación de montos y campos opcionales para notas
  - Resumen automático con diferencias entre teórico vs contado
- **Traducción Completa**: Todos los tipos de movimiento en español
  - Iconos diferenciados por tipo: 🔓 Apertura, 🔒 Cierre, 💰 Pagos, etc.
  - Colores distintivos para identificación visual rápida

**🔧 Correcciones y Mejoras:**
- **Gestión de Pagos Anticipados**: Flujo corregido para evitar doble cobro
  - Pagos se crean al momento pero se asignan al atender el turno
  - Asignación automática de pagos al marcar turnos como atendidos
  - Actualización correcta de `final_amount` para dashboard y liquidaciones
- **Cálculo de Ingresos**: Dashboard corregido para mostrar ingresos reales del día
  - Basado en asignaciones de pago de turnos atendidos (no solo pagos creados)
  - Separación correcta por métodos de pago
- **Validaciones de Formularios**: Corrección de errores 422 en creación de turnos
  - Validación flexible de campos boolean y opcionales
  - Manejo mejorado de errores con logging para debug

**🚀 Nuevas Rutas y Controllers:**
- `GET /cash/status` - Verificar estado actual de caja
- `POST /cash/open` - Abrir caja con monto inicial
- `POST /cash/close` - Cerrar caja con conteo final

### v2.3.0 (2025-09-11) - Sistema de Autenticación y Control de Usuarios
**🔐 Nuevas Funcionalidades:**
- **Sistema de Autenticación Completo**: Login/logout con validación de credenciales
  - Pantalla de login moderna con imagen de fondo personalizada
  - Validación de usuarios activos y manejo de sesiones
  - Redirección automática según estado de autenticación
- **Gestión de Usuarios**: CRUD completo con control de permisos
  - Roles diferenciados: Administrador y Recepcionista
  - Activación/desactivación de usuarios
  - Políticas de autorización (UserPolicy)
- **Control de Acceso por Roles**: Sistema de permisos granular
  - Solo administradores pueden gestionar usuarios
  - Acceso diferenciado al menú de navegación
  - Protección de rutas sensibles
- **Middleware de Seguridad**: Verificación automática de usuarios activos
  - CheckUserActive middleware personalizado
  - Logout automático de usuarios desactivados
  - Protección de todas las rutas con middleware auth

**🎨 Mejoras de Interfaz:**
- **Pantalla de Login Rediseñada**: Diseño moderno de dos columnas
  - Panel izquierdo con imagen de fondo difuminada (back_login.png)
  - Gradientes verdes coherentes con la identidad visual
  - Información de marca y características del sistema
- **Menú de Usuario**: Dropdown con perfil y logout
  - Enlace a gestión de usuarios (solo admin)
  - Vista de perfil personal con cambio de contraseña
  - Navegación mejorada con breadcrumbs

**🏗️ Arquitectura y Seguridad:**
- **Modelos Expandidos**: User model con métodos de rol y scopes
- **Controladores Nuevos**: AuthController y UserController
- **Vistas Adicionales**: Login, gestión de usuarios, perfil
- **Seeders**: Usuarios por defecto (admin y recepcionista)
- **Rutas Protegidas**: Todas las rutas existentes requieren autenticación

**👤 Usuarios por Defecto:**
- Administrador: `admin@puntosalud.com` / `password123`
- Recepcionista: `recepcion@puntosalud.com` / `password123`

### v2.2.3 (2025-09-11) - Mejoras de UI y Experiencia de Usuario
**🎨 Mejoras de Interfaz:**
- **Dashboard optimizado**: Cards superiores reducidas para mejor aprovechamiento del espacio
  - Elementos más compactos sin perder legibilidad
  - Botones de reportes reubicados en línea con métricas principales
- **Favicon personalizado**: Nuevo diseño SVG representativo de PuntoSalud
  - Cruz médica con punto dorado distintivo y línea de pulso
- **Navegación breadcrumb**: Implementada en todas las vistas principales
  - Patrón consistente para mejor orientación del usuario
- **Títulos estandarizados**: Formato unificado "Sección - PuntoSalud"

**🔧 Mejoras de Contenido:**
- **Menú lateral**: "Pagos" → "Cobro Pacientes" (mayor claridad)
- **Estados de liquidación**: "Pendiente" → "Para liquidar" en vista de pagos
- **Eliminación de card innecesaria**: Removida "Profesionales Activos" del dashboard

### v2.2.2 (2025-09-11) - Corrección Sistema de Turnos
**🐛 Correcciones Críticas:**
- **Fix creación de turnos del mismo día**: Sistema ahora permite crear turnos para hoy con validación de horarios
  - Corrección en lógica de fechas pasadas: `isPast()` → `isBefore(today())`
  - Botón "+" aparece correctamente en el día actual
- **Validación completa de disponibilidad**: Sistema robusto que verifica:
  - Horarios laborales del profesional por día de semana
  - Conflictos con turnos existentes considerando duración
  - Días feriados y excepciones de horario
  - Fines de semana automáticamente bloqueados
- **Fix error 500 en creación de turnos**: Corrección de tipos de datos para Carbon
  - Conversión de `$duration` string a entero para `addMinutes()`
  - Aplicado en `store()`, `update()` y `availableSlots()`
- **Mejores mensajes de validación**: Mensajes personalizados en español
  - "Debe seleccionar un paciente" en lugar de mensajes genéricos
  - Información detallada de horarios disponibles vs solicitados

**🔧 Mejoras Técnicas:**
- Importación de modelos `ProfessionalSchedule` y `ScheduleException`
- Validación de horarios usando formato correcto (`H:i` vs objetos DateTime)
- Soporte para edición de turnos con exclusión del turno actual en validaciones
- Mensajes de error específicos con rangos horarios y motivos de rechazo

### v2.2.1 (2025-09-11) - Mejoras en Gestión de Pacientes
**🆕 Nuevas Funcionalidades:**
- **Sistema de activación/desactivación de pacientes**: Control completo del estado de pacientes
  - Campo `activo` en base de datos con valor por defecto `true`
  - Interfaz visual con botones de toggle activo/inactivo
  - Filtros por estado en la vista de pacientes

**🔧 Mejoras:**
- **Formateo automático de DNI**: Los DNI se formatean automáticamente con puntos
  - Entrada: `25678910` → Guardado: `25.678.910`
  - Manejo de DNI de 7 y 8 dígitos
  - Limpieza automática de espacios y puntos existentes
- **Corrección de fechas en edición**: Fix del formato de fecha de nacimiento en modal de edición
- **Corrección de estadísticas**: Arreglo en el conteo de pacientes sin obra social
  - Lógica simplificada: Total - Con obra social = Sin obra social

**🐛 Correcciones:**
- Fix en accessor `is_active` para compatibilidad entre frontend y backend
- Corrección en validación de campos `activo` vs `is_active`
- Mejora en la lógica de conteo de estadísticas de pacientes
- Formato correcto de fechas ISO para inputs HTML tipo date

### v2.2.0 (2025-08-30) - Sistema de Reportes para Profesionales
**🆕 Nuevas Funcionalidades:**
- **Listado de Pacientes a Atender**: Reporte diario imprimible para profesionales
  - Filtrado automático por profesional y fecha
  - Vista de selección con accesos directos
  - Información completa: horarios, pacientes, montos, estado de pagos
- **Liquidación Diaria de Profesionales**: Reporte de cierre con cálculos de comisiones
  - Separación de turnos por tipo de pago (anticipado, del día, pendiente)
  - Cálculo automático de comisiones por profesional
  - Resumen detallado de ingresos y liquidación
- **Sistema de impresión optimizado**: Auto-cierre de ventanas tras imprimir
- **Vistas de preview**: Visualización web antes de imprimir

**🔧 Mejoras:**
- Nuevos métodos en ReportController para manejo de reportes
- Vistas Blade optimizadas para impresión con CSS específico
- JavaScript para manejo automático de ventanas de impresión
- Integración completa con el dashboard principal

**🎯 Casos de Uso:**
- Profesional llega → imprime listado de pacientes del día
- Profesional se retira → imprime liquidación con sus comisiones

### v2.1.0 (2025-08-30) - Sistema Dual de Pagos Anticipados
**🆕 Nuevas Funcionalidades:**
- Sistema dual de pagos: individual y paquetes de tratamiento
- Pago anticipado al crear turnos con ingreso inmediato a caja
- Cálculo automático de totales para paquetes
- Modal mejorado con opciones flexibles de pago
- Extensión de tipos de movimientos de caja

**🔧 Mejoras:**
- Validaciones completas en frontend y backend
- JavaScript modernizado con ES6+ y async/await
- Componentes Blade reutilizables
- Manejo de errores robusto con transacciones DB

**🐛 Correcciones:**
- Fix en PaymentAllocationService para sesiones de paquetes
- Corrección de paths SVG malformados en dashboard
- Mejora en modal positioning y funcionalidad

### v2.0.0 (2025-08-28) - Dashboard y Módulo de Pagos
**🆕 Funcionalidades:**
- Dashboard completo para recepcionistas
- Módulo de pagos con liquidaciones automáticas
- Gestión de movimientos de caja
- Sistema de estados de turnos dinámico

### v1.0.0 (2025-07-03) - Versión Base
**🆕 Funcionalidades:**
- Gestión básica de turnos médicos
- CRUD de pacientes y profesionales
- Sistema de horarios y disponibilidad
- Interfaz base con Laravel 12

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Convenciones
- Usar Laravel Pint para formateo de código
- Escribir tests para nuevas funcionalidades
- Documentar cambios en el changelog

## 📄 License

Este proyecto está bajo la licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

---

**Desarrollado con ❤️ para el sector salud**

*Sistema en desarrollo activo - Contribuciones bienvenidas*