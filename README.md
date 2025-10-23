# 🏥 PuntoSalud - Sistema de Gestión Médica

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat\&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat\&logo=php)](https://php.net)
[![Version](https://img.shields.io/badge/Version-2.5.5-green?style=flat)](#changelog)
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

### 💰 Módulo de Pagos Avanzado

* Pagos individuales o en paquetes
* Múltiples métodos de pago: efectivo, transferencia, tarjeta
* Generación automática de recibos en formato A5
* Trazabilidad completa de transacciones

### 🏦 Gestión de Caja Integral

* Apertura/cierre con validaciones automáticas
* Alertas inteligentes y balance en tiempo real
* Trazabilidad completa por usuario
* Reportes diarios y por período

### 👨‍⚕️ Administración de Profesionales

* Configuración de comisiones y horarios
* Liquidaciones automáticas con control de pendientes

### 👥 Gestión de Pacientes

* Registro completo de información personal y médica
* Historial de citas y tratamientos
* Seguimiento de pagos y saldos

### 📊 Dashboard y Reportes

* Vista en tiempo real del día actual
* Liquidación diaria de profesionales
* Reportes optimizados para impresión y control administrativo

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
* **Payment**: Pagos individuales y paquetes
* **Professional**: Configuración de médicos
* **Patient**: Datos de pacientes
* **CashMovement**: Movimientos de caja

### Servicios

* **PaymentAllocationService**: Asignación automática de pagos a turnos

### Base de Datos

* Migraciones versionadas y relaciones optimizadas
* Índices para consultas eficientes

## 📝 Changelog

### 🔄 Últimas versiones

* **v2.5.5** (2025-10-23) – Mejoras en gestión de datos y métodos de pago.
* **v2.5.4** (2025-10-23) – Mejoras en UX y gestión de horarios.
* **v2.5.3** (2025-10-20) – Optimizaciones de rendimiento y validaciones de caja.
* **v2.5.2** (2025-10-17) – Sistema de entreturnos/urgencias.
* **v2.5.1** (2025-10-14) – Impresión profesional de recibos.

👉 [Ver changelog completo](CHANGELOG.md)

## 👨‍💻 Contribución

Las contribuciones son bienvenidas. Por favor, abrí un issue o enviá un pull request con una descripción clara de los cambios propuestos.

---

**Licencia:** [MIT](LICENSE)

---

> 💚 *PuntoSalud* - Simplificando la gestión médica con tecnología moderna y confiable.
