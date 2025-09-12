# 🏥 PuntoSalud - Sistema de Gestión Médica

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat&logo=php)](https://php.net)
[![Version](https://img.shields.io/badge/Version-2.3.0-green?style=flat)](#changelog)
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

### 🏦 **Gestión de Caja Integral** *(v2.1.0)*
- Registro automático de todos los movimientos financieros
- Balance en tiempo real con trazabilidad por usuario
- Tipos de movimiento extendidos: apertura, cierre, control de turno
- Preparado para gestión multi-usuario y cambios de turno
- Reportes diarios y por períodos

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