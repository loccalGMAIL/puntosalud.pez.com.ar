# 🏥 PuntoSalud - Sistema de Gestión Médica

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat\&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat\&logo=php)](https://php.net)
[![Version](https://img.shields.io/badge/Version-2.11.0-green?style=flat)](#changelog)
[![License](https://img.shields.io/badge/License-MIT-yellow?style=flat)](#license)

Sistema integral de gestión médica para clínicas y consultorios, desarrollado con Laravel 12 y tecnologías modernas.

## 📋 Tabla de Contenidos

* [Características](#características)
* [Tecnologías](#tecnologías)
* [Comandos de Desarrollo](#comandos-de-desarrollo)
* [Arquitectura del Sistema](#arquitectura-del-sistema)
* [Changelog](#changelog)
* [Contribución](#contribución)

## ✨ Características

### 💬 Integración WhatsApp (v2.11.0)

* **Integración con Evolution API v2**: conexión mediante QR, gestión de instancia y envío de mensajes.
* **Recordatorios automáticos**: comando Artisan + endpoint `POST /api/scheduler/run` (Bearer token) para disparo externo vía n8n u otro scheduler.
* **Confirmación al crear turno** (`send_on_create`): el paciente recibe un mensaje de confirmación inmediato al crearse el turno.
* **Aviso de cancelación** (`send_on_cancel`): notificación automática cuando un turno es cancelado.
* **Toggles independientes** para cada tipo de notificación (recordatorios, confirmación, cancelación) desde la página de Conexión, guardados via AJAX sin recargar.
* **Plantilla configurable** por tipo en `/whatsapp/settings` (acordeones con preview en tiempo real), variables `{{nombre}}`, `{{fecha}}`, `{{hora}}`, `{{profesional}}`.
* **Opt-out por paciente/profesional**: configurable desde el modal de pacientes.
* **Historial de mensajes** con tipo, estado (enviado/fallido/pendiente), modal de detalle y filtro por tipo.
* **Ícono de estado** en la barra superior: verde = conectado, rojo = desconectado.

### ⚙️ Configuración del Centro (v2.10.2)

* **Menú Sistema > General**: configuración centralizada del centro médico (nombre, dirección, teléfono, email, logo, imagen de login).
* **Bloqueo de acceso**: switch para suspender el acceso de todos los usuarios excepto el Administrador del sistema (útil ante problemas de pago u operativos).
* Datos del centro dinámicos en toda la aplicación: login, sidebar, recibos, reportes.

### 🎯 Gestión de Turnos

* Programación de citas con validación de disponibilidad
* Sistema de entreturnos/urgencias con registro inmediato
* Control de estados: programado → atendido → cobrado
* Asignación automática de pagos a turnos
* **Vista Agenda en dos columnas** (v2.9.3): calendario compacto a la izquierda, panel de día inline a la derecha; sin modal overlay, sin doble dismiss
* **Panel de día**: auto-apertura del día actual, botón "Nuevo Turno" en el header, timeline expandido
* **Mini-calendario**: celdas cuadradas, indicadores con punto + conteo por estado, tooltip con leyenda al hover
* **Timeline de día preciso** con posicionamiento absoluto por tiempo, turnos pasados en solo lectura y prevención de solapamiento de turnos
* **Gestión de feriados** integrada con bloqueo automático de turnos
* **Creación rápida de pacientes** desde modal de turnos con botón "+"
* **Búsqueda inteligente** de pacientes sin importar acentos (Jose encuentra José)

### 💰 Módulo de Pagos Avanzado (v2.6.0)

* **Nuevo sistema de payment_details** para pagos mixtos
* Soporte para **cobros en múltiples métodos** (efectivo + transferencia)
* Pagos individuales o en paquetes
* Múltiples métodos de pago: efectivo, transferencia, tarjeta de débito, tarjeta de crédito, QR
* Generación automática de recibos en formato A5
* Trazabilidad completa de transacciones
* **Estadísticas segregadas** por método de pago
* **Tracking de quién recibe el pago** (centro vs profesional)

### 🏦 Gestión de Caja Integral

* Apertura/cierre con validaciones automáticas
* **Cierre automático ajustado** a 23:59 del día de apertura (sin correcciones manuales)
* Alertas inteligentes y balance en tiempo real
* Trazabilidad completa por usuario con auditoría de hora real
* Reportes diarios y por período

### 👨‍⚕️ Administración de Profesionales

* Configuración de comisiones y horarios
* Liquidaciones automáticas con control de pendientes
* **Configuración de cobro directo** (receives_transfers_directly)
* Cálculo automático de comisión según porcentaje configurado
* **Sistema de cumpleaños** con visualización en agenda 🎂
* Registro de fecha de nacimiento con cálculo automático de edad
* Recordatorios visuales en calendario con tooltip informativo

### 👥 Gestión de Pacientes

* Registro completo de información personal y médica
* **Formato de visualización profesional**: Apellido, Nombre
* **Teléfono Fijo** (v2.10.3): campo opcional adicional al teléfono móvil, visible en índice y ficha de paciente
* Historial de citas y tratamientos
* Seguimiento de pagos y saldos

### 📅 Módulo de Recesos

* **Gestión centralizada de feriados** desde Configuración
* CRUD completo con activar/desactivar y eliminación
* Filtrado por año para búsqueda eficiente
* **Integración automática con Agenda** (bloqueo visual y funcional)
* Prevención de creación de turnos en fechas bloqueadas

### 📊 Dashboard y Reportes

* Vista en tiempo real del día actual
* Liquidación diaria de profesionales
* Reportes optimizados para impresión y control administrativo
* **Exportación de reportes de caja** a Excel (CSV estructurado) e impresión via navegador
* **Módulo de Informes Analíticos** (v2.10.0): 13 informes históricos agrupados en Profesionales, Pacientes y Financiero; todos con impresión directa, gráficos Chart.js y submenús colapsables en la navegación
* **UX de impresión estandarizada** (v2.10.4): todas las vistas print cierran automáticamente la pestaña tras imprimir (`afterprint`); corregido bug de doble diálogo en 8 reportes

## 🛠 Tecnologías

### Backend

* **Laravel 12** - Framework PHP
* **PHP 8.2** - Lenguaje de programación
* **MySQL** - Base de datos
* **Eloquent ORM** - Manejo de datos

### Frontend

* **Vite** - Build tool moderno
* **TailwindCSS 4.0** - Framework CSS
* **Alpine.js** - Framework JS reactivo
* **Blade** - Motor de plantillas

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

* **Appointment**: Citas médicas
* **Payment**: Pagos individuales, paquetes e ingresos manuales
* **PaymentDetail**: Detalles de métodos de pago (v2.6.0)
* **PatientPackage**: Gestión de paquetes de sesiones (v2.6.0)
* **Professional**: Configuración de médicos
* **Patient**: Datos de pacientes
* **CashMovement**: Movimientos de caja
* **MovementType**: Tipos de movimiento configurables

### Servicios

* **PaymentAllocationService**: Asignación automática de pagos a turnos

### Base de Datos

* Migraciones versionadas y relaciones optimizadas
* Índices para consultas eficientes

## 📝 Changelog

### 🔄 Últimas versiones

* **v2.11.0** (2026-04-19) – 💬 WhatsApp: notificaciones al crear/cancelar turno, toggles independientes por tipo, vista Plantillas con acordeones y preview en tiempo real, historial con modal de detalle. 🐛 Fix toggle enabled AJAX, formErrors en patientModal de Agenda, reactividad opt-out en Pacientes.
* **v2.10.5** (2026-04-16) – 💬 WhatsApp: módulo completo de recordatorios automáticos de turnos con Evolution API v2; QR, desconexión robusta, plantilla configurable, historial de mensajes, endpoint para n8n, ícono de estado en barra superior. 🗂️ Tabla de pacientes compactada. 🐛 Fix toggle opt-out WhatsApp en Pacientes.
* **v2.10.4** (2026-04-07) – 🖨️ Fix impresión doble en reportes: corregido bug donde el diálogo de impresión se mostraba dos veces; estandarizado comportamiento `afterprint` con cierre automático de pestaña en todas las vistas print (19 vistas).
* **v2.10.3** (2026-04-04) – 📅 Persistencia de fecha en Agenda: al crear/modificar un turno se conserva el día seleccionado en lugar de volver siempre a hoy. 📞 Teléfono Fijo en Pacientes: nuevo campo opcional `phone_landline` en formulario, índice y ficha.
* **v2.10.2** (2026-03-26) – 🔒 Fix CSRF 419: manejo explícito de sesión expirada en todos los formularios Alpine.js; toast de advertencia + redirección automática al login en 8 módulos (pacientes, profesionales, turnos, agenda, pagos, caja).
* **v2.10.1** (2026-03-26) – 🔐 Revisión de Seguridad: middleware de módulos en rutas core, fix bug crítico `payment_type='expense'`, eliminación de log de datos sensibles, fix IDOR en notas de profesionales. 138 tests unitarios con 13 factories nuevas cubriendo modelos y servicios clave.
* **v2.10.0** (2026-03-26) – 📊 Módulo de Informes Analíticos: 13 nuevos reportes históricos (Profesionales, Pacientes, Financiero) con Chart.js, impresión universal y menú de Reportes reestructurado en submenús colapsables.
* **v2.9.5** (2026-03-23) – 💰 Mejoras en vistas de Liquidación Profesional: totales de comisión explícitos, estilo minimalista en detalle de cobros, header de impresión rediseñado con logo.
* **v2.9.4-2** (2026-03-10) – 🎨 UX Caja: formularios compactados con acordeón para notas/comprobante. 🌙 Toggle de tema claro/oscuro: barra persistente con fecha, versión del sistema y botón sol/luna; preferencia guardada en `localStorage`, light por defecto.
* **v2.9.4-1** (2026-03-07) – 🔧 Refactoring MovementType: eliminación de jerarquía padre/hijo, reclasificación de `refund` a gastos, retiros incluidos en Informe de Gastos, Excel mejorado con secciones y formato argentino, eliminación de botón PDF y DomPDF.
* **v2.9.4** (2026-03-05) – 🖨️ Listado Diario: sistema de impresión estándar (layout/print + header con logo), auto-impresión y auto-cierre de pestaña, fix botón imprimir en cards de selección, fix conteo de pacientes sin cancelados.
* **v2.9.3-1** (2026-03-04) – 🐛 Fix: permitir creación de turnos en sábados cuando el profesional tiene horario configurado para ese día.
* **v2.9.3** (2026-03-01) – 🗓️ Agenda en dos columnas: panel de día inline (reemplaza modal overlay), mini-calendario con celdas cuadradas y tooltip de leyenda, auto-apertura del día actual, header del panel rediseñado.
* **v2.9.2** (2026-03-01) – 🖨️ Reportes de impresión rediseñados: nuevo componente `x-report-print-header`, layout unificado `layouts/print`, vistas migradas (Análisis de Caja, Gastos, Movimientos de Caja), botón imprimir movimientos restaurado.
* **v2.9.1** (2026-02-28) – 📝 Notas internas por profesional en Agenda: panel lateral colapsable con CRUD de notas, trazabilidad en log de actividad.
* **v2.9.0** (2026-02-27) – ✨ Refactoring de Agenda (5 partials), fix scroll, mejoras visuales de bloques y dashboard.
* **v2.8.1** (2026-02-27) – 🔐 Perfiles de Acceso Modular: reemplaza roles fijos por perfiles configurables desde la BD; 9 módulos, CRUD de perfiles, menús Configuración/Sistema independientes.
* **v2.8.0** (2026-02-23) – 🔍 Registro de Actividades: Auditoría completa CRUD, login/logout, historial filtrable para admins. 📅 Mejoras Agenda: timeline preciso con posicionamiento absoluto por tiempo, celdas clickeables, turnos pasados en solo lectura, prevención de solapamiento de duraciones.
* **v2.7.0** (2026-02-09) – 📅 Sábados en Agenda y Horarios: Habilitación del día Sábado en la vista de Agenda (grid de 6 columnas), nuevo botón de acción rápida "Semana Completa" (Lun-Sáb) en configuración de horarios con Sábado 8:00-15:00.
* **v2.6.3** (2026-01-30) – 🗂️ Reorganización Menú de Caja + 📊 Exportación Excel/PDF: Nueva estructura de menú (Caja del Día, Movimientos de Caja, Análisis de Caja), exportación de reportes a Excel y PDF, impresión de movimientos, y correcciones en reportes por rango.
* **v2.6.2-hotfix-4** (2026-01-21) – 🖨️ Impresión Individual de Liquidaciones: Icono de impresora en cada liquidación parcial para imprimir por separado, vista de impresión adaptada con resumen específico, y corrección de totales en pagos múltiples (efectivo + digital).
* **v2.6.2-hotfix-3** (2026-01-21) – 🔄 Liquidaciones Parciales: Permite liquidar profesionales aunque tengan turnos pendientes, habilitando múltiples liquidaciones durante el día sin esperar al cierre.
* **v2.6.2-hotfix** (2026-01-09) – 🐛 Correcciones Críticas: Fix error en cierre de caja (relación paymentAppointment), componente reutilizable de modal de cierre con resumen completo, corrección de lista de liquidaciones para profesionales con monto $0, y exclusión de gastos en lista de pagos.
* **v2.6.1** (2026-01-05) – 🎂 Sistema de Cumpleaños de Profesionales + 🔧 Cierre Automático de Caja + 🔄 Orden de Nombres: Registro de fecha de nacimiento con visualización en agenda, ajuste automático del cierre de caja a las 23:59 del día de apertura, y cambio de visualización de pacientes a formato "Apellido, Nombre".
* **v2.6.0** (2025-11-18) – 🚀 Reestructuración Sistema de Pagos: payment_details, pagos mixtos, comando de migración automático.
* **v2.5.11** (2025-11-04) – Arqueo de Caja + Recibos de Ingresos Manuales: Sistema unificado de numeración de recibos.
* **v2.5.10** (2025-11-03) – Separación de gestión operativa de caja y reportes históricos con cards simplificadas.
* **v2.5.9** (2025-11-02) – Sistema de entreturnos, anulación de pagos con trazabilidad completa.

👉 [Ver changelog completo](CHANGELOG.md)

## 👨‍💻 Contribución

Las contribuciones son bienvenidas. Por favor, abrí un issue o enviá un pull request con una descripción clara de los cambios propuestos.

---

**Licencia:** [MIT](LICENSE)

---

> 💚 *PuntoSalud* - Simplificando la gestión médica con tecnología moderna y confiable.
