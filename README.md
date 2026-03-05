# 🏥 PuntoSalud - Sistema de Gestión Médica

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat\&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat\&logo=php)](https://php.net)
[![Version](https://img.shields.io/badge/Version-2.9.4-green?style=flat)](#changelog)
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
* **Exportación de reportes de caja** a Excel (CSV) y PDF

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
